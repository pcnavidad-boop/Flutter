<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // View all notifications
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    // Fetch unread notifications (for navbar dropdown)
    public function fetchUnread()
    {
        return response()->json([
            'unread_count' => auth()->user()->unreadNotifications->count(),
            'notifications' => auth()->user()->unreadNotifications->take(20),
        ]);
    }

    // Mark a notification as read
    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return redirect()->back();
    }

    // Mark all notifications as read
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    // Delete a notification
    public function destroy($id)
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail()
            ->delete();

        return redirect()->back()->with('success', 'Notification deleted.');
    }

    // Open a notification and redirect based on its type
    public function open($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        $data = $notification->data;

        // Room Booking
        if ($data['title'] === 'New Room Booking') {
            return redirect()->route('room_booking.view', $data['booking_id']);
        }

        // Service Booking
        if ($data['title'] === 'New Service Booking') {
            return redirect()->route('service_booking.view', $data['booking_id']);
        }

        return redirect()->back();
    }
}
