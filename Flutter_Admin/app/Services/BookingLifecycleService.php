<?php

namespace App\Services;

use App\Models\RoomBooking;
use App\Models\ServiceBooking;
use Carbon\Carbon;

class BookingLifecycleService
{
    /**
     * ----------------------------------------------------------------------
     *  ITEM VALIDATION (Used by Customer + Webhook + Admin)
     * ----------------------------------------------------------------------
     * Ensures the ROOM or SERVICE can be booked (not archived, not in maintenance)
     */
    public function assertItemBookable($item): void
    {
        if (!$item) {
            throw new \Exception("Item not found.");
        }

        if (isset($item->is_archived) && $item->is_archived) {
            throw new \Exception("This item is archived.");
        }

        if (isset($item->status) && $item->status === 'maintenance') {
            throw new \Exception("This item is under maintenance.");
        }
    }

    /**
     * ----------------------------------------------------------------------
     *  BOOKING EDIT RULES (Admin only)
     * ----------------------------------------------------------------------
     */
    public function assertBookingEditable($booking): void
    {
        if (in_array($booking->booking_status, ['cancelled', 'checked_out', 'completed'])) {
            throw new \Exception("Cannot modify a {$booking->booking_status} booking.");
        }

        if ($booking->payments()->exists()) {
            throw new \Exception("Cannot modify booking because payments already exist.");
        }

        $item = $this->resolveItem($booking);
        $this->assertItemBookable($item);
    }

    /**
     * ----------------------------------------------------------------------
     *  PAYMENT VALIDATION (Admin + Customer + Webhook)
     * ----------------------------------------------------------------------
     * Replaces old assertBookingPayable and assertBookingEditableForPayment
     */
    public function assertBookingPayable($booking): void
    {
        if (in_array($booking->booking_status, ['cancelled', 'checked_out', 'completed'])) {
            throw new \Exception("Cannot add payment to a {$booking->booking_status} booking.");
        }

        // Ensure related room/service is valid
        $item = $this->resolveItem($booking);
        $this->assertItemBookable($item);
    }

    /**
     * ----------------------------------------------------------------------
     *  PAYMENT ROLLBACK VALIDATION (Admin only)
     * ----------------------------------------------------------------------
     */
    public function assertBookingMutableForPaymentRollback($booking): void
    {
        if (in_array($booking->booking_status, ['completed', 'checked_out', 'cancelled'])) {
            throw new \Exception("Cannot rollback payment for a {$booking->booking_status} booking.");
        }

        $item = $this->resolveItem($booking);
        $this->assertItemBookable($item);
    }

    /**
     * ----------------------------------------------------------------------
     *  STATUS TRANSITION RULES (Admin only)
     * ----------------------------------------------------------------------
     */
    public function assertStatusTransition($booking, string $newStatus): void
    {
        $old = $booking->booking_status;

        $allowed = [
            'pending'    => ['pending', 'confirmed', 'cancelled'],
            'confirmed'  => ['confirmed', 'checked_in', 'cancelled'],
            'checked_in' => ['checked_in', 'checked_out'],
        ];

        if (!isset($allowed[$old]) || !in_array($newStatus, $allowed[$old])) {
            throw new \Exception("Cannot change status from {$old} to {$newStatus}.");
        }
    }

    /**
     * ----------------------------------------------------------------------
     *  DATE & TIME VALIDATION (Admin + Customer)
     * ----------------------------------------------------------------------
     */
    public function assertValidSchedule(array $data): void
    {
        // DATE validation
        if (isset($data['start_date'], $data['end_date'])) {
            $start = Carbon::parse($data['start_date']);
            $end   = Carbon::parse($data['end_date']);

            if ($end->lt($start)) {
                throw new \Exception("End date must be after start date.");
            }
        }

        // TIME validation
        if (isset($data['start_time'], $data['end_time'])) {
            $start = Carbon::createFromFormat('H:i', substr($data['start_time'], 0, 5));
            $end   = Carbon::createFromFormat('H:i', substr($data['end_time'], 0, 5));

            if ($end->lte($start)) {
                throw new \Exception("End time must be after start time.");
            }
        }
    }

    /**
     * ----------------------------------------------------------------------
     * Resolve associated item (room or service)
     * ----------------------------------------------------------------------
     */
    private function resolveItem($booking)
    {
        if ($booking instanceof RoomBooking) {
            return $booking->room;
        }

        if ($booking instanceof ServiceBooking) {
            return $booking->service;
        }

        return null;
    }
}

