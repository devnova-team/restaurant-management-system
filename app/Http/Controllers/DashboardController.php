<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\DashboardRequest;
use App\Http\Resources\DashboardStatsResource;
use App\Http\Traits\ApiResponse;
use App\Services\DashboardServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use  ApiResponse;
    public function __construct(protected DashboardServices $services){}

    public function stats(DashboardRequest $request):JsonResponse{
        $restaurantId=$this->restaurantId($request);
        $data=$this->services->getStats($request->validated('from'),$request->validated('to'));

        return $this->success(new DashboardStatsResource($data));
    }


    public function restaurantId(DashboardRequest $request):int{
        if(Auth::check() && isset(Auth::user()->restaurant_id)){
            return (int) Auth::user()->restaurant_id;
        }
        if(app()->environment('local') && $request->has('restaurant_id')){
            return (int) $request->query('restaurant_id');
        }

        abort(401,'غير مصرح لك');
    }
}
