<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conduct_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('conduct_documents')->cascadeOnDelete();
            $table->string('guardian_name');
            $table->timestamp('signed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['student_id', 'document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conduct_signatures');
    }
};
