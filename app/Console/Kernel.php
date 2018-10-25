<?php

namespace App\Console;

use App\Http\Services\BonusService;
use App\Http\Services\PayrollService;
use App\Mail\SendPaymentReminderEmail;
use App\Models\Admin;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Send Mail To All admins before 2 days of payment.
        $payroll = new PayrollService();
        $payrollDate = $payroll->checkDates();
        $schedule->call(function () {
            $admins = Admin::all();
            $salaries = DB::table('staff')->sum('salary');
            foreach ($admins as $admin) {
                    Mail::to($admin)
                        ->send(new SendPaymentReminderEmail($salaries));

            }
        })->monthlyOn(substr($payrollDate, 8, 2));

        $bonus = new BonusService();
        $bonusDate = $bonus->checkDateForBonus();
        $schedule->call(function () {
            $admins = Admin::all();
            $salaries = DB::table('staff')->sum('salary');
            $totalBonus = $salaries * 0.1;
            foreach ($admins as $admin) {
                Mail::to($admin)
                    ->send(new SendPaymentReminderEmail($totalBonus));

            }
        })->monthlyOn(substr($bonusDate, 8, 2));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
