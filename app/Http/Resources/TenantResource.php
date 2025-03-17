<?php

namespace App\Http\Resources;

use App\Models\Campus;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $subcribtion = SubscriptionPlan::where('tenant_id', $this->id)->first();
        $owner = User::where('id', $this->owner_user_id)->first();
        $nb_campus = Campus::where('tenant_id', $this->id)->count();

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $this->getFirstMediaUrl('logo'),
            'subscription' => $subcribtion,
            'owner' => $owner,
            'campus_limit'=> $this->campus_limit,
            'slogan'=> $this->slogan,
            'short_name'=> $this->short_name,
            'status'=> $this->status,
            'website'=> $this->website,
            'nb_campus'=> $nb_campus,
        ];

        return $data;
    }
}
