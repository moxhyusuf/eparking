<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jukir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('user');
            $table->string('no_absen');
            $table->string('nama');
            $table->string('koordinator_wasda');
            $table->string('sub_koordinator_wasda');
            $table->string('lokasi');
            $table->integer('tahun');
            $table->integer('bulan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jukir');
    }
};
