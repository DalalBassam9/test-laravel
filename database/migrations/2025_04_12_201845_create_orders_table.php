<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\OrderStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();


            $table->foreignId('address_id')
                ->nullable()
                ->constrained('addresses')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->text('notes')->nullable();
            
            $table->enum('status', OrderStatusEnum::getValues())
                ->default(OrderStatusEnum::Pending->value);

            $table->decimal('total_price', 20, 2);
            $table->decimal('delivery_fee', 10, 2)->default(15);
            
            $table->enum('payment_method', ['cash', 'click']);
            $table->string('click_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
