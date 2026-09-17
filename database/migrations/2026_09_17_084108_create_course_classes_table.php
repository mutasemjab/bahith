<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('course_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['course_id', 'class_id']);
        });

        // Carry each course's existing single class into the new pivot table
        // so multi-class filtering keeps working for courses created before this upgrade.
        DB::table('courses')
            ->whereNotNull('class_id')
            ->orderBy('id')
            ->chunkById(200, function ($courses) {
                foreach ($courses as $course) {
                    DB::table('course_classes')->insert([
                        'course_id'  => $course->id,
                        'class_id'   => $course->class_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down()
    {
        Schema::dropIfExists('course_classes');
    }
};
