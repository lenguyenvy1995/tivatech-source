<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('quote_domain_id'); // Thêm dòng này
            $table->text('keywords');
            $table->string('top_position');
            $table->string('region');
            $table->string('keyword_type');
            $table->string('campaign_type');
            $table->string('status')->default('pending');
            $table->timestamps();
    
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('quote_domain_id')->references('id')->on('quote_domains')->onDelete('cascade'); // Thêm dòng này
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
