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
        Schema::create('document_formats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('country', 2);
            $table->unsignedSmallInteger('width_mm');
            $table->unsignedSmallInteger('height_mm');
            $table->unsignedSmallInteger('dpi')->default(300);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_formats');
    }
};
