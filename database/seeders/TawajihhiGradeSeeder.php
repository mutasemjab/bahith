<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TawajihhiGradeSeeder extends Seeder
{
    public function run(): void
    {
        $tawjihi = DB::table('categories')
            ->whereNull('parent_id')
            ->where('name_ar', 'like', '%توجيهي%')
            ->first();

        if (! $tawjihi) {
            return;
        }

        $alreadyDone = DB::table('categories')
            ->where('parent_id', $tawjihi->id)
            ->where('name_ar', 'like', '%حادي عشر%')
            ->exists();

        if ($alreadyDone) {
            return;
        }

        $existingChildIds = DB::table('categories')
            ->where('parent_id', $tawjihi->id)
            ->pluck('id');

        $grade12Id = DB::table('categories')->insertGetId([
            'parent_id'   => $tawjihi->id,
            'level'       => ($tawjihi->level + 1),
            'name_ar'     => 'الصف الثاني عشر',
            'name_en'     => 'Grade 12',
            'is_active'   => 1,
            'order_index' => 20,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        if ($existingChildIds->isNotEmpty()) {
            DB::table('categories')
                ->whereIn('id', $existingChildIds)
                ->update([
                    'parent_id'  => $grade12Id,
                    'level'      => ($tawjihi->level + 2),
                    'updated_at' => now(),
                ]);
        }

        $grade11Id = DB::table('categories')->insertGetId([
            'parent_id'   => $tawjihi->id,
            'level'       => ($tawjihi->level + 1),
            'name_ar'     => 'الصف الحادي عشر',
            'name_en'     => 'Grade 11',
            'is_active'   => 1,
            'order_index' => 10,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $subjects = [
            ['name_ar' => 'الرياضيات',        'name_en' => 'Mathematics',       'order_index' => 1],
            ['name_ar' => 'اللغة العربية',     'name_en' => 'Arabic Language',   'order_index' => 2],
            ['name_ar' => 'التاريخ',           'name_en' => 'History',           'order_index' => 3],
            ['name_ar' => 'التربية الإسلامية', 'name_en' => 'Islamic Education', 'order_index' => 4],
            ['name_ar' => 'اللغة الإنجليزية', 'name_en' => 'English Language',  'order_index' => 5],
        ];

        foreach ($subjects as $sub) {
            DB::table('subjects')->insert([
                'category_id' => $grade11Id,
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
