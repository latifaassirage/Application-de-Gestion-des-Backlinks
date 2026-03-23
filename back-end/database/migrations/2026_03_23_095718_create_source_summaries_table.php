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
        Schema::create('source_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('website')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('link_type')->nullable();
            $table->string('contact_email')->nullable();
            $table->integer('spam')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('source_summaries');
    }
};
