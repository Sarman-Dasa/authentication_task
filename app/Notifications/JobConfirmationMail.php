<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobConfirmationMail extends Notification
{
    use Queueable;

    public $employee;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($employee)
    {
        $this->employee = $employee;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {   
        $name = $this->employee->first_name ." ".$this->employee->last_name;
        return (new MailMessage)
                    ->subject('Job confirmation mail for ' . $this->employee->company_name)
                    ->line('Hello ,'.$name)
                    ->line($name." has been employed as a ".$this->employee->job_type ." at ". $this->employee->company_name . " joining date on ". $this->employee->joining_date);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
