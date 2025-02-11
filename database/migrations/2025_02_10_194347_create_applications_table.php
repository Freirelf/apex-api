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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motorcyclist_id')->constrained()->onDelete('cascade'); // FK com motoboys
            $table->foreignId('delivery_job_id')->constrained()->onDelete('cascade'); // FK com vagas
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending'); // Status da candidatura
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};