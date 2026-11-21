<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zona_parkir', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->enum('status', ['boleh', 'dilarang']);
            $table->json('koordinat'); // exp: [[−7.743,113.215],[−7.744,113.216],...]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zona_parkir');
    }
};
