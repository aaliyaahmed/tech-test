<?php

namespace App\Http\Helper;

use Illuminate\Http\JsonResponse;

trait ApplicationHelper
{
    //on success
    public function onSuccessListAllApplications($data=[], $message='', int $code=200): JsonResponse
    {
        $formattedJsonData[]=[];

        foreach ($data as $d) {
            $formattedJsonData[$d->applicationId] = [
                'Application id' => $d->applicationId,
                'Customer Full Name' => self::fullName($d->first_name, $d->last_name),
                'Address' => self::fullAddress($d->address_1, $d->address_2, $d->city),
                'Plan type' => $d->type,
                'Plan name' => $d->name,
                'State' => $d->state,
                'Plan monthly cost' => '$' . self::convertMoney($d->monthly_cost),
                'Order Id' => $d->orderId
            ];
        }

        return response()->json([
            'status'=>$code,
            'message'=>$message,
            'data'=>$formattedJsonData
        ], $code);
    }

    //on failured
    public function onFailedList($request): JsonResponse
    {
        $formattedJsonData='';

        return response()->json([
            'status'=>$request,
            'message'=>'',
            'data'=>''
        ], '');
    }

    public function fullName($first_name, $last_name)
    {
        return $first_name . ' ' . $last_name;
    }

    public function fullAddress($address_1, $address_2, $city)
    {
        return $address_1 . ' ' . $address_2 . ' ' . $city;
    }

    public function convertMoney($cost)
    {
        return number_format($cost, 2);
    }
}
