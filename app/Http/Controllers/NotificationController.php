<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Support\DueReminders;

class NotificationController extends Controller
{
    public function index()
    {
        DueReminders::run(auth()->id());

        $notifications = UserNotification::where('user_id', auth()->id())
            ->latest()->paginate(15);

        return view('student.notifications', compact('notifications'));
    }

    public function read(UserNotification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->update(['read_at' => $notification->read_at ?? now()]);

        return $notification->url ? redirect($notification->url) : back();
    }

    public function readAll()
    {
        UserNotification::where('user_id', auth()->id())->unread()->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
