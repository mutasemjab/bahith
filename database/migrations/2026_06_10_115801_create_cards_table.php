<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_id')->nullable();
            $table->foreign('pos_id')->references('id')->on('p_o_s')->onDelete('cascade');
            $table->string('name_en');
            $table->string('name_ar');
            $table->double('selling_price');
            $table->double('number_of_cards');
            $table->string('photo')->nullable();
            
            // course  → activates one specific course
            // teacher → activates all courses by one teacher
            // price   → activates any course whose price equals this card's selling_price
            $table->enum('activation_type', ['course', 'teacher', 'price'])->default('price');
            $table->unsignedBigInteger('linked_course_id')->nullable();
            $table->unsignedBigInteger('linked_teacher_id')->nullable();

            $table->foreign('linked_course_id')->references('id')->on('courses')->nullOnDelete();
            $table->foreign('linked_teacher_id')->references('id')->on('teachers')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cards');
    }
};
