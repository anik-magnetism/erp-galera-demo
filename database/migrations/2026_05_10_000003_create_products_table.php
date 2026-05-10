<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('sku')->nullable()->index();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
