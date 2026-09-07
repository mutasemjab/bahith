<?php

namespace App\Imports;

use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class TeachersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {

            // Row 1 = headers
            $rowNum = $index + 2;

            $name = trim($row['name'] ?? '');
            $nationalId = trim((string) ($row['national_id'] ?? ''));
            $phone = trim((string) ($row['phone'] ?? ''));
            $specialization = trim($row['specialization'] ?? '');
            $password = trim((string) ($row['password'] ?? ''));

            // Name is required
            if (empty($name)) {
                $this->errors[] =
                    "Row {$rowNum}: Name is required — skipped";

                $this->skipped++;
                continue;
            }

            // National ID is required
            if (empty($nationalId)) {
                $this->errors[] =
                    "Row {$rowNum}: National ID is required — skipped";

                $this->skipped++;
                continue;
            }

            // Password is required
            if (empty($password)) {
                $this->errors[] =
                    "Row {$rowNum}: Password is required — skipped";

                $this->skipped++;
                continue;
            }

            // Check duplicate national ID
            if (Teacher::where('national_id', $nationalId)->exists()) {
                $this->errors[] =
                    "Row {$rowNum}: National ID '{$nationalId}' already exists — skipped";

                $this->skipped++;
                continue;
            }

            // Create teacher
            Teacher::create([
                'name'              => $name,
                'national_id'       => $nationalId,
                'phone'             => $phone ?: null,
                'specialization_ar' => $specialization ?: null,
                'password'          => Hash::make($password),
                'is_active'         => true,
                'is_verified'       => false,
            ]);

            $this->imported++;
        }
    }
}

