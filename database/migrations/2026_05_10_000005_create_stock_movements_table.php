<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->integer('quantity');
            $table->enum('movement_type', ['in', 'out'])->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
};
