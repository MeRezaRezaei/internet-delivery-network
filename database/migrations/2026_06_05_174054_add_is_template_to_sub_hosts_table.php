<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_hosts', function (Blueprint $blueprint) {
            $blueprint->boolean('is_template')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('sub_hosts', function (Blueprint $blueprint) {
            $blueprint->dropColumn('is_template');
        });
    }
};
