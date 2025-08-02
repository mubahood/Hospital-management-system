<?php

namespace App\Services;

use App\Models\User;
use App\Models\Enterprise;
use App\Models\Consultation;
use App\Models\PaymentRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

/**
 * NotificationService - Handles complex notification and communication business logic
 * 
 * This service manages:
 * - System notifications and alerts
 * - Email communications
 * - SMS notifications
 * - Push notifications
 * - Automated reminders and alerts
 */
class NotificationService
{
    /**
     * Send appointment reminder notifications
     */
    public function sendAppointmentReminders(): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'details' => []];

        // Get consultations scheduled for tomorrow
        $upcomingConsultations = Consultation::whereDate('consultation_date_time', now()->addDay())
            ->where('status', 'Scheduled')
            ->with(['patient', 'assignedTo', 'enterprise'])
            ->get();

        foreach ($upcomingConsultations as $consultation) {
            try {
                $this->sendAppointmentReminder($consultation);
                $results['sent']++;
                $results['details'][] = [
                    'consultation_id' => $consultation->id,
                    'patient' => $consultation->patient?->name,
                    'status' => 'sent'
                ];
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'consultation_id' => $consultation->id,
                    'patient' => $consultation->patient?->name,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
                
                Log::error('Failed to send appointment reminder', [
                    'consultation_id' => $consultation->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Appointment reminders processed', $results);
        return $results;
    }

    /**
     * Send individual appointment reminder
     */
    public function sendAppointmentReminder(Consultation $consultation): bool
    {
        $patient = $consultation->patient;
        $doctor = $consultation->assignedTo;
        $enterprise = $consultation->enterprise;

        if (!$patient || !$patient->email) {
            throw new \Exception('Patient email not available');
        }

        $appointmentTime = Carbon::parse($consultation->consultation_date_time);
        
        $emailData = [
            'patient_name' => $patient->name,
            'doctor_name' => $doctor?->name ?? 'Doctor',
            'appointment_date' => $appointmentTime->format('l, F j, Y'),
            'appointment_time' => $appointmentTime->format('g:i A'),
            'hospital_name' => $enterprise?->name ?? 'Hospital',
            'consultation_number' => $consultation->consultation_number,
            'notes' => $consultation->symptoms ?? 'General consultation'
        ];

        // Send email notification
        Mail::send('emails.appointment-reminder', $emailData, function ($message) use ($patient, $enterprise) {
            $message->to($patient->email, $patient->name)
                    ->subject('Appointment Reminder - ' . ($enterprise?->name ?? 'Hospital'))
                    ->from(config('mail.from.address'), $enterprise?->name ?? config('mail.from.name'));
        });

        // Send SMS if phone number available
        if ($patient->phone) {
            $this->sendSMSReminder($patient, $consultation);
        }

        // Log the notification
        Log::info('Appointment reminder sent', [
            'consultation_id' => $consultation->id,
            'patient_id' => $patient->id,
            'notification_types' => ['email', $patient->phone ? 'sms' : null]
        ]);

        return true;
    }

    /**
     * Send payment reminder notifications
     */
    public function sendPaymentReminders(): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'details' => []];

        // Get overdue payments (30+ days old)
        $overduePayments = PaymentRecord::where('status', 'Pending')
            ->where('created_at', '<=', now()->subDays(30))
            ->with(['consultation.patient', 'consultation.enterprise'])
            ->get();

        foreach ($overduePayments as $payment) {
            try {
                $this->sendPaymentReminder($payment);
                $results['sent']++;
                $results['details'][] = [
                    'payment_id' => $payment->id,
                    'patient' => $payment->consultation?->patient?->name,
                    'amount' => $payment->amount,
                    'status' => 'sent'
                ];
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'payment_id' => $payment->id,
                    'patient' => $payment->consultation?->patient?->name,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
                
                Log::error('Failed to send payment reminder', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Payment reminders processed', $results);
        return $results;
    }

    /**
     * Send low stock alerts to relevant staff
     */
    public function sendLowStockAlerts(): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'details' => []];

        // Get enterprises with low stock items
        $enterprises = Enterprise::whereHas('stockItems', function ($query) {
            $query->whereColumn('current_quantity', '<=', 'minimum_quantity')
                  ->where('status', 'Active');
        })->with(['stockItems' => function ($query) {
            $query->whereColumn('current_quantity', '<=', 'minimum_quantity')
                  ->where('status', 'Active')
                  ->with('category');
        }])->get();

        foreach ($enterprises as $enterprise) {
            try {
                $this->sendLowStockAlert($enterprise);
                $results['sent']++;
                $results['details'][] = [
                    'enterprise_id' => $enterprise->id,
                    'enterprise_name' => $enterprise->name,
                    'low_stock_items' => $enterprise->stockItems->count(),
                    'status' => 'sent'
                ];
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'enterprise_id' => $enterprise->id,
                    'enterprise_name' => $enterprise->name,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Send system alerts to administrators
     */
    public function sendSystemAlert(string $type, string $message, array $data = [], array $recipients = []): bool
    {
        try {
            // If no specific recipients, send to all system administrators
            if (empty($recipients)) {
                $recipients = User::where('user_type', 'admin')
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->toArray();
            }

            $alertData = [
                'alert_type' => $type,
                'message' => $message,
                'data' => $data,
                'timestamp' => now(),
                'system_info' => [
                    'app_name' => config('app.name'),
                    'environment' => config('app.env'),
                    'url' => config('app.url')
                ]
            ];

            foreach ($recipients as $recipient) {
                Mail::send('emails.system-alert', $alertData, function ($mail) use ($recipient, $type) {
                    $mail->to($recipient)
                         ->subject('System Alert: ' . $type)
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });
            }

            Log::info('System alert sent', [
                'type' => $type,
                'recipients_count' => count($recipients),
                'message' => $message
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send system alert', [
                'type' => $type,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send welcome email to new users
     */
    public function sendWelcomeEmail(User $user, string $temporaryPassword = null): bool
    {
        try {
            $welcomeData = [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_type' => $user->user_type,
                'temporary_password' => $temporaryPassword,
                'login_url' => config('app.url') . '/login',
                'enterprise_name' => $user->enterprise?->name ?? 'Hospital',
                'support_email' => config('mail.from.address')
            ];

            Mail::send('emails.welcome', $welcomeData, function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Welcome to ' . config('app.name'))
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info('Welcome email sent', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send consultation completed notification
     */
    public function sendConsultationCompletedNotification(Consultation $consultation): bool
    {
        try {
            $patient = $consultation->patient;
            $doctor = $consultation->assignedTo;
            
            if (!$patient || !$patient->email) {
                return false;
            }

            $notificationData = [
                'patient_name' => $patient->name,
                'doctor_name' => $doctor?->name ?? 'Doctor',
                'consultation_date' => Carbon::parse($consultation->consultation_date_time)->format('F j, Y'),
                'consultation_number' => $consultation->consultation_number,
                'diagnosis' => $consultation->diagnosis,
                'recommendations' => $consultation->recommendations,
                'next_appointment' => $consultation->next_appointment_date,
                'hospital_name' => $consultation->enterprise?->name ?? 'Hospital'
            ];

            Mail::send('emails.consultation-completed', $notificationData, function ($message) use ($patient, $consultation) {
                $message->to($patient->email, $patient->name)
                        ->subject('Consultation Summary - ' . $consultation->consultation_number)
                        ->from(config('mail.from.address'), $consultation->enterprise?->name ?? config('mail.from.name'));
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send consultation completed notification', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send bulk notifications
     */
    public function sendBulkNotification(array $recipients, string $subject, string $message, array $data = []): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'details' => []];

        foreach ($recipients as $recipient) {
            try {
                $emailData = array_merge($data, [
                    'recipient_name' => $recipient['name'] ?? 'User',
                    'message' => $message,
                    'subject' => $subject
                ]);

                Mail::send('emails.bulk-notification', $emailData, function ($mail) use ($recipient, $subject) {
                    $mail->to($recipient['email'], $recipient['name'] ?? 'User')
                         ->subject($subject)
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });

                $results['sent']++;
                $results['details'][] = [
                    'email' => $recipient['email'],
                    'status' => 'sent'
                ];
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'email' => $recipient['email'] ?? 'unknown',
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        Log::info('Bulk notification processed', [
            'subject' => $subject,
            'total_recipients' => count($recipients),
            'sent' => $results['sent'],
            'failed' => $results['failed']
        ]);

        return $results;
    }

    /**
     * Schedule notification for future sending
     */
    public function scheduleNotification(string $type, array $data, Carbon $sendAt): bool
    {
        try {
            // In a real implementation, this would use Laravel's job queue
            // For now, we'll log the scheduled notification
            Log::info('Notification scheduled', [
                'type' => $type,
                'data' => $data,
                'send_at' => $sendAt->toDateTimeString(),
                'scheduled_at' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to schedule notification', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get notification preferences for user
     */
    public function getNotificationPreferences(User $user): array
    {
        // In a real implementation, this would fetch from user preferences table
        return [
            'email_notifications' => true,
            'sms_notifications' => !empty($user->phone),
            'push_notifications' => false,
            'appointment_reminders' => true,
            'payment_reminders' => true,
            'system_alerts' => $user->user_type === 'admin',
            'marketing_emails' => false
        ];
    }

    /**
     * Send SMS reminder (placeholder implementation)
     */
    private function sendSMSReminder($patient, Consultation $consultation): bool
    {
        try {
            $message = "Reminder: You have an appointment tomorrow at " . 
                      Carbon::parse($consultation->consultation_date_time)->format('g:i A') . 
                      ". Consultation #" . $consultation->consultation_number;

            // In a real implementation, integrate with SMS service (Twilio, etc.)
            Log::info('SMS reminder sent', [
                'patient_id' => $patient->id,
                'phone' => $patient->phone,
                'consultation_id' => $consultation->id,
                'message' => $message
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send SMS reminder', [
                'patient_id' => $patient->id,
                'phone' => $patient->phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send payment reminder
     */
    private function sendPaymentReminder(PaymentRecord $payment): bool
    {
        $patient = $payment->consultation?->patient;
        $enterprise = $payment->consultation?->enterprise;

        if (!$patient || !$patient->email) {
            throw new \Exception('Patient email not available');
        }

        $daysOverdue = Carbon::parse($payment->created_at)->diffInDays(now());

        $reminderData = [
            'patient_name' => $patient->name,
            'amount_due' => $payment->amount,
            'consultation_number' => $payment->consultation?->consultation_number,
            'consultation_date' => Carbon::parse($payment->consultation?->consultation_date_time)->format('F j, Y'),
            'days_overdue' => $daysOverdue,
            'hospital_name' => $enterprise?->name ?? 'Hospital',
            'payment_reference' => $payment->reference_number
        ];

        Mail::send('emails.payment-reminder', $reminderData, function ($message) use ($patient, $enterprise) {
            $message->to($patient->email, $patient->name)
                    ->subject('Payment Reminder - Outstanding Balance')
                    ->from(config('mail.from.address'), $enterprise?->name ?? config('mail.from.name'));
        });

        return true;
    }

    /**
     * Send low stock alert
     */
    private function sendLowStockAlert(Enterprise $enterprise): bool
    {
        // Get admin users for this enterprise
        $adminUsers = User::where('enterprise_id', $enterprise->id)
            ->where('user_type', 'admin')
            ->whereNotNull('email')
            ->get();

        if ($adminUsers->isEmpty()) {
            throw new \Exception('No admin users found for enterprise');
        }

        $alertData = [
            'enterprise_name' => $enterprise->name,
            'low_stock_items' => $enterprise->stockItems->map(function ($item) {
                return [
                    'name' => $item->name,
                    'current_quantity' => $item->current_quantity,
                    'minimum_quantity' => $item->minimum_quantity,
                    'category' => $item->category?->name ?? 'Uncategorized'
                ];
            })->toArray(),
            'alert_date' => now()->format('F j, Y g:i A')
        ];

        foreach ($adminUsers as $admin) {
            Mail::send('emails.low-stock-alert', $alertData, function ($message) use ($admin, $enterprise) {
                $message->to($admin->email, $admin->name)
                        ->subject('Low Stock Alert - ' . $enterprise->name)
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
        }

        return true;
    }
}
