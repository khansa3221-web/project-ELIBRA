<?php

namespace App\Console\Commands;

use App\Services\LoanService;
use Illuminate\Console\Command;

class SendLoanReminders extends Command
{
    protected $signature = 'elibra:reminders';

    protected $description = 'Tandai peminjaman terlambat dan kirim pengingat jatuh tempo (H-2 s.d. hari H).';

    public function handle(LoanService $loans): int
    {
        $r = $loans->remind();
        $this->info("Ditandai terlambat: {$r['terlambat']}, pengingat dikirim: {$r['diingatkan']}");

        return self::SUCCESS;
    }
}
