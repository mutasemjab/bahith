<?php

namespace App\Services\Apple;

use App\Contracts\AppleTransactionVerifier;
use App\Exceptions\ApplePurchaseException;
use OpenSSLAsymmetricKey;
use OpenSSLCertificate;

class AppleSignedTransactionVerifier implements AppleTransactionVerifier
{
    private const LEAF_EXTENSION_OID_DER = '060a2a864886f76364060b01';

    private const INTERMEDIATE_EXTENSION_OID_DER = '060a2a864886f76364060201';

    public function verify(string $signedTransaction): array
    {
        $parts = explode('.', $signedTransaction);
        if (count($parts) !== 3) {
            throw $this->invalid();
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = $this->decodeJsonSegment($encodedHeader);
        $payload = $this->decodeJsonSegment($encodedPayload);

        if (($header['alg'] ?? null) !== 'ES256') {
            throw $this->invalid();
        }

        $chain = $header['x5c'] ?? null;
        if (! is_array($chain) || count($chain) !== 3) {
            throw $this->invalid();
        }

        $certificateDer = [];
        foreach ($chain as $encodedCertificate) {
            if (! is_string($encodedCertificate)) {
                throw $this->invalid();
            }
            $decoded = base64_decode($encodedCertificate, true);
            if ($decoded === false || $decoded === '') {
                throw $this->invalid();
            }
            $certificateDer[] = $decoded;
        }

        $signedDate = $this->requiredMilliseconds($payload, 'signedDate');
        $effectiveTimestamp = (int) floor($signedDate / 1000);
        $leafPublicKey = $this->verifyCertificateChain(
            $certificateDer,
            $effectiveTimestamp
        );

        $signature = $this->base64UrlDecode($encodedSignature);
        if (strlen($signature) !== 64) {
            throw $this->invalid();
        }

        $verified = openssl_verify(
            $encodedHeader.'.'.$encodedPayload,
            $this->joseSignatureToDer($signature),
            $leafPublicKey,
            OPENSSL_ALGO_SHA256
        );
        if ($verified !== 1) {
            throw $this->invalid();
        }

        return $this->normalizeAndValidatePayload($payload);
    }

    /**
     * @param  array<int, string>  $chainDer
     * @return OpenSSLAsymmetricKey|resource
     */
    private function verifyCertificateChain(array $chainDer, int $effectiveTimestamp)
    {
        $certificates = array_map(
            fn (string $der) => $this->readCertificate($der),
            $chainDer
        );
        [$leaf, $intermediate] = $certificates;

        if (! $this->containsOid($chainDer[0], self::LEAF_EXTENSION_OID_DER)
            || ! $this->containsOid($chainDer[1], self::INTERMEDIATE_EXTENSION_OID_DER)) {
            throw $this->invalid();
        }

        $leafDetails = openssl_x509_parse($leaf);
        $intermediateDetails = openssl_x509_parse($intermediate);
        if (! is_array($leafDetails) || ! is_array($intermediateDetails)) {
            throw $this->invalid();
        }

        $intermediateIsCa = str_contains(
            (string) ($intermediateDetails['extensions']['basicConstraints'] ?? ''),
            'CA:TRUE'
        );
        if (! $intermediateIsCa
            || ($leafDetails['issuer'] ?? null) != ($intermediateDetails['subject'] ?? null)) {
            throw $this->invalid();
        }

        $intermediateKey = openssl_pkey_get_public($intermediate);
        if ($intermediateKey === false || openssl_x509_verify($leaf, $intermediateKey) !== 1) {
            throw $this->invalid();
        }

        $trustedRoot = $this->findTrustedRoot(
            $intermediate,
            $intermediateDetails
        );

        $this->checkCertificateDates($leafDetails, $effectiveTimestamp);
        $this->checkCertificateDates($intermediateDetails, $effectiveTimestamp);
        $this->checkCertificateDates($trustedRoot['details'], $effectiveTimestamp);

        $leafKey = openssl_pkey_get_public($leaf);
        if ($leafKey === false) {
            throw $this->invalid();
        }

        return $leafKey;
    }

    /**
     * @param  OpenSSLCertificate|resource  $intermediate
     * @param  array<string, mixed>  $intermediateDetails
     * @return array{certificate: mixed, details: array<string, mixed>}
     */
    private function findTrustedRoot(
        $intermediate,
        array $intermediateDetails
    ): array {
        foreach ((array) config('apple_iap.trusted_root_certificates', []) as $path) {
            if (! is_string($path) || ! is_file($path)) {
                continue;
            }

            $pem = file_get_contents($path);
            if ($pem === false) {
                continue;
            }

            $root = openssl_x509_read($pem);
            $rootDetails = $root === false ? false : openssl_x509_parse($root);
            if ($root === false || ! is_array($rootDetails)) {
                continue;
            }
            if (($intermediateDetails['issuer'] ?? null) != ($rootDetails['subject'] ?? null)) {
                continue;
            }

            $rootKey = openssl_pkey_get_public($root);
            if ($rootKey !== false && openssl_x509_verify($intermediate, $rootKey) === 1) {
                return ['certificate' => $root, 'details' => $rootDetails];
            }
        }

        throw $this->invalid();
    }

    /** @param  array<string, mixed>  $details */
    private function checkCertificateDates(array $details, int $effectiveTimestamp): void
    {
        $notBefore = (int) ($details['validFrom_time_t'] ?? 0);
        $notAfter = (int) ($details['validTo_time_t'] ?? 0);
        $skew = (int) config('apple_iap.max_clock_skew_seconds', 300);

        if ($notBefore <= 0 || $notAfter <= 0
            || $notBefore > $effectiveTimestamp + $skew
            || $notAfter < $effectiveTimestamp - $skew) {
            throw $this->invalid();
        }
    }

    /** @param  array<string, mixed>  $payload */
    private function normalizeAndValidatePayload(array $payload): array
    {
        $bundleId = $this->requiredString($payload, 'bundleId');
        $productId = $this->requiredString($payload, 'productId');
        $transactionId = $this->requiredString($payload, 'transactionId');
        $appAccountToken = strtolower($this->requiredString($payload, 'appAccountToken'));
        $environment = $this->requiredString($payload, 'environment');
        $type = $this->requiredString($payload, 'type');
        $purchaseDate = $this->requiredMilliseconds($payload, 'purchaseDate');
        $signedDate = $this->requiredMilliseconds($payload, 'signedDate');
        $quantity = (int) ($payload['quantity'] ?? 1);

        if (! hash_equals((string) config('apple_iap.bundle_id'), $bundleId)
            || ! hash_equals((string) config('apple_iap.course_product_id'), $productId)
            || ! in_array($environment, (array) config('apple_iap.allowed_environments'), true)
            || $type !== 'Consumable'
            || $quantity !== 1
            || ! preg_match('/^\d{6,30}$/', $transactionId)
            || ! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $appAccountToken)
            || ! empty($payload['revocationDate'])
            || (($payload['inAppOwnershipType'] ?? 'PURCHASED') !== 'PURCHASED')) {
            throw $this->invalid();
        }

        $skewMilliseconds = (int) config('apple_iap.max_clock_skew_seconds', 300) * 1000;
        $nowMilliseconds = (int) floor(microtime(true) * 1000);
        if ($signedDate > $nowMilliseconds + $skewMilliseconds
            || $purchaseDate > $signedDate + $skewMilliseconds) {
            throw $this->invalid();
        }

        return [
            'bundle_id' => $bundleId,
            'product_id' => $productId,
            'transaction_id' => $transactionId,
            'original_transaction_id' => isset($payload['originalTransactionId'])
                ? (string) $payload['originalTransactionId']
                : null,
            'app_account_token' => $appAccountToken,
            'environment' => $environment,
            'type' => $type,
            'quantity' => $quantity,
            'purchase_date_ms' => $purchaseDate,
            'signed_date_ms' => $signedDate,
            'payload' => $payload,
        ];
    }

    /** @return array<string, mixed> */
    private function decodeJsonSegment(string $segment): array
    {
        $decoded = $this->base64UrlDecode($segment);
        $value = json_decode($decoded, true);
        if (! is_array($value) || json_last_error() !== JSON_ERROR_NONE) {
            throw $this->invalid();
        }

        return $value;
    }

    private function base64UrlDecode(string $value): string
    {
        if ($value === '' || preg_match('/[^A-Za-z0-9_-]/', $value)) {
            throw $this->invalid();
        }
        $padding = (4 - strlen($value) % 4) % 4;
        $decoded = base64_decode(strtr($value, '-_', '+/').str_repeat('=', $padding), true);
        if ($decoded === false) {
            throw $this->invalid();
        }

        return $decoded;
    }

    /** @param  array<string, mixed>  $payload */
    private function requiredString(array $payload, string $key): string
    {
        if (! array_key_exists($key, $payload)
            || (! is_string($payload[$key]) && ! is_int($payload[$key]))) {
            throw $this->invalid();
        }
        $value = (string) $payload[$key];
        if ($value === '') {
            throw $this->invalid();
        }

        return $value;
    }

    /** @param  array<string, mixed>  $payload */
    private function requiredMilliseconds(array $payload, string $key): int
    {
        $value = $payload[$key] ?? null;
        if ((! is_int($value) && ! is_string($value)) || ! ctype_digit((string) $value)) {
            throw $this->invalid();
        }
        $milliseconds = (int) $value;
        if ($milliseconds <= 0) {
            throw $this->invalid();
        }

        return $milliseconds;
    }

    /** @return OpenSSLCertificate|resource */
    private function readCertificate(string $der)
    {
        $certificate = openssl_x509_read($this->derToPem($der));
        if ($certificate === false) {
            throw $this->invalid();
        }

        return $certificate;
    }

    private function containsOid(string $certificateDer, string $oidHex): bool
    {
        $oid = hex2bin($oidHex);

        return $oid !== false && str_contains($certificateDer, $oid);
    }

    private function derToPem(string $der): string
    {
        return "-----BEGIN CERTIFICATE-----\n"
            .chunk_split(base64_encode($der), 64, "\n")
            ."-----END CERTIFICATE-----\n";
    }

    private function joseSignatureToDer(string $signature): string
    {
        $r = $this->unsignedInteger(substr($signature, 0, 32));
        $s = $this->unsignedInteger(substr($signature, 32, 32));
        $sequence = "\x02".chr(strlen($r)).$r."\x02".chr(strlen($s)).$s;

        return "\x30".chr(strlen($sequence)).$sequence;
    }

    private function unsignedInteger(string $value): string
    {
        $value = ltrim($value, "\x00");
        if ($value === '') {
            $value = "\x00";
        }
        if ((ord($value[0]) & 0x80) !== 0) {
            $value = "\x00".$value;
        }

        return $value;
    }

    private function invalid(): ApplePurchaseException
    {
        return new ApplePurchaseException(
            'تعذر التحقق من عملية الشراء لدى App Store.',
            422
        );
    }
}
