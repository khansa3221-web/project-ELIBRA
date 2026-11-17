<?php

namespace App\Console\Commands;

use App\Support\DueReminders;
use Illuminate\Console\Command;

class SendDueReminders extends Command
{
    protected $signature = 'elibra:reminders';
    protected $description = 'Buat notifikasi pengingat tenggat pengembalian buku';

    public function handle(): int
    {
        $this->info('Memeriksa '.DueReminders::run().' peminjaman mendekati/melewati tenggat.');

        return self::SUCCESS;
    }
}
