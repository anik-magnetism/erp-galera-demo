<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type')->index();
            $table->unsignedBigInteger('auditable_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event')->index();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
        });

        // Suggest partitioning by year for very large audit_logs table
        // ALTER TABLE audit_logs PARTITION BY RANGE (YEAR(created_at)) (
        //   PARTITION p2024 VALUES LESS THAN (2025),
        //   PARTITION p2025 VALUES LESS THAN (2026),
        //   PARTITION pmax VALUES LESS THAN (MAXVALUE)
        // );
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
