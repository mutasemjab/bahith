<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KindergartenSeeder extends Seeder
{
    public function run(): void
    {
        // ── Root: الروضة ───────────────────────────────────────────────────
        $kg = Category::updateOrCreate(
            ['parent_id' => null, 'name_en' => 'Kindergarten'],
            [
                'level'       => 0,
                'name_ar'     => 'الروضة',
                'icon'        => 'bi-stars',
                'order_index' => 0,
                'is_active'   => true,
            ]
        );

        $semesters = [
            ['name_ar' => 'الفصل الأول', 'name_en' => 'First Semester', 'order' => 1],
            ['name_ar' => 'الفصل الثاني', 'name_en' => 'Second Semester', 'order' => 2],
        ];

        $kgSubjects = [
            ['name_ar' => 'اللغة العربية',     'name_en' => 'Arabic Language',    'order_index' => 1],
            ['name_ar' => 'الرياضيات',          'name_en' => 'Mathematics',         'order_index' => 2],
            ['name_ar' => 'اللغة الإنجليزية',  'name_en' => 'English Language',    'order_index' => 3],
            ['name_ar' => 'التربية الإسلامية',  'name_en' => 'Islamic Education',  'order_index' => 4],
            ['name_ar' => 'العلوم',             'name_en' => 'Science',             'order_index' => 5],
            ['name_ar' => 'الفنون',             'name_en' => 'Arts',               'order_index' => 6],
            ['name_ar' => 'التربية البدنية',    'name_en' => 'Physical Education', 'order_index' => 7],
        ];

        foreach ($semesters as $sem) {
            $semester = Category::updateOrCreate(
                ['parent_id' => $kg->id, 'name_en' => $sem['name_en']],
                [
                    'level'       => 1,
                    'name_ar'     => $sem['name_ar'],
                    'order_index' => $sem['order'],
                    'is_active'   => true,
                ]
            );

            foreach ($kgSubjects as $sub) {
                $alreadyExists = DB::table('subjects')
                    ->where('category_id', $semester->id)
                    ->where('name_ar', $sub['name_ar'])
                    ->exists();

                if (! $alreadyExists) {
                    DB::table('subjects')->insert([
                        'category_id' => $semester->id,
                        'name_ar'     => $sub['name_ar'],
                        'name_en'     => $sub['name_en'],
                        'order_index' => $sub['order_index'],
                        'is_active'   => 1,
                        'is_elective' => 0,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }

        // ── Classes table entries ──────────────────────────────────────────
        $kgClasses = [
            ['name' => 'الروضة - الفصل الأول',  'order' => 1],
            ['name' => 'الروضة - الفصل الثاني', 'order' => 2],
        ];

        foreach ($kgClasses as $cls) {
            $exists = DB::table('classes')->where('name', $cls['name'])->exists();
            if (! $exists) {
                DB::table('classes')->insert([
                    'name'       => $cls['name'],
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
