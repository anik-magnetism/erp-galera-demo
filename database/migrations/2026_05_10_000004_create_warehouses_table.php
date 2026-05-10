<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
};
