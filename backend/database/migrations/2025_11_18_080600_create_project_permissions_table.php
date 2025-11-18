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
        Schema::create('project_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('permission')->index(); // read, write, delete, manage
            $table->foreignUuid('granted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('granted_at')->useCurrent();
            $table->timestamps();

            // Prevent duplicate permissions for same user-project-permission combination
            $table->unique(['project_id', 'user_id', 'permission']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_permissions');
    }
};
