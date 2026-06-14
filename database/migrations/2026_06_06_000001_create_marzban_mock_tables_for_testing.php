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
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->string('username', 255)->unique();
                $table->string('status', 50)->default('active');
                $table->bigInteger('used_traffic')->nullable();
                $table->bigInteger('data_limit')->nullable();
                $table->integer('expire')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->integer('admin_id')->nullable();
                $table->string('data_limit_reset_strategy', 50)->default('no_reset');
                $table->dateTime('sub_revoked_at')->nullable();
                $table->string('note', 500)->nullable();
                $table->dateTime('sub_updated_at')->nullable();
                $table->string('sub_last_user_agent', 512)->nullable();
                $table->dateTime('online_at')->nullable();
                $table->dateTime('edit_at')->nullable();
                $table->dateTime('on_hold_timeout')->nullable();
                $table->bigInteger('on_hold_expire_duration')->nullable();
                $table->integer('auto_delete_in_days')->nullable();
                $table->dateTime('last_status_change')->nullable();
            });
        }

        if (!Schema::hasTable('proxies')) {
            Schema::create('proxies', function (Blueprint $table) {
                $table->integer('id')->autoIncrement();
                $table->integer('user_id')->nullable();
                $table->string('type', 50);
                $table->json('settings');
                
                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proxies');
        Schema::dropIfExists('users');
    }
};
