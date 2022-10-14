<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Enums\ApplicationStatus;
use App\Http\Helper\ApplicationHelper;
use \Illuminate\Http\JsonResponse;
use App\Models\Application;
use App\Jobs\B2bJob;
class B2BController
{
    //
    public function listNbnApplications(Request $request): JsonResponse
    {
        $response = DB::table('plans')->select(
            'name',
            'applications.id as applicationId',
            'applications.address_1',
            'applications.address_2',
            'applications.city',
            'applications.postcode',
            'applications.State',
            'applications.status',
            'applications.order_id as orderId'
        )
                    ->where('type', 'nbn')
                    ->where('status', ApplicationStatus::Order)
                    ->join('applications', 'plans.id', '=', 'applications.plan_id')
                    ->get();

        if($response && $response->count()>0)
        {
            foreach ($response as $nbnApplication) {
                $application = Application::find($nbnApplication->applicationId);

                if (! B2bJob::dispatch($application)) {
                    $application->status=ApplicationStatus::OrderFailed;
                    $application->save();
                }
            }

            $formattedJsonData=[];

            foreach ($response as $d) {
                $formattedJsonData = [
                    'address_1' => $d->address_1,
                    'address_2' => $d->address_2,
                    'city'=>$d->city,
                    'state' => $d->state,
                    'postcode' => $d->postcode,
                    'plan name' => $d->name
                ];
            }
            if ($response) {
                return response()->json([
                    'status'=>200,
                    'message'=>'Successfully retrieved nbn applications',
                    'data'=> $formattedJsonData
                ]);
            }
        }
        else
            $request->status = 404;

        return response()->json([
            'status'=>$request->status,
            'message'=>'',
            'data'=>''
        ], $request->status);
    }
}
