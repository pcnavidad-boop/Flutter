<?php

namespace App\Services;

use App\Models\RoomBooking;
use App\Models\ServiceBooking;
use Carbon\Carbon;

class BookingLifecycleService
{
    /**
     * ----------------------------------------------------------------------
     * ITEM VALIDATION
     * ----------------------------------------------------------------------
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
     * BOOKING EDIT RULES
     * ----------------------------------------------------------------------
     */
    public function assertBookingEditable($booking): void
    {
        if (in_array($booking->booking_status, ['cancelled','checked_out','completed'])) {
            throw new \Exception("Cannot modify a {$booking->booking_status} booking.");
        }

        if ($booking->payments()->exists()) {
            throw new \Exception("Cannot modify booking because payments already exist.");
        }

        $this->assertItemBookable($this->resolveItem($booking));
    }

    /**
     * ----------------------------------------------------------------------
     * PAYMENT VALIDATION
     * ----------------------------------------------------------------------
     */
    public function assertBookingPayable($booking): void
    {
        if (in_array($booking->booking_status, ['cancelled','checked_out','completed'])) {
            throw new \Exception("Cannot add payment to a {$booking->booking_status} booking.");
        }

        $this->assertItemBookable($this->resolveItem($booking));
    }

    /**
     * ----------------------------------------------------------------------
     * PAYMENT ROLLBACK VALIDATION
     * ----------------------------------------------------------------------
     */
    public function assertBookingMutableForPaymentRollback($booking): void
    {
        if (in_array($booking->booking_status, ['completed','checked_out','cancelled'])) {
            throw new \Exception("Cannot rollback payment for a {$booking->booking_status} booking.");
        }

        $this->assertItemBookable($this->resolveItem($booking));
    }

    /**
     * ----------------------------------------------------------------------
     * STATUS TRANSITIONS
     * ----------------------------------------------------------------------
     */
    public function assertStatusTransition($booking, string $newStatus): void
    {
        $old = $booking->booking_status;

        $allowed = [
            'pending'    => ['pending','confirmed','cancelled'],
            'confirmed'  => ['confirmed','checked_in','cancelled'],
            'checked_in' => ['checked_in','checked_out'],
        ];

        if (!isset($allowed[$old]) || !in_array($newStatus, $allowed[$old])) {
            throw new \Exception("Cannot change status from {$old} to {$newStatus}.");
        }
    }

    /**
     * ----------------------------------------------------------------------
     * 🚫 CANCEL RULE — REFUND REQUIRED
     * ----------------------------------------------------------------------
     */
    public function assertCancelable($booking): void
    {
        if (
            $booking->payments()
                ->where('status', 'completed')
                ->exists()
        ) {
            throw new \Exception(
                'Refund payments before cancelling this booking.'
            );
        }
    }

    /**
     * ----------------------------------------------------------------------
     * DATE & TIME VALIDATION
     * ----------------------------------------------------------------------
     */
    public function assertValidSchedule(array $data): void
    {
        if (isset($data['start_date'], $data['end_date'])) {
            $start = Carbon::parse($data['start_date']);
            $end   = Carbon::parse($data['end_date']);

            if ($end->lt($start)) {
                throw new \Exception("End date must be after start date.");
            }
        }

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
     * Resolve associated item
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
