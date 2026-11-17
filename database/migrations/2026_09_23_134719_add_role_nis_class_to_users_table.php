<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('siswa')->after('password');
            }
            if (! Schema::hasColumn('users', 'nis')) {
                $table->string('nis')->nullable()->unique()->after('role');
            }
            if (! Schema::hasColumn('users', 'class')) {
                $table->string('class')->nullable()->after('nis');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'nis', 'class']);
        });
    }
};