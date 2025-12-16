<?php

namespace RaDevs\JwtAuth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ApiResetPasswordCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;


    public string $code;
    public int $expireMinutes;


    public function __construct(string $code, int $expireMinutes)
    {
        $this->code = strtoupper($code);
        $this->expireMinutes = $expireMinutes;
    }


    public function via(object $notifiable): array
    {
        return ['mail'];
    }


    public function toMail(object $notifiable): MailMessage
    {
        $minutes = max(1, (int)$this->expireMinutes);
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' ' . ($hours === 1 ? 'hour' : 'hours');
        }
        if ($mins > 0) {
            $parts[] = $mins . ' ' . ($mins === 1 ? 'minute' : 'minutes');
        }
        $expiresHuman = implode(' ', $parts);

        return (new MailMessage)
            ->subject(__('Reset Password Notification'))
            ->view('ra-jwt-auth::mail.reset.code', [
                'appName' => config('app.name'),
                'code' => $this->code,
                'expiresHuman' => $expiresHuman,
            ]);
    }
}