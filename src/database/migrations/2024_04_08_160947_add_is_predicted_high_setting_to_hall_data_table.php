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
        Schema::table('hall_data', function (Blueprint $table) {
            $table->boolean('is_predicted_high_setting')->nullable()->default(0)->after('is_high_setting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hall_data', function (Blueprint $table) {
            $table->dropColumn('is_predicted_high_setting');
        });
    }
};
