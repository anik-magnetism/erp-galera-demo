<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
