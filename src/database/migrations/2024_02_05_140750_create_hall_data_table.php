<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hall_data', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedInteger('slot_number');
            $table->unsignedInteger('game_count');
            $table->Integer('difference_coins');
            $table->unsignedInteger('big_bonus_count');
            $table->unsignedInteger('regular_bonus_count');
            $table->unsignedInteger('art_count')->nullable();
            $table->decimal('synthesis_probability', 10, 1);
            $table->decimal('big_bonus_probability', 10, 1);
            $table->decimal('regular_bonus_probability', 10, 1);
            $table->decimal('art_probability', 10, 1)->nullable();
            $table->boolean('is_high_setting')->nullable()->default(0);
            $table->unsignedBigInteger('hall_id');
            $table->foreign('hall_id')->references('id')->on('halls');
            $table->unsignedBigInteger('slot_machines_id');
            $table->foreign('slot_machines_id')->references('id')->on('slot_machines');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_data');
    }
};
