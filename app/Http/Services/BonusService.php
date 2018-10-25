<?php

namespace App\Http\Services;

use App\Models\Bonus;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BonusService
{
    /**
     * @param $requested_data
     *
     * @return mixed
     */

    public function createBonus($requestedData)
    {
        return Bonus::create($requestedData);
    }

    /*
     * this function check if the end of month is Friday  or Saturday payday will be the last weekday before the
    last day of the month.
     */

    public function checkDateForBonus()
    {
        $date = Carbon::now()->startOfMonth()->addWeek(2);
        $nameOfTheDay = $date->format('l');
        if ($nameOfTheDay == 'Friday') {
            $date = $date->addDays(6);
        } elseif ($nameOfTheDay == 'Saturday') {
            $date = $date->addDays(5);
        }
        return $date->toDateString();
    }



}
