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
        Schema::table('MibEntries', function (Blueprint $table) {
            $table->timestamps();
        });

        Schema::table('MibEntry_Audit', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('MibEntries', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        Schema::table('MibEntry_Audit', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
