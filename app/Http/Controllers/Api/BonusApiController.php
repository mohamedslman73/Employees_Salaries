<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Transformers\BonusTransformer;
use App\Http\Controllers\Api\Transformers\PayrollTransformer;
use App\Http\Services\BonusService;
use App\Http\Services\PayrollService;
use App\Models\Admin;
use App\Models\Bonus;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonusApiController extends SystemApiController
{

    protected $bonusService;

    public function __construct(BonusService $bonusService)
    {
        $this->bonusService = $bonusService;
    }


    public function allBonuses(Request $request)
    {

        $eloquentData = Bonus::select([
            'bonuses.id',
            'bonuses.year',
            'bonuses.month',
            'bonuses.amount',
            'bonuses.day',
            'bonuses.created_by',
            'bonuses.staff_id',
            'bonuses.created_at',
        ])
            ->with(['createdBy' => function ($admin) {
                $admin->select(['id', 'created_by', \DB::Raw("CONCAT(admins.first_name,' ',admins.last_name) as admin_name")]);
            }, 'staff' => function ($staff) {
                $staff->select(['id', 'created_by', \DB::Raw("CONCAT(staff.first_name,' ',staff.last_name) as staff_name")]);
            }])
            ->where('bonuses.year', '=', date('Y'));

        whereBetween($eloquentData, 'DATE(bonuses.created_at)', $request->created_at1, $request->created_at2);
        whereBetween($eloquentData, 'bonuses.created_at', $request->amount1, $request->amount2);
        // filters .
        if ($request->id) {
            $eloquentData->where('bonuses.id', '=', $request->id);
        }
        if ($request->staff_id) {
            $eloquentData->where('bonuses.staff_id', '=', $request->staff_id);
        }
        if ($request->created_by) {
            $eloquentData->where('bonuses.created_by', '=', $request->created_by);
        }

        if ($request->year) {
            $eloquentData->where('bonuses.year', '=', $request->year);
        }
        if ($request->month) {
            $eloquentData->where('bonuses.month', '=', $request->month);
        }
        if ($request->day) {
            $eloquentData->where('bonuses.day', '=', $request->day);
        }

        $transformer = new BonusTransformer();

        if (empty($eloquentData->first())) {
            return $this->json(false, __('No Bonuses  Available'));
        }
        $bonuses = $eloquentData->orderBy('created_at', 'DESC')->jsonPaginate();

        // send This to filter through them in mobile app .
        $staff = Staff::select(['id', \DB::Raw("CONCAT(first_name,'',last_name) as name")])->get();
        $admins = Admin::select(['id', \DB::Raw("CONCAT(first_name,'',last_name) as name")])->get();
        $transformer->staff = $staff;
        $allData = $transformer->transformCollection($bonuses->toArray());
        $allData['staff'] = $staff;
        $allData['admins'] = $admins;
        return $this->json(true, __('Bonuses'), $allData);
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
        $check = Bonus::where(['staff_id' => $requestData['staff_id'], 'month' => date('m'), 'year' => date('Y')])->first();
        if (!empty($check)) {
            return $this->json(false, 'Can\'t Create More Than one Bonus at the Same Month For The Same Staff');
        }
        $requestData['created_by'] = Auth::id();
        $date = $this->bonusService->checkDateForBonus();
        $requestData['day'] = substr($date, 8, 2);
        $requestData['month'] = substr($date, 5, 2);
        $requestData['year'] = substr($date, 0, 4);

        $transforrmer = new BonusTransformer();
        $bonus = $this->bonusService->createBonus($requestData);
        return $this->json(true, 'Bonus Created Successfully', $transforrmer->transform($bonus));
    }

    public function oneBonus(Request $request)
    {
        $requestData = $request->only(['bonus_id']);
        $validator = Validator::make($requestData, [
            'staff_id' => 'required|exists:bonuses,id',
        ]);
        if ($validator->errors()->any()) {
            return $this->ValidationError($validator, __('Validation Error'));
        }
        $bonus = Bonus::select([
            'bonuses.id',
            'bonuses.year',
            'bonuses.month',
            'bonuses.amount',
            'bonuses.day',
            'bonuses.created_by',
            'bonuses.staff_id',
            'bonuses.created_at',
        ])
            ->with(['createdBy' => function ($admin) {
                $admin->select(['id', 'created_by', \DB::Raw("CONCAT(admins.first_name,' ',admins.last_name) as admin_name")]);
            }, 'staff' => function ($staff) {
                $staff->select(['id', 'created_by', \DB::Raw("CONCAT(staff.first_name,' ',staff.last_name) as staff_name")]);
            }])
            ->where('bonuses.year', '=', date('Y'))
            ->where('id', $request->bonus_id);
        if (empty($bonus))
            return $this->json(false, __('No Results'));
        $transforrmer = new BonusTransformer();
        return $this->json(true, 'Show One Bonus', $transforrmer->transform($bonus));
    }
}