<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('units', 'total_videos')) {
            Schema::table('units', function (Blueprint $table) {
                $table->unsignedInteger('total_videos')->default(0)->after('order_index');
            });
        }

        if (! Schema::hasColumn('units', 'total_pdfs')) {
            Schema::table('units', function (Blueprint $table) {
                $table->unsignedInteger('total_pdfs')->default(0)->after('total_videos');
            });
        }

        // Backfill from existing data so counts aren't stuck at 0 for units
        // that already have lessons/materials (the columns never existed
        // before, so nothing was ever tracked for them until now).
        if (Schema::hasTable('lessons')) {
            DB::statement('
                UPDATE units
                SET total_videos = (SELECT COUNT(*) FROM lessons WHERE lessons.unit_id = units.id)
            ');
        }

        if (Schema::hasTable('materials')) {
            DB::statement('
                UPDATE units
                SET total_pdfs = (SELECT COUNT(*) FROM materials WHERE materials.unit_id = units.id)
            ');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['total_videos', 'total_pdfs']);
        });
    }
};
