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
        Schema::create('monitoring_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('order');             // Order number
            $table->string('color');              // Color for the status
            $table->string('status');             // Status name (e.g., Healthy, Warning)
            $table->string('description');        // Description (e.g., No requests for 3 days)
            $table->integer('days')->nullable();  // Number of days (e.g., 3, 5, 10)
            $table->string('condition', 2)->nullable();  // Storing the operator (e.g., <, >, =, <=, >=)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_configs');
    }
};
