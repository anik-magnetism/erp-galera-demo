<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
