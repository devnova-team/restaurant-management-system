<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class StaffService
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    public function index(): Collection
    {
        return Staff::all();
    }

    public function store(Staff $owner, array $data): Staff
    {
        $data['restaurant_id'] = $owner->restaurant_id; // Assign the restaurant_id of the owner to the new staff member
        $data['password_hash'] = Hash::make($data['password']);
        $data['is_active'] = true;
        unset($data['password']); // Remove the plain password from the data array

        return Staff::create($data);
    }

    public function update(Staff $staff, array $data): Staff
    {
        if (isset($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
            unset($data['password']);
        }

        $staff->update($data);

        return $staff;
    }

    public function destroy(Staff $staff): void
    {
        $staff->update(['is_active' => false]);
    }
}
