<?php
namespace App\Services;

use App\Http\Requests\StoreTenantRequest;
use App\Models\Owner;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MediaValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantService
{
    public function store(StoreTenantRequest $request)
    {
        DB::beginTransaction();
        try {
            // Valider le logo si présent
            if ($request->hasFile('logo')) {
                $this->validateLogo($request->file('logo'));
            }

            // Créer l'owner et le tenant en une seule transaction
            $ownerData = $this->prepareOwnerData($request);
            $tenantData = $this->prepareTenantData($request);
            $subscriptionData = $this->prepareSubscriptionData($request);
            
            // Créer l'owner
            $owner = User::create($ownerData);
            
            // Créer le tenant avec l'owner_id
            $tenantData['owner_user_id'] = $owner->id;
            $tenant = Tenant::create($tenantData);
            
            // Créer le plan d'abonnement
            $subscriptionData['tenant_id'] = $tenant->id;
            SubscriptionPlan::create($subscriptionData);
            
            // Gérer le logo si présent
            if ($request->hasFile('logo')) {
                $tenant->addMedia($request->file('logo'))
                    ->preservingOriginal()
                    ->toMediaCollection('logo', 'tenant-logos');
            }

            Owner::create([
                'user_id' => $owner->id,
                'tenant_id' => $tenant->id
            ]);

            DB::commit();

            return $this->successResponse('Le tenant a été créé avec succès.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->errorResponse($e->getMessage());
        }
    }

    private function validateLogo($logo)
    {
        $validation = MediaValidator::validate($logo, 1);
        if (!$validation['isValid']) {
            throw new \Exception($validation['message']);
        }
    }

    private function prepareOwnerData(StoreTenantRequest $request): array
    {
        $role = $this->getRoleOwner();

        if ($role->name == 'Owner') {
            $matricule = $this->generateOwnerMatricule($request->name, $request->owner_first_name, $request->owner_last_name);
        }
        
        return [
            'matricule' => $matricule,
            'first_name' => $request->owner_first_name,
            'last_name' => $request->owner_last_name,
            'email' => $request->owner_email,
            'password' => bcrypt('P@sser24'),
            'role_name' => $request->role_name,
            'phone_number_one' => $request->phone_number_one,
            'phone_number_two' => $request->phone_number_two,
            'role_id' => $role->id,
            'status' => true,
        ];
    }

    private function prepareTenantData(StoreTenantRequest $request): array
    {
        return [
            'name' => $request->name,
            'campus_limit' => $request->campus_limit,
            'logo' => $request->logo,
            'slogan' => $request->slogan,
            'website' => $request->website,
            'short_name' => $request->short_name,
        ];
    }

    private function prepareSubscriptionData(StoreTenantRequest $request): array
    {
        $amountDue = $request->amount_subscription * $request->campus_limit;
        
        return [
            'amount_subscription' => $request->amount_subscription,
            'amount_subscription_due' => $amountDue,
            'amount_subscription_paid' => $request->amount_subscription_paid,
            'amount_subscription_no_paid' => $amountDue - $request->amount_subscription_paid,
            'next_billing_date' => $request->next_billing_date,
            'billing_cycle' => $request->billing_cycle,
            'status' => true,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ];
    }

    private function successResponse(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => true
        ], 201);
    }

    private function errorResponse(string $error): JsonResponse
    {
        return response()->json([
            'message' => 'Une erreur est survenue lors de la création du tenant.',
            'status' => false,
            'error' => $error,
        ], 500);
    }

    public function getRoleOwner()
    {
        return Role::where('name', 'Owner')->first();
    }

    function generateOwnerMatricule($tenantName, $first_name, $last_name)
    {
        // Get the first two letters of the tenant's name
        $tenantPrefix = strtoupper(substr($tenantName, 0, 2));
        
        // Get the last two digits of the current year
        $year = date('y');
        
        // Get the first letter of the owner's first name and last name
        $ownerInitials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
        
        // Generate a random 4-digit number
        $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Combine all parts to create the matricule
        $matricule = $tenantPrefix . $year . $ownerInitials . $randomNumber;
        
        return $matricule;
    }   

    public static function getTenantId($user): ?string 
    {
        if (!$user || !$user->role || !$user->role->name) {
            return null;
        }

        switch ($user->role->name) {
            case 'Manager':
                return $user->manager && $user->manager->campus && $user->manager->campus->tenant 
                    ? $user->manager->campus->tenant->id 
                    : null;
            case 'Owner':
                return $user->owner && $user->owner->tenant 
                    ? $user->owner->tenant->id 
                    : null;
            default:
                return null;
        }
    }

    public static function getCampusId($user): ?int 
    {
        if (!$user || !$user->role || !$user->role->name) {
            return null;
        }

        switch ($user->role->name) {
            case 'Manager':
                return $user->manager ? $user->manager->campus_id : null;
            case 'Seller':
                return $user->seller ? $user->seller->campus_id : null;
            case 'Treasurer':
                return $user->treasurer ? $user->treasurer->campus_id : null;
            default:
                return null;
        }
    }

    public static function getCampusCode($user): ?string 
    {
        if (!$user || !$user->role || !$user->role->name) {
            return null;
        }

        switch ($user->role->name) {
            case 'Manager':
                return $user->manager ? $user->manager->campus->code : null;
            case 'Seller':
                return $user->seller ? $user->seller->campus->code : null;
            case 'Treasurer':
                return $user->treasurer ? $user->treasurer->campus->code : null;
            default:
                return null;
        }
    }
}