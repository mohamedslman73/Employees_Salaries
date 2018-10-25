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

class PayrollApiController extends SystemApiController {

    protected $payroll;

    public function __construct(PayrollService $payroll)
    {
        $this->payroll = $payroll;
    }
    public function allPayrolls(Request $request)
    {
        $eloquentData = Payroll::select([
            'expenses.id',
            'expenses.expense_causes_id',
            'expenses.date',
            'expenses.amount',
            'expenses.description',
            'expenses.supplier_id',
            'expenses.staff_id',
            'expenses.created_at',
            \DB::Raw("CONCAT(staff.firstname,' ',staff.lastname) as staff_name"),
        ])
            ->join('staff', 'staff.id', '=', 'expenses.staff_id')
            ->with(['expense_causes'=>function($expense_causes){
                $expense_causes->select(['id','name']);
            },'supplier'=>function($supplier){
                $supplier->select(['id','name']);
            }]);

        whereBetween($eloquentData, 'DATE(expenses.created_at)', $request->created_at1, $request->created_at2);
        whereBetween($eloquentData, 'DATE(expenses.date)', $request->date1, $request->date2);
        whereBetween($eloquentData, 'expenses.amount', $request->amount1, $request->amount2);

        if ($request->id) {
            $eloquentData->where('expenses.id', '=', $request->id);
        }
        if ($request->staff_id) {
            $eloquentData->where('expenses.staff_id', '=', $request->staff_id);
        }

        if ($request->expense_causes_id) {
            $eloquentData->where('expenses.expense_causes_id', '=', $request->expense_causes_id);
        }
        if ($request->description) {
            $eloquentData->where('expenses.description', 'LIKE','%'. $request->description. '%');
        }

        $Transformer = new ExpenseTransformer();

        if (empty($eloquentData->first())){
            return $this->json(false,__('No Expense  Available'));
        }
        $expense = $eloquentData->orderBy('created_at','DESC')->jsonPaginate();


        $staff = Staff::select(['id',\DB::Raw("CONCAT(firstname,'',lastname) as name")])->get();
        $expenseCauses = ExpenseCauses::get(['id','name']);
        $Transformer->staff = $staff;
        $allData = $Transformer->transformCollection($expense->toArray());
        $allData['staff'] = $staff;
        $allData['expense_causes'] = $expenseCauses;
        return $this->json(true, __('Expenses'),$allData);
    }
    public function create(Request $request)
    {
        $requestData = $request->only(['staff_id','year','month','amount']);
        $validator = Validator::make($requestData,[
            //  'staff_id'=>            'required|exists:staff,id',
            'year' =>               'required',
            'month' =>               'required',
            'amount' =>               'required',
        ]);
        if ($validator->errors()->any()) {
            return $this->ValidationError($validator, __('Validation Error'));
        }
        $date =   Carbon::now()->endOfMonth();
        dd($date->format('l'));

        // $requestData['created_by'] = Auth::id();
        $requestData['created_by'] = 1;
        $Transforrmer = new PayrollTransformer();
        $payroll =  $this->payroll->createPayroll($requestData);
        return $this->json(true,'Payroll Created Successfully',$Transforrmer->transform($payroll));
    }
    /*
     * this api for list all staff to create payroll.
     */
    public function listStaff(){
        return $this->json(true,'List all Staff to Choose from them',Staff::get(['id',\DB::raw("CONCAT(first_name,' ',last_name) as staff_name")]));
    }
}