<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conduct_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar')->default('مدونة السلوك والانضباط الداخلي للطلبة');
            $table->string('title_en')->default('Student Code of Conduct');
            $table->longText('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conduct_documents');
    }
};
