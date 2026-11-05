<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class MisPasswordResetNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset your IRDCRP MIS password')
            ->view('emails.auth.password-reset', [
                'userName' => $notifiable->name,
                'resetUrl' => $url,
                'expiresIn' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ])
            ->text('emails.auth.password-reset-text', [
                'userName' => $notifiable->name,
                'resetUrl' => $url,
                'expiresIn' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ]);
    }
}
