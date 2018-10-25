<?php

namespace App\Http\Controllers\Api;

//use App\Http\Services\PayrollService;
use App\Http\Controllers\Api\Transformers\PayrollTransformer;
use App\Http\Services\PayrollService;
use App\Http\Services\ProposalCodeService;
use App\Models\Payroll;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PayrollApiController extends SystemApiController
{

    protected $payroll;

    public function __construct(PayrollService $payroll)
    {
        $this->payroll = $payroll;
    }

    public function create(Request $request)
    {
        $requestData = $request->only(['staff_id', 'year', 'month', 'amount']);
        $validator = Validator::make($requestData, [
            'staff_id' => 'required|exists:staff,id',
            'amount' => 'required',
        ]);
        if ($validator->errors()->any()) {
            return $this->ValidationError($validator, __('Validation Error'));
        }
        $requestData['created_by'] = Auth::id();
        $date = $this->payroll->checkDates();
        $date = $date->toDateString();
        $requestData['day'] = substr($date, 8, 2);
        $requestData['month'] = substr($date, 5, 2);
        $requestData['year'] = substr($date, 0, 4);

        $transforrmer = new PayrollTransformer();
        $payroll = $this->payroll->createPayroll($requestData);
        return $this->json(true, 'Payroll Created Successfully', $transforrmer->transform($payroll));
    }

    /*
     * this api for list all staff to create payroll.
     */
    public function listStaff()
    {
        return $this->json(true, 'List all Staff to Choose from them', Staff::get(['id',\DB::raw("CONCAT(first_name,' ',last_name) as staff_name")]));
    }
}