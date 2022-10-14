<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Application;
use App\Http\Helper\ApplicationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\Paginator;
class ApplicationController
{
    use ApplicationHelper;

    //
    public function index()
    {
        $response = Plan::all()->Application::all()->Customer::all();

        if (($response->count()) > 0) {
            $response->Paginate(10);
            return $this->onSuccessListAllApplications($response, 'Successfully retrieved all applications');
        }
    }
    public function show(Application $application): Application
    {
        return $application;
    }
    public function listAllApplications(Request $request): JsonResponse
    {
        if (array_key_exists('planType',$request->route()->parameters()))
            $planType = $request->route('planType');
        else
            $planType = null;

        $response = DB::table('plans')->select(
            'name',
            'type',
            'applications.id as applicationId',
            'customers.first_name',
            'customers.last_name',
            'applications.address_1',
            'applications.address_2',
            'applications.city',
            'applications.status',
            'applications.state',
            'plans.monthly_cost',
            'applications.order_id as orderId'
        )
                    ->join('applications', 'plans.id', '=', 'applications.plan_id')
                    ->join('customers', 'applications.customer_id', '=', 'customers.id')
                    ->orderBy('applications.id')
                    ->get();

        if (!empty($planType))
            $response = $response->where('type', $planType);
        /*dd(
            DB::getQueryLog()
        );*/
        if ($response && $response->count()>0) {
            $perPage = 3;
            $totalPages = intval($response->count()/$perPage)<=0?1:intval($response->count()/$perPage);
            $collection = collect($response);
            $pages = $collection->forPage($totalPages, $perPage);
            $pages->all();

            return $this->onSuccessListAllApplications($pages, 'Successfully retrieved all applications');
        }

        return $this->onFailedList($response);
    }
}
