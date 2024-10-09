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
        Schema::table('MibImpairments', function (Blueprint $table) {
            $table->timestamps();
        });

        Schema::table('MibImpairment_Audit', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('MibImpairments', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        Schema::table('MibImpairment_Audit', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
