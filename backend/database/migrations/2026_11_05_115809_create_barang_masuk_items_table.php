<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang_masuk_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_masuk_id')->constrained('barang_masuks')->cascadeOnDelete();
            $table->string('nama_barang', 200);
            $table->unsignedBigInteger('harga');
            $table->unsignedInteger('jumlah');
            $table->string('satuan', 40);
            $table->unsignedBigInteger('total');
            $table->date('tgl_expired')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_masuk_items');
    }
};
