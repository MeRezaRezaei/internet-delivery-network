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
        Schema::create('xray_transport_splithttp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('handler_id');
            $table->string('handler_type'); // Morphic
            $table->string('host')->nullable();
            $table->string('path')->default('/');
            $table->string('mode')->default('streaming');
            $table->json('headers')->nullable();
            $table->string('x_padding_range')->nullable();
            $table->boolean('x_padding_obfs_mode')->default(false);
            $table->timestamps();
        });

        Schema::create('xray_transport_httpupgrade', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('handler_id');
            $table->string('handler_type'); // Morphic
            $table->string('host')->nullable();
            $table->string('path')->default('/');
            $table->json('headers')->nullable();
            $table->boolean('accept_proxy_protocol')->default(false);
            $table->unsignedInteger('ed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xray_transport_httpupgrade');
        Schema::dropIfExists('xray_transport_splithttp');
    }
};
