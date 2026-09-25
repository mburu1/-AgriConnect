<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 15. Carts
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->decimal('subtotal_amount', 12, 2)->default(0.00);
            $table->decimal('delivery_estimate_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // 16. Cart Items
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });

        // 17. Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g. AGC-2026-0001
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('farmer_id')->nullable()->constrained('farmers')->nullOnDelete();
            $table->decimal('subtotal_amount', 12, 2);
            $table->decimal('shipping_fee', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2);
            $table->string('currency')->default('KES');
            $table->enum('status', [
                'PENDING',
                'PAYMENT_PENDING',
                'PAID',
                'PROCESSING',
                'READY_FOR_FULFILLMENT',
                'DISPATCHED',
                'DELIVERED',
                'CANCELLED',
                'REFUNDED',
                'PAYMENT_FAILED'
            ])->default('PENDING');
            $table->enum('payment_status', ['UNPAID', 'PARTIALLY_PAID', 'PAID', 'REFUNDED', 'FAILED'])->default('UNPAID');
            $table->string('payment_method')->default('MPESA'); // MPESA, CASH_ON_DELIVERY, BANK_TRANSFER

            // Delivery Details
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->foreignId('county_id')->nullable()->constrained('counties')->nullOnDelete();
            $table->string('delivery_address');
            $table->text('delivery_instructions')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 18. Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->string('product_title');
            $table->string('unit_measure');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // 19. Order Status History
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // 20. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('payment_reference')->unique(); // Internal payment identifier
            $table->string('payment_method')->default('MPESA'); // MPESA
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('KES');
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED', 'CANCELLED', 'REFUNDED'])->default('PENDING');
            $table->timestamps();
        });

        // 21. Payment Transactions (M-Pesa STK & Callbacks)
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->string('gateway')->default('MPESA_DARAJA');
            $table->string('merchant_request_id')->nullable()->index();
            $table->string('checkout_request_id')->nullable()->index();
            $table->string('mpesa_receipt_number')->nullable()->unique();
            $table->string('phone_number')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->integer('result_code')->nullable();
            $table->string('result_desc')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
