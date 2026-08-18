<?php

namespace App\Contracts;

interface AppleTransactionVerifier
{
    /**
     * Verify Apple's signature and certificate chain, then return normalized
     * transaction fields plus the decoded payload.
     *
     * @return array<string, mixed>
     */
    public function verify(string $signedTransaction): array;
}
