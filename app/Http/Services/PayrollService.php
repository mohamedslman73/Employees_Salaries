<?php

namespace App\Http\Services;

use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * @param $requested_data
     *
     * @return mixed
     */

    public function createPayroll($requestedData)
    {
        return Payroll::create($requestedData);
    }

    /*
     * this function check if the end of month is Friday  or Saturday payday will be the last weekday before the
    last day of the month.
     */
    public function checkDates()
    {
        $date = Carbon::now()->endOfMonth();

        $dayName = $date->format('l');
        if ($dayName == 'Friday') {
            $date = $date->subDays(1);
        } elseif ($dayName == 'Saturday') {
            $date = $date->subDays(2);
        }

        return $date;
    }


}
