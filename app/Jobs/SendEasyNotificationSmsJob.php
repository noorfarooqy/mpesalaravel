<?php

namespace Noorfarooqy\MpesaLaravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Noorfarooqy\EasyNotifications\Services\SmsServices;

class SendEasyNotificationSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $to;
    public $content;
    public function __construct($to, $content)
    {
        $this->to = $to;
        $this->content = $content;
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $smsServices = new SmsServices();
        $is_sent = $smsServices->SendSmsUsingOnfon($this->to, $this->content);

        if (!$is_sent) {
            Log::debug($smsServices->getMessage());
        }
    }
}
