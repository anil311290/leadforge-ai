<?php

namespace App\Jobs;

use App\Models\EmailMessage;
use App\Services\Email\MailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmail implements ShouldQueue
{
    use Queueable;

    public $tries = 2;
    public $backoff = [10, 60];

    public function __construct(public EmailMessage $email)
    {
    }

    public function handle(MailService $mail): void
    {
        if ($this->email->status !== 'approved') {
            return;
        }

        $mail->send($this->email);
    }
}