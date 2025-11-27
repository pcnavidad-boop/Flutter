<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;

class NotificationController extends Controller
{
    // Show all notifications for the authenticated user
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    // AJAX: Fetch unread notifications (limit 20)
    public function fetchUnread()
    {
        $user = auth()->user();

        return response()->json([
            'unread_count'  => $user->unreadNotifications->count(),
            'notifications' => $user->unreadNotifications->take(20),
        ]);
    }

    // Mark a single notification as read
    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return redirect()->back();
    }

    // Mark all unread notifications as read
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    // Delete a notification
    public function destroy($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        return redirect()
            ->back()
            ->with('success', 'Notification deleted.');
    }

    // Open a notification and redirect to its booking page
    public function open($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        // Mark as read immediately
        $notification->markAsRead();

        $data = $notification->data ?? [];

        // Must contain required keys
        if (!isset($data['reference'], $data['title'])) {
            return redirect()
                ->back()
                ->with('error', 'Invalid notification data.');
        }

        $reference = $data['reference'];

        // Determine booking type based on ref prefix (RB-xxxx, SB-xxxx)
        $type = PaymentService::detectBookingType($reference);

        if (!$type) {
            return redirect()
                ->back()
                ->with('error', 'Invalid booking reference.');
        }

        // Check if booking still exists
        $booking = PaymentService::findBookingByReference($type, $reference);

        if (!$booking) {
            return redirect()
                ->back()
                ->with('error', 'This booking no longer exists.');
        }

        // Determine redirect target
        $title = strtolower($data['title']);

        if (str_contains($title, 'room')) {
            return redirect()->route('room_booking.index_page', [
                'ref' => $reference,
            ]);
        }

        if (str_contains($title, 'service')) {
            return redirect()->route('service_booking.index_page', [
                'ref' => $reference,
            ]);
        }

        // Fallback
        return redirect()->back();
    }
}
