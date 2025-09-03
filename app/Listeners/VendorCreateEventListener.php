<?php

namespace App\Listeners;

use App\Events\VendorCreateEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class VendorCreateEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VendorCreateEvent $event): void
    {
        $vendor = $event->vendor;
        $password = $event->password;
        $link = $event->link;

        Mail::send('mail.vendor-registration', ['vendor' => $vendor, 'password' => $password, 'link' => $link], function ($message) use ($vendor) {
            $message->to($vendor->user->email);
            $message->subject('New Vendor Registration');
        });
    }
}
