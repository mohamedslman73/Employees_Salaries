<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Api\Transformers\PayrollTransformer;
use App\Http\Services\PayrollService;
use App\Models\Admin;
use App\Models\Payroll;
use App\Models\Staff;
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


    public function allPayrolls(Request $request)
    {
        $eloquentData = Payroll::select([
            'payrolls.id',
            'payrolls.year',
            'payrolls.month',
            'payrolls.amount',
            'payrolls.day',
            'payrolls.created_by',
            'payrolls.staff_id',
            'payrolls.created_at',
            \DB::Raw("CONCAT(staff.first_name,' ',staff.last_name) as staff_name"),
        ])
            ->join('staff', 'staff.id', '=', 'payrolls.staff_id')
            ->with(['createdBy' => function ($admin) {
                $admin->select(['id', 'created_by', \DB::Raw("CONCAT(admins.first_name,' ',admins.last_name) as admin_name")]);
            }])
            ->where('payrolls.year', '=', date('Y'));

        whereBetween($eloquentData, 'DATE(payrolls.created_at)', $request->created_at1, $request->created_at2);
        whereBetween($eloquentData, 'payrolls.created_at', $request->amount1, $request->amount2);
        // filters .
        if ($request->id) {
            $eloquentData->where('payrolls.id', '=', $request->id);
        }
        if ($request->staff_id) {
            $eloquentData->where('payrolls.staff_id', '=', $request->staff_id);
        }
        if ($request->created_by) {
            $eloquentData->where('payrolls.created_by', '=', $request->created_by);
        }

        if ($request->year) {
            $eloquentData->where('payrolls.year', '=', $request->year);
        }
        if ($request->month) {
            $eloquentData->where('payrolls.month', '=', $request->month);
        }
        if ($request->day) {
            $eloquentData->where('payrolls.day', '=', $request->day);
        }

        $transformer = new PayrollTransformer();

        if (empty($eloquentData->first())) {
            return $this->json(false, __('No Payrolls  Available'));
        }
        $payrolls = $eloquentData->orderBy('created_at', 'DESC')->jsonPaginate();

        // send This to filter through them in mobile app .
        $staff = Staff::select(['id', \DB::Raw("CONCAT(first_name,'',last_name) as name")])->get();
        $admins = Admin::select(['id', \DB::Raw("CONCAT(first_name,'',last_name) as name")])->get();
        $transformer->staff = $staff;
        $allData = $transformer->transformCollection($payrolls->toArray());
        $allData['staff'] = $staff;
        $allData['admins'] = $admins;
        return $this->json(true, __('Payrolls'), $allData);
    }

    public function create(Request $request)
    {
        $requestData = $request->only(['staff_id', 'amount']);
        $validator = Validator::make($requestData, [
            'staff_id' => 'required|exists:staff,id',
            'amount' => 'required',
        ]);
        if ($validator->errors()->any()) {
            return $this->ValidationError($validator, __('Validation Error'));
        }
        $check = Payroll::where(['staff_id' => $requestData['staff_id'], 'month' => date('m'), 'year' => date('Y')])->first();
        if (!empty($check)) {
            return $this->json(false, 'Can\'t Create More Than one Payroll at the Same Month For The Same Staff');
        }
        $requestData['created_by'] = Auth::id();
        $date = $this->payroll->checkDates();
        $requestData['day'] = substr($date, 8, 2);
        $requestData['month'] = substr($date, 5, 2);
        $requestData['year'] = substr($date, 0, 4);
        $payroll = $this->payroll->createPayroll($requestData);
        $transforrmer = new PayrollTransformer();

        return $this->json(true, 'Payroll Created Successfully', $transforrmer->transform($payroll));
    }

    /*
     * this api for list all staff to create payroll.
     */
    public function listStaff()
    {
        return $this->json(true, 'List all Staff to Choose from them', Staff::get(['id', \DB::raw("CONCAT(first_name,' ',last_name) as staff_name")]));
    }
}