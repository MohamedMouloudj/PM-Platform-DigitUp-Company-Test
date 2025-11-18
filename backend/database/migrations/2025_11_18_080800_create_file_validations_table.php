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
        Schema::create('file_validations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('size');
            $table->string('hash')->unique(); // SHA-256
            $table->string('scan_status')->default('pending')->index(); // pending, clean, infected, suspicious
            $table->text('scan_result')->nullable();
            $table->foreignUuid('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_validations');
    }
};
