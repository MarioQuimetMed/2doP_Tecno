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
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('pagofacil_transaction_id')->nullable()->after('referencia_comprobante');
            $table->string('company_transaction_id')->nullable()->after('pagofacil_transaction_id');
            $table->text('qr_base64')->nullable()->after('company_transaction_id');
            $table->string('checkout_url')->nullable()->after('qr_base64');
            $table->string('deep_link')->nullable()->after('checkout_url');
            $table->string('qr_content_url')->nullable()->after('deep_link');
            $table->string('universal_url')->nullable()->after('qr_content_url');
            $table->timestamp('qr_expiration_date')->nullable()->after('universal_url');
            $table->string('payment_status')->default('PENDING')->after('qr_expiration_date');
            // Valores: PENDING, PAID, EXPIRED, CANCELLED
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'pagofacil_transaction_id',
                'company_transaction_id',
                'qr_base64',
                'checkout_url',
                'deep_link',
                'qr_content_url',
                'universal_url',
                'qr_expiration_date',
                'payment_status',
            ]);
        });
    }
};
