<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class StudentsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {

            // Row 1 = headers, so first data row is Excel row 2
            $rowNum = $index + 2;

            $name = trim($row['name'] ?? '');
            $nationalId = trim((string) ($row['national_id'] ?? ''));
            $password = trim((string) ($row['password'] ?? ''));
            $classId = $row['class_id'] ?? null;

            // Skip if name is empty
            if (empty($name)) {
                $this->skipped++;
                continue;
            }

            // Check duplicate national ID
            if ($nationalId && Student::where('national_id', $nationalId)->exists()) {
                $this->errors[] =
                    "Row {$rowNum}: National ID '{$nationalId}' already exists — skipped";

                $this->skipped++;
                continue;
            }

            // Validate class_id
            if ($classId && !\App\Models\SchoolClass::where('id', $classId)->exists()) {
                $this->errors[] =
                    "Row {$rowNum}: Class ID '{$classId}' does not exist — skipped";

                $this->skipped++;
                continue;
            }

            Student::create([
                'name'        => $name,
                'national_id' => $nationalId ?: null,
                'email'       => null,
                'phone'       => null,
                'password'    => Hash::make($password),
                'class_id'    => $classId ?: null,
                'is_active'   => true,
            ]);

            $this->imported++;
        }
    }
}