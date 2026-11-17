<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Hanya MENAMBAH: kolom alasan penolakan + tabel notifikasi. Tidak ada data/tabel lama yang dihapus.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('loans', 'reject_reason')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->string('reject_reason', 255)->nullable()->after('note');
            });
        }

        if (! Schema::hasTable('user_notifications')) {
            Schema::create('user_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('loan_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 30);          // submitted, approved, rejected, returned, due_soon, overdue
                $table->string('title');
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'read_at']);
                $table->index(['loan_id', 'type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
        if (Schema::hasColumn('loans', 'reject_reason')) {
            Schema::table('loans', fn (Blueprint $t) => $t->dropColumn('reject_reason'));
        }
    }
};
