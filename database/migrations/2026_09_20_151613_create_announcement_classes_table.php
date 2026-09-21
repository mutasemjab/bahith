<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('announcement_classes')) {
            Schema::create('announcement_classes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
                $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['announcement_id', 'class_id']);
            });
        }

        // Carry each announcement's existing single class into the pivot so
        // multi-class targeting keeps matching announcements created before this.
        DB::table('announcements')
            ->whereNotNull('class_id')
            ->orderBy('id')
            ->chunkById(200, function ($announcements) {
                foreach ($announcements as $announcement) {
                    DB::table('announcement_classes')->insertOrIgnore([
                        'announcement_id' => $announcement->id,
                        'class_id'        => $announcement->class_id,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            });
    }

    public function down()
    {
        Schema::dropIfExists('announcement_classes');
    }
};
