<?php

namespace App\Support;

use App\Models\Loan;
use App\Models\UserNotification;

class DueReminders
{
    /** Buat pengingat tenggat (H-2 s/d hari H) dan keterlambatan. Aman dipanggil berulang. */
    public static function run(?int $userId = null): int
    {
        $query = Loan::with('book')->returnable()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', today()->addDays(2));

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $n = 0;
        foreach ($query->get() as $loan) {
            $overdue = $loan->due_date->lt(today());
            $key     = ($overdue ? 'overdue:' : 'due:').$loan->id;

            UserNotification::pushOnce(
                $loan->user_id, $key,
                $overdue ? 'overdue' : 'due_soon',
                $overdue ? 'Buku terlambat dikembalikan' : 'Tenggat pengembalian segera',
                '"'.$loan->book->title.'" '.($overdue ? 'melewati tenggat ' : 'jatuh tempo ').$loan->due_date->translatedFormat('d M Y').'.',
                route('student.loans')
            );
            $n++;
        }

        return $n;
    }
}
