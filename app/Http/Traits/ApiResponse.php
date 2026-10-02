<?php
namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

Trait ApiResponse{
    public function success(mixed $data=null,string $message='تمت العمليه بنجاح',int $status=200):JsonResponse{
        return response()->json([
            'success'=>true,
            'message'=>$message,
            'data'=>$data
        ],$status);
    }
    public function fail(string $message='حدث خطأ',int $status=400):JsonResponse{
        return response()->json([
            'success'=>false,
            'message'=>$message,
        ],$status);
    }
}