<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PaymentService;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.notifications.index', compact('notifications'));
    }

    public function fetchUnread()
    {
        $user = auth()->user();

        return response()->json([
            'unread_count'  => $user->unreadNotifications->count(),
            'notifications' => $user->unreadNotifications->take(20),
        ]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();
        return redirect()->back();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->delete();

        return redirect()->back()->with('success', 'Notification deleted.');
    }

    public function open($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $data = $notification->data ?? [];
        if (!isset($data['reference'])) {
            return redirect()->back()->with('error', 'Invalid notification data.');
        }

        $reference = $data['reference'];
        $type = PaymentService::detectBookingType($reference);
        if (!$type) {
            return redirect()->back()->with('error', 'Invalid booking reference.');
        }

        $booking = PaymentService::findBookingByReference($type, $reference);
        if (!$booking) {
            return redirect()->back()->with('error', 'This booking no longer exists.');
        }

        return match ($type) {
            'room' => redirect()->route('admin.room_bookings.index', ['ref' => $reference]),
            'service' => redirect()->route('admin.service_bookings.index', ['ref' => $reference]),
            default => redirect()->back()->with('error', 'Unsupported notification type.'),
        };
    }
}
