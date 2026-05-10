<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 14, 2);
            $table->timestamps();

            $table->index(['order_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
