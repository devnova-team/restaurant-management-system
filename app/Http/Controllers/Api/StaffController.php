<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\Staff;
use App\Services\StaffService;
use App\Support\ApiResponse;

class StaffController extends Controller
{
    public function __construct(private StaffService $staffService) {}

    public function index()
    {
        $staff = $this->staffService->index();

        return ApiResponse::success([
            'staff' => StaffResource::collection($staff),
        ], 'تم جلب بيانات الموظفين بنجاح');
    }

    public function store(StoreStaffRequest $request)
    {
        $owner = $request->user();
        $validatedData = $request->validated();
        $staff = $this->staffService->store($owner, $validatedData);

        return ApiResponse::success([
            'staff' => new StaffResource($staff),
        ], 'تم إضافة الموظف بنجاح');
    }

    public function update(UpdateStaffRequest $request, Staff $staff){

        $validatedData = $request->validated();
        $staff = $this->staffService->update($staff, $validatedData);

        return ApiResponse::success([
            'staff' => new StaffResource($staff),
        ], 'تم تعديل بيانات الموظف بنجاح');
    }

    public function destroy(Staff $staff){
        $this->staffService->destroy($staff);
        return ApiResponse::success(null, 'تم تعطيل حساب الموظف بنجاح');
    }
}
