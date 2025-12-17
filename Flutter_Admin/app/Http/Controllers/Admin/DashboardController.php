<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Service;
use App\Models\RoomBooking;
use App\Models\ServiceBooking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /* =========================
           COMMON TIME REFERENCES
        ========================== */
        $now        = Carbon::now();
        $from30Days = $now->copy()->subDays(30);
        $year       = $now->year;

        /* =========================
           ROW 1 — KPI CARDS (30 DAYS)
        ========================== */
        $pendingBookings =
            RoomBooking::where('booking_status', 'pending')
                ->where('booking_date', '>=', $from30Days)
                ->count()
          + ServiceBooking::where('booking_status', 'pending')
                ->where('booking_date', '>=', $from30Days)
                ->count();

        $confirmedBookings =
            RoomBooking::whereIn('booking_status', ['confirmed', 'checked_in', 'checked_out'])
                ->where('booking_date', '>=', $from30Days)
                ->count()
          + ServiceBooking::whereIn('booking_status', ['confirmed', 'completed'])
                ->where('booking_date', '>=', $from30Days)
                ->count();

        $cancelledBookings =
            RoomBooking::where('booking_status', 'cancelled')
                ->where('booking_date', '>=', $from30Days)
                ->count()
          + ServiceBooking::where('booking_status', 'cancelled')
                ->where('booking_date', '>=', $from30Days)
                ->count();

        $availableRooms    = Room::available()->active()->count();
        $availableServices = Service::available()->active()->count();

        /* =========================
           CHART 1 — CONFIRMED BOOKINGS
           (ROOM vs SERVICE, MONTHLY)
        ========================== */
        $roomBookingsMonthly = RoomBooking::select(
                DB::raw('MONTH(booking_date) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('booking_date', $year)
            ->whereIn('booking_status', ['confirmed', 'checked_in', 'checked_out'])
            ->groupBy(DB::raw('MONTH(booking_date)'))
            ->pluck('total', 'month');

        $serviceBookingsMonthly = ServiceBooking::select(
                DB::raw('MONTH(booking_date) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('booking_date', $year)
            ->whereIn('booking_status', ['confirmed', 'completed'])
            ->groupBy(DB::raw('MONTH(booking_date)'))
            ->pluck('total', 'month');

        $months = collect(range(1, 12));

        $confirmedBookingsMonthly = [
            'labels'   => $months->map(fn ($m) =>
                Carbon::create()->month($m)->format('M')
            ),
            'rooms'    => $months->map(fn ($m) =>
                $roomBookingsMonthly[$m] ?? 0
            ),
            'services' => $months->map(fn ($m) =>
                $serviceBookingsMonthly[$m] ?? 0
            ),
        ];

        /* =========================
           CHART 2 — PAYMENTS
           COMPLETED vs REFUNDED
        ========================== */
        $paymentsCompletedMonthly = Payment::select(
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->whereYear('paid_at', $year)
            ->where('status', 'completed')
            ->groupBy(DB::raw('MONTH(paid_at)'))
            ->pluck('total', 'month');

        $paymentsRefundedMonthly = Payment::select(
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->whereYear('paid_at', $year)
            ->where('status', 'refunded')
            ->groupBy(DB::raw('MONTH(paid_at)'))
            ->pluck('total', 'month');

        $paymentsMonthly = [
            'labels'    => $months->map(fn ($m) =>
                Carbon::create()->month($m)->format('M')
            ),
            'completed' => $months->map(fn ($m) =>
                round($paymentsCompletedMonthly[$m] ?? 0, 2)
            ),
            'refunded'  => $months->map(fn ($m) =>
                round($paymentsRefundedMonthly[$m] ?? 0, 2)
            ),
        ];

        /* =========================
           RIGHT COLUMN — RECENT BOOKINGS
           (ROOM + SERVICE)
        ========================== */
        $recentRoomBookings = RoomBooking::latest('booking_date')
            ->take(5)
            ->get();

        $recentServiceBookings = ServiceBooking::latest('booking_date')
            ->take(5)
            ->get();

        $recentBookings = $recentRoomBookings
            ->merge($recentServiceBookings)
            ->sortByDesc('booking_date')
            ->take(8)
            ->values();

        /* =========================
           RETURN VIEW
        ========================== */
        return view('admin.dashboard', compact(
            'pendingBookings',
            'confirmedBookings',
            'cancelledBookings',
            'availableRooms',
            'availableServices',
            'confirmedBookingsMonthly',
            'paymentsMonthly',
            'recentBookings'
        ));
    }
}
