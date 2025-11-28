<?php

namespace App\Services;

class BookingLifecycleService
{
    /**
     * Ensure booking edit is allowed.
     *
     * Throws \Exception on violation.
     */
    public function assertBookingEditable($booking): void
    {
        if (in_array($booking->booking_status, ['cancelled', 'checked_out', 'completed'])) {
            throw new \Exception("Cannot modify a {$booking->booking_status} booking.");
        }

        if ($booking->payments()->exists()) {
            throw new \Exception("Cannot modify booking because payments already exist.");
        }

        // Use payable polymorphic relation if available, else fallback to specific relations
        $item = $this->getPayableFromBooking($booking);

        if ($item) {
            if (property_exists($item, 'is_archived') && $item->is_archived) {
                throw new \Exception("Cannot modify a booking for an archived item.");
            }

            if (property_exists($item, 'status') && $item->status === 'maintenance') {
                throw new \Exception("Cannot modify booking while item is under maintenance.");
            }
        }
    }

    /**
     * Validate status transitions (throws on invalid).
     */
    public function assertStatusTransition($booking, string $newStatus): void
    {
        $old = $booking->booking_status;

        $allowed = [
            'confirmed' => ['confirmed', 'checked_in', 'cancelled'],
            'checked_in' => ['checked_in', 'checked_out'],
        ];

        if (!isset($allowed[$old]) || !in_array($newStatus, $allowed[$old])) {
            throw new \Exception("Cannot change status from {$old} to {$newStatus}.");
        }
    }

    /**
     * Validate date/time logic of an update payload.
     */
    public function assertValidSchedule(array $data): void
    {
        if (isset($data['start_date'], $data['end_date']) &&
            $data['end_date'] < $data['start_date']) {
            throw new \Exception("End date must be after start date.");
        }

        if (isset($data['start_time'], $data['end_time']) &&
            $data['end_time'] <= $data['start_time']) {
            throw new \Exception("End time must be after start time.");
        }
    }

    /**
     * Helper: get the related item to the booking (payable / room / service)
     */
    protected function getPayableFromBooking($booking)
    {
        // Prefer polymorphic relationship 'payable' if exists
        if (method_exists($booking, 'payable')) {
            try {
                return $booking->payable;
            } catch (\Throwable $e) {
                // ignore and fallback
            }
        }

        if (method_exists($booking, 'room') && $booking->room) {
            return $booking->room;
        }

        if (method_exists($booking, 'service') && $booking->service) {
            return $booking->service;
        }

        return null;
    }
}
