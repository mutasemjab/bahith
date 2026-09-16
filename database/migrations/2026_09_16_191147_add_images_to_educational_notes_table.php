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
        Schema::table('educational_notes', function (Blueprint $table) {
            $table->json('images')->nullable()->after('attachment');
        });

        // Carry any existing single attachment into the new images array
        // so old notes keep showing their file after this upgrade.
        DB::table('educational_notes')
            ->whereNotNull('attachment')
            ->orderBy('id')
            ->chunkById(200, function ($notes) {
                foreach ($notes as $note) {
                    DB::table('educational_notes')
                        ->where('id', $note->id)
                        ->update(['images' => json_encode([$note->attachment])]);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('educational_notes', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
