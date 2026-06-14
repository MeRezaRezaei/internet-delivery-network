<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "marzban";

    public function up(): void
    {
        Schema::dropIfExists("proxies");
        Schema::dropIfExists("users");

        Schema::create("users", function (Blueprint $table) {
            $table->id();
            $table->string("username", 255)->unique();
            $table->string("status", 50)->default("active");
        });

        Schema::create("proxies", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id")->nullable();
            $table->string("type", 50);
            $table->json("settings");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("proxies");
        Schema::dropIfExists("users");
    }
};
