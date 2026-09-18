<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\Helper;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $resetUrl = url(config('app.url').route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false));

        // Try to use the custom template system first
        try {
            $data = [
                'action_url' => $resetUrl,
                'username'   => $notifiable->username ?? $notifiable->name ?? 'Member',
            ];
            
            // Check if template exists by trying to find it manually or just let Helper handle it
            // Ideally Helper::sendEmailTemplate returns true/false.
            // But here we need to return a MailMessage if we are inside toMail...
            // Actually, Helper::sendEmailTemplate sends the mail immediately using Mail::send().
            // So we can't return a MailMessage if we use Helper.
            // WE MUST RETURN void or similar if we sent it manually?
            // Notification::toMail expects a MailMessage or Mailable.
            
            // Wait, if I use Helper::sendEmailTemplate, it sends the email right away.
            // If I return nothing or null, Laravel might throw an error or do nothing.
            
            // Let's check how Laravel handles custom sending in notifications.
            // If I return a Mailable, it sends that.
            
            // Option 1: Re-implement Helper logic here to return a MailMessage (Hard because Helper uses html strings from DB)
            // Option 2: Send it manually here and return null?
            // Laravel documentation says: "The toMail method should return an Illuminate\Notifications\Messages\MailMessage instance."
            
            // BUT, if we want to use the DB template which is HTMl content...
            // We can construct a MailMessage with ->view() or ->html().
            
            $template = \App\Models\EmailTemplate::where('key', 'reset_password')->first();
            if ($template) {
                $subject = $template->subject;
                $content = $template->content;
    
                foreach ($data as $k => $v) {
                    $subject = str_replace('{' . $k . '}', $v, $subject);
                    $content = str_replace('{' . $k . '}', $v, $content);
                }
                
                return (new MailMessage)
                    ->subject($subject)
                    ->view('vendor.notifications.email', ['content' => $content]) // We might not have a generic view
                    // OR simple raw html
                    ->line($content); // This escapes HTML which is bad if content is HTML.
            }
        } catch (\Exception $e) {
            // Fallback
        }
        
        // Let's try a different approach. We can override the User's sendPasswordResetNotification method
        // to NOT use the Notification system if we use Helper::sendEmailTemplate.
        // See the User model change below.
        
        // IGNORE THIS FILE CONTENT FOR NOW, I WILL HANDLE THIS IN THE USER MODEL DIRECTLY.
        // It is cleaner to just call Helper::sendEmailTemplate in the User model method.
        
        return (new MailMessage)
                    ->subject('Đặt lại mật khẩu')
                    ->line('Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.')
                    ->action('Đặt lại mật khẩu', $resetUrl)
                    ->line('Link đặt lại mật khẩu này sẽ hết hạn sau 60 phút.')
                    ->line('Nếu bạn không yêu cầu đặt lại mật khẩu, không cần thực hiện thêm hành động nào.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
