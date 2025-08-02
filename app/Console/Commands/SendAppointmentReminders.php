<?php

namespace App\Console\Commands;

use App\Models\Consultation;
use App\Models\User;
use App\Notifications\AppointmentReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointment reminders to patients';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting appointment reminder job...');

        $remindersSent = 0;

        try {
            // 24-hour reminders (appointments tomorrow)
            $tomorrowReminders = $this->send24HourReminders();
            $remindersSent += $tomorrowReminders;

            // 2-hour reminders (appointments in 2 hours)
            $twoHourReminders = $this->send2HourReminders();
            $remindersSent += $twoHourReminders;

            // Now reminders (appointments starting in 15 minutes)
            $nowReminders = $this->sendNowReminders();
            $remindersSent += $nowReminders;

            $this->info("Appointment reminders completed. Total reminders sent: {$remindersSent}");
            Log::info("Appointment reminders job completed. Reminders sent: {$remindersSent}");

        } catch (\Exception $e) {
            $this->error('Error sending appointment reminders: ' . $e->getMessage());
            Log::error('Appointment reminders job failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Send 24-hour reminder (appointments tomorrow)
     */
    private function send24HourReminders()
    {
        $tomorrow = Carbon::tomorrow();
        $tomorrowEnd = Carbon::tomorrow()->endOfDay();

        $appointments = Consultation::whereNotNull('appointment_date')
            ->whereIn('appointment_status', ['scheduled', 'confirmed'])
            ->whereBetween('appointment_date', [$tomorrow->startOfDay(), $tomorrowEnd])
            ->whereNull('reminder_24h_sent_at')
            ->with(['patient', 'doctor'])
            ->get();

        $count = 0;
        foreach ($appointments as $appointment) {
            try {
                if ($appointment->patient) {
                    $appointment->patient->notify(new AppointmentReminder($appointment, '24hours'));
                    
                    // Mark as sent
                    $appointment->reminder_24h_sent_at = now();
                    $appointment->save();
                    
                    $count++;
                    $this->line("24h reminder sent for appointment #{$appointment->consultation_number}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to send 24h reminder for appointment #{$appointment->consultation_number}: " . $e->getMessage());
                Log::error("Failed to send 24h reminder for appointment #{$appointment->consultation_number}: " . $e->getMessage());
            }
        }

        $this->info("24-hour reminders sent: {$count}");
        return $count;
    }

    /**
     * Send 2-hour reminder (appointments in 2 hours)
     */
    private function send2HourReminders()
    {
        $twoHoursFromNow = Carbon::now()->addHours(2);
        $twoHoursWindow = Carbon::now()->addHours(2)->addMinutes(15); // 15-minute window

        $appointments = Consultation::whereNotNull('appointment_date')
            ->whereIn('appointment_status', ['scheduled', 'confirmed'])
            ->whereBetween('appointment_date', [$twoHoursFromNow, $twoHoursWindow])
            ->whereNull('reminder_2h_sent_at')
            ->with(['patient', 'doctor'])
            ->get();

        $count = 0;
        foreach ($appointments as $appointment) {
            try {
                if ($appointment->patient) {
                    $appointment->patient->notify(new AppointmentReminder($appointment, '2hours'));
                    
                    // Mark as sent
                    $appointment->reminder_2h_sent_at = now();
                    $appointment->save();
                    
                    $count++;
                    $this->line("2h reminder sent for appointment #{$appointment->consultation_number}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to send 2h reminder for appointment #{$appointment->consultation_number}: " . $e->getMessage());
                Log::error("Failed to send 2h reminder for appointment #{$appointment->consultation_number}: " . $e->getMessage());
            }
        }

        $this->info("2-hour reminders sent: {$count}");
        return $count;
    }

    /**
     * Send immediate reminder (appointments starting in 15 minutes)
     */
    private function sendNowReminders()
    {
        $fifteenMinutesFromNow = Carbon::now()->addMinutes(15);
        $nowWindow = Carbon::now()->addMinutes(20); // 5-minute window

        $appointments = Consultation::whereNotNull('appointment_date')
            ->whereIn('appointment_status', ['scheduled', 'confirmed'])
            ->whereBetween('appointment_date', [$fifteenMinutesFromNow, $nowWindow])
            ->whereNull('reminder_now_sent_at')
            ->with(['patient', 'doctor'])
            ->get();

        $count = 0;
        foreach ($appointments as $appointment) {
            try {
                if ($appointment->patient) {
                    $appointment->patient->notify(new AppointmentReminder($appointment, 'now'));
                    
                    // Mark as sent
                    $appointment->reminder_now_sent_at = now();
                    $appointment->save();
                    
                    $count++;
                    $this->line("Immediate reminder sent for appointment #{$appointment->consultation_number}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to send immediate reminder for appointment #{$appointment->consultation_number}: " . $e->getMessage());
                Log::error("Failed to send immediate reminder for appointment #{$appointment->consultation_number}: " . $e->getMessage());
            }
        }

        $this->info("Immediate reminders sent: {$count}");
        return $count;
    }
}
