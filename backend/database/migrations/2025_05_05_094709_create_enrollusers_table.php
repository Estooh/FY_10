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
        Schema::create('enrollusers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->enum('biometric_method', ['face', 'fingerprint']);

            // Storing face descriptor as JSON, better than longText
            $table->json('face_descriptor')->nullable();

            // Store file paths (not base64 blobs) to avoid bloated database
            $table->string('face_image')->nullable();
            $table->string('fingerprint_template')->nullable();

            // Fingerprint credential ID can be long, keep it as text
            $table->text('fingerprint_credential')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollusers');
    }
};
