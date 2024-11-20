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
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('files')->onDelete('cascade'); // File yang direvisi
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // User yang melakukan revisi
            $table->string('document');
            $table->string('status')->default('Revisi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};
