<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class AppointmentReminder extends Notification
{
    use Queueable;

    protected $consultation;
    protected $reminderType;

    /**
     * Create a new notification instance.
     *
     * @param Consultation $consultation
     * @param string $reminderType ('24hours', '2hours', 'now')
     */
    public function __construct(Consultation $consultation, $reminderType = '24hours')
    {
        $this->consultation = $consultation;
        $this->reminderType = $reminderType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $appointmentDate = Carbon::parse($this->consultation->appointment_date);
        $subject = $this->getSubject();
        $greeting = $this->getGreeting();

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line('You have an upcoming appointment:')
            ->line('**Date & Time:** ' . $appointmentDate->format('F d, Y - h:i A'))
            ->line('**Doctor:** Dr. ' . ($this->consultation->doctor->name ?? 'TBD'))
            ->line('**Type:** ' . ucfirst(str_replace('_', ' ', $this->consultation->appointment_type ?? 'consultation')))
            ->line('**Consultation Number:** ' . $this->consultation->consultation_number)
            ->when($this->consultation->appointment_notes, function ($message) {
                return $message->line('**Notes:** ' . $this->consultation->appointment_notes);
            })
            ->action('View Appointment Details', url('/'))
            ->line('Please arrive 15 minutes before your scheduled appointment time.')
            ->line('If you need to reschedule or cancel, please contact us as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $appointmentDate = Carbon::parse($this->consultation->appointment_date);
        
        return [
            'consultation_id' => $this->consultation->id,
            'consultation_number' => $this->consultation->consultation_number,
            'appointment_date' => $this->consultation->appointment_date,
            'doctor_name' => $this->consultation->doctor->name ?? 'TBD',
            'patient_name' => $this->consultation->patient_name,
            'appointment_type' => $this->consultation->appointment_type,
            'reminder_type' => $this->reminderType,
            'message' => $this->getMessage(),
            'title' => $this->getTitle()
        ];
    }

    /**
     * Get the notification title
     */
    private function getTitle()
    {
        switch ($this->reminderType) {
            case '24hours':
                return 'Appointment Tomorrow';
            case '2hours':
                return 'Appointment in 2 Hours';
            case 'now':
                return 'Appointment Starting Soon';
            default:
                return 'Appointment Reminder';
        }
    }

    /**
     * Get the notification message
     */
    private function getMessage()
    {
        $appointmentDate = Carbon::parse($this->consultation->appointment_date);
        $doctorName = $this->consultation->doctor->name ?? 'TBD';

        switch ($this->reminderType) {
            case '24hours':
                return "Your appointment with Dr. {$doctorName} is tomorrow at " . $appointmentDate->format('h:i A');
            case '2hours':
                return "Your appointment with Dr. {$doctorName} is in 2 hours at " . $appointmentDate->format('h:i A');
            case 'now':
                return "Your appointment with Dr. {$doctorName} is starting soon at " . $appointmentDate->format('h:i A');
            default:
                return "You have an appointment with Dr. {$doctorName} on " . $appointmentDate->format('F d, Y at h:i A');
        }
    }

    /**
     * Get email subject
     */
    private function getSubject()
    {
        switch ($this->reminderType) {
            case '24hours':
                return 'Appointment Reminder - Tomorrow';
            case '2hours':
                return 'Appointment Reminder - In 2 Hours';
            case 'now':
                return 'Appointment Starting Soon';
            default:
                return 'Appointment Reminder';
        }
    }

    /**
     * Get email greeting
     */
    private function getGreeting()
    {
        return 'Hello ' . $this->consultation->patient_name . ',';
    }
}
