<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xray_transport_splithttp', function (Blueprint $table) {
            $table->id();
            $table->morphs('handler');
            $table->string('path')->default('/');
            $table->string('host')->nullable();
            $table->integer('max_upload_size')->default(1000000);
            $table->integer('max_concurrent_uploads')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xray_transport_splithttp');
    }
};
