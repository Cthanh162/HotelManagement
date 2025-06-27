<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
class AutoCancelBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $bookingId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bookingId)
    {
        $this->bookingId = $bookingId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $booking = Booking::with('room')->find($this->bookingId);

        if (!$booking) return;

        if ($booking->status === 'pending_payment' && $booking->paymentStatus === 'pending') {
            DB::transaction(function () use ($booking) {
                $booking->update(['status' => 'timeout']);
                if ($booking->room) {
                    $booking->room->update(['status' => 'available']);
                }
            });
        }
    }
}
