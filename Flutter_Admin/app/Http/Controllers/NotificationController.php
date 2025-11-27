<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;

class NotificationController extends Controller
{
    // View notifications
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    // AJAX unread fetch
    public function fetchUnread()
    {
        $user = auth()->user();

        return response()->json([
            'unread_count'  => $user->unreadNotifications->count(),
            'notifications' => $user->unreadNotifications->take(20),
        ]);
    }

    // Mark one as read
    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();
        return redirect()->back();
    }

    // Mark all as read
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    // Delete
    public function destroy($id)
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail()
            ->delete();

        return redirect()->back()->with('success', 'Notification deleted.');
    }

    // Open → redirect to booking page
    public function open($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        $data = $notification->data ?? [];

        if (!isset($data['reference'], $data['title'])) {
            return redirect()->back()->with('error', 'Invalid notification.');
        }

        $ref = $data['reference'];

        // Strictly validate reference
        if (!PaymentService::detectBookingType($ref)) {
            return redirect()->back()->with('error', 'Invalid booking reference.');
        }

        if (str_contains(strtolower($data['title']), 'room')) {
            return redirect()->route('room_booking.index_page', ['ref' => $ref]);
        }

        if (str_contains(strtolower($data['title']), 'service')) {
            return redirect()->route('service_booking.index_page', ['ref' => $ref]);
        }

        return redirect()->back();
    }
}
