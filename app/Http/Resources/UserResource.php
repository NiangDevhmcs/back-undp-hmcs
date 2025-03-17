<?php

namespace App\Http\Resources;

use App\Models\Role;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $campus_id = TenantService::getCampusId($this);
        $tenant_id = TenantService::getTenantId($this);
        $data = [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'matricule' => $this->matricule,
            'phone_number_one' => $this->phone_number_one,
            'phone_number_two' => $this->phone_number_two,
            'email' => $this->email,
            'status' => $this->status,
            'address' => $this->address,
            'role' => $this->role,
            'role_id' => $this->role->id,
            'gender' => $this->gender,
            'campus_id' => $campus_id,
            'tenant_id' => $tenant_id,
            'campus' => $this->campus,
            'profile_photo_path' => $this->getFirstMediaUrl('profile_picture')
        ];

        return $data;
    }
}
