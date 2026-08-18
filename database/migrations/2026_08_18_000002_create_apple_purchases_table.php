<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apple_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_id');
            $table->string('transaction_id')->unique();
            $table->string('original_transaction_id')->nullable()->index();
            $table->uuid('student_app_account_token');
            $table->uuid('purchase_token');
            $table->string('environment', 20);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('verified_at');
            $table->timestamp('revoked_at')->nullable();
            $table->longText('signed_transaction');
            $table->json('verified_payload');
            $table->timestamps();

            $table->index(['student_id', 'course_id']);
            $table->index(['purchase_token', 'environment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apple_purchases');
    }
};
