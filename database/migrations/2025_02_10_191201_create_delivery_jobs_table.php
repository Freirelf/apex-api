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
        Schema::create('delivery_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade'); // Relaciona com a loja
            $table->string('title'); 
            $table->text('description'); 
            $table->decimal('distance', 8, 2); // Distância máxima (em km)
            $table->date('date'); // Data da entrega
            $table->time('time'); // Horário da entrega
            $table->decimal('value', 10, 2); // Valor da entrega
            $table->boolean('is_guaranteed')->default(false); // É garantido?
            $table->boolean('meal_included')->default(false); // Refeição no local?
            $table->boolean('provides_bag')->default(false); // Disponibiliza bag?
            $table->string('pickup_address'); // Endereço de coleta
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open'); // Status da vaga
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_jobs');
    }
};
