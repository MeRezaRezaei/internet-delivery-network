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
        Schema::create('sub_hosts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->integer('port');
            $table->string('sni')->nullable();
            $table->string('host')->nullable();
            $table->string('path')->default('/');
            $table->string('mode')->default('packet-up');
            $table->string('security')->default('tls');
            $table->boolean('insecure')->default(false);
            $table->string('alpn')->default('h2');
            $table->json('extra')->nullable();
            $table->string('type')->default('direct'); // direct, reverse
            $table->boolean('is_active')->default(true);
            $table->string('remark_prefix')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_hosts');
    }
};
