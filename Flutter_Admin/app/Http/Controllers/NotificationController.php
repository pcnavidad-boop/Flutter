<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;

class NotificationController extends Controller
{
    // ---------------------------------------------------------
    // List notifications
    // ---------------------------------------------------------
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    // ---------------------------------------------------------
    // AJAX: Fetch unread notifications (max 20)
    // ---------------------------------------------------------
    public function fetchUnread()
    {
        $user = auth()->user();

        return response()->json([
            'unread_count'  => $user->unreadNotifications->count(),
            'notifications' => $user->unreadNotifications->take(20),
        ]);
    }

    // ---------------------------------------------------------
    // Mark single notification as read
    // ---------------------------------------------------------
    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return redirect()->back();
    }

    // ---------------------------------------------------------
    // Mark ALL unread notifications as read
    // ---------------------------------------------------------
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    // ---------------------------------------------------------
    // Delete a notification (soft delete)
    // ---------------------------------------------------------
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

    // ---------------------------------------------------------
    // Open notification and redirect to the booking it refers to
    // ---------------------------------------------------------
    public function open($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        $data = $notification->data ?? [];

        // Validate expected payload
        if (!isset($data['reference'])) {
            return redirect()->back()->with('error', 'Invalid notification data.');
        }

        $reference = $data['reference'];

        // Determine booking type by reference prefix
        $type = PaymentService::detectBookingType($reference);

        if (!$type) {
            return redirect()->back()->with('error', 'Invalid booking reference.');
        }

        // Find booking
        $booking = PaymentService::findBookingByReference($type, $reference);

        if (!$booking) {
            return redirect()->back()->with('error', 'This booking no longer exists.');
        }

        // Routing based on type, NOT title string
        return match ($type) {
            'room'    => redirect()->route('room_booking.index_page', ['ref' => $reference]),
            'service' => redirect()->route('service_booking.index_page', ['ref' => $reference]),
            default   => redirect()->back()->with('error', 'Unsupported notification type.'),
        };
    }
}
