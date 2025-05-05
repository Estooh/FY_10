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
    Schema::create('enrollusers', function (Blueprint $table) {
        $table->id();
        $table->string('full_name');
        $table->string('email')->unique();
        $table->enum('biometric_method', ['face', 'fingerprint']);
        $table->longText('face_descriptor')->nullable(); // stored as JSON
        $table->longText('face_image')->nullable();       // base64 image
        $table->longText('fingerprint_template')->nullable(); // base64 image
        $table->string('fingerprint_credential')->nullable(); // credential ID
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
