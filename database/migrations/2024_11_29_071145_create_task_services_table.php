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
        Schema::create('task_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete(); // Đổi thành nullOnDelete
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->string('service_name')->nullable();
            $table->string('service_unit')->nullable();
            $table->decimal('service_price', 11, 0)->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('money_received', 15, 0)->nullable();
            $table->string('status')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_services');
    }
};
