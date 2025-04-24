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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quote_request_id');
            $table->unsignedBigInteger('user_id'); // Người tạo báo giá
            $table->decimal('estimated_cost', 15, 2);
            $table->text('details')->nullable();
            $table->string('status')->default('sent');
            $table->timestamps();
    
            $table->foreign('quote_request_id')->references('id')->on('quote_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
