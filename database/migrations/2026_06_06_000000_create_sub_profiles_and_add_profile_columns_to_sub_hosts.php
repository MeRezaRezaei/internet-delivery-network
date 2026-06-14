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
        // 1. Create sub_profiles table
        if (!Schema::hasTable('sub_profiles')) {
            Schema::create('sub_profiles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type'); // 'tls', 'xhttp', 'xmux'
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        // 2. Add columns to sub_hosts table
        Schema::table('sub_hosts', function (Blueprint $table) {
            if (!Schema::hasColumn('sub_hosts', 'tls_profile_id')) {
                $table->foreignId('tls_profile_id')->nullable()->constrained('sub_profiles')->nullOnDelete();
            }
            if (!Schema::hasColumn('sub_hosts', 'xhttp_profile_id')) {
                $table->foreignId('xhttp_profile_id')->nullable()->constrained('sub_profiles')->nullOnDelete();
            }
            if (!Schema::hasColumn('sub_hosts', 'xmux_profile_id')) {
                $table->foreignId('xmux_profile_id')->nullable()->constrained('sub_profiles')->nullOnDelete();
            }
            if (!Schema::hasColumn('sub_hosts', 'download_host_id')) {
                $table->foreignId('download_host_id')->nullable()->constrained('sub_hosts')->nullOnDelete();
            }
            if (!Schema::hasColumn('sub_hosts', 'is_dev')) {
                $table->boolean('is_dev')->default(false)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_hosts', function (Blueprint $table) {
            $table->dropForeign(['tls_profile_id']);
            $table->dropForeign(['xhttp_profile_id']);
            $table->dropForeign(['xmux_profile_id']);
            $table->dropForeign(['download_host_id']);
            $table->dropColumn(['tls_profile_id', 'xhttp_profile_id', 'xmux_profile_id', 'download_host_id', 'is_dev']);
        });

        Schema::dropIfExists('sub_profiles');
    }
};
