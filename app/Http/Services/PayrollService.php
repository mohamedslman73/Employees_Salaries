<?php

namespace App\Http\Services;

use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * @param $requested_data
     *
     * @return mixed
     */
    public function createPayroll($requested_data)
    {
      //  $requested_data['created_by'] = Auth::id();

//        $requested_data['year'] = '2018-10-12';
//        $requested_data['month'] = '10';
//        $requested_data['amount'] = 10;
//        $requested_data['created_by'] = 1;
//        $requested_data['staff_id'] = 1;

        return Payroll::create($requested_data);
    }


}
