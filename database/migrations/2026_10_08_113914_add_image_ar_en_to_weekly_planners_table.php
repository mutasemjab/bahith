<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('weekly_planners', function (Blueprint $table) {
            if (! Schema::hasColumn('weekly_planners', 'image_ar')) {
                $table->string('image_ar')->nullable()->after('image');
            }
            if (! Schema::hasColumn('weekly_planners', 'image_en')) {
                $table->string('image_en')->nullable()->after('image_ar');
            }
        });

        // Existing planners only ever had one (Arabic) image — carry it over so
        // they keep showing it under the new image_ar field.
        DB::table('weekly_planners')
            ->whereNotNull('image')
            ->whereNull('image_ar')
            ->update(['image_ar' => DB::raw('image')]);
    }

    public function down()
    {
        Schema::table('weekly_planners', function (Blueprint $table) {
            $table->dropColumn(['image_ar', 'image_en']);
        });
    }
};
