<?php

namespace App\Jobs;

use App\Mail\NotificationMail;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $toEmail;
    public string $toName;
    public string $bladeTemplate;
    public string $subject;
    public array $data;
    public ?string $fromEmail;
    public ?string $fromName;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $toEmail,
        string $toName,
        string $bladeTemplate,
        string $subject,
        array $data = [],
        ?string $fromEmail = null,
        ?string $fromName = null
    ) {
        $this->toEmail = $toEmail;
        $this->toName = $toName;
        $this->bladeTemplate = $bladeTemplate;
        $this->subject = $subject;
        $this->data = $data;
        $this->fromEmail = $fromEmail ?? config('mail.from.address');
        $this->fromName = $fromName ?? config('mail.from.name');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $mailable = new NotificationMail(
                $this->bladeTemplate,
                $this->subject,
                array_merge($this->data, [
                    'toName' => $this->toName,
                    'toEmail' => $this->toEmail,
                ])
            );

            Mail::to($this->toEmail, $this->toName)
                ->send($mailable);
        } catch (\Exception $e) {
            // Log error but don't fail the job
            \Log::error('Failed to send notification email', [
                'to' => $this->toEmail,
                'template' => $this->bladeTemplate,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
