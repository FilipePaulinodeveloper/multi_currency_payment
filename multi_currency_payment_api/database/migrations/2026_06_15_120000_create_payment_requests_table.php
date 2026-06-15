<?php

use App\Enums\PaymentRequestStatus;
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
        Schema::create('payment_requests', function (Blueprint $table) {
            // ID e Foreign Keys
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');

            // Campos preenchidos pelo usuário
            $table->string('description', 1000)->nullable();
            $table->decimal('amount_local', 15, 2);
            $table->string('currency', 3)->index();

            // Campos calculados pelo sistema
            $table->decimal('amount_eur', 15, 2);
            $table->decimal('exchange_rate', 10, 6);
            $table->string('exchange_source', 100)->default('ExchangeRate API');
            $table->timestamp('exchange_timestamp')->nullable();

            // Status e timestamps
            $table->enum('status', PaymentRequestStatus::values())->default('pending')->index();
            $table->timestamps();
            $table->softDeletes();

            // Índices para queries comuns
            $table->index(['user_id', 'status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
