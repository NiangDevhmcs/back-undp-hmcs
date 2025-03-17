<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Campus;
use App\Models\Manager;
use App\Models\Role;
use App\Models\Seller;
use App\Models\Tenant;
use App\Models\Treasurer;
use App\Models\User;
use App\Services\MediaValidator;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index(Request $request, $campus_id) 
    {
        // dd($campus_id);
        $perPage = $request->input('perPage', 15); 
        $search = $request->input('search');
        $page = (int) $request->input('page', 1);

        $campus = Campus::find($campus_id);

        if (!$campus) {
            return response()->json([
                'status' => false,
                'message' => 'Campus non trouvé'
            ]);
        }
        
        // Construire la requête de base
        $query = User::where(function($query) use ($campus_id) {
            $query->whereHas('seller', function($q) use ($campus_id) {
                $q->where('campus_id', $campus_id);
            })
            ->orWhereHas('manager', function($q) use ($campus_id) {
                $q->where('campus_id', $campus_id);
            })
            ->orWhereHas('treasurer', function($q) use ($campus_id) {
                $q->where('campus_id', $campus_id);
            });
        })
        ->with(['manager', 'seller', 'treasurer']); // Charger les relations

        // Ajouter la recherche si un terme est fourni
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('matricule', 'LIKE', "%{$search}%")
                ->orWhereHas('role', function($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%");
                });
            });
        }
                
        // Ajouter le tri par ordre décroissant sur la date de création
        $query->orderBy('created_at', 'desc');

        $users = $query->paginate($perPage, ['*'], 'page', $page);
       
        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ]
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function usersByTenant(Request $request)
    {
        $user = Auth::user();
        $tenant_id = TenantService::getTenantId($user);
        $perPage = $request->input('perPage', 15);
        $search = $request->input('search');
        $searchByCampus = $request->input('searchByCampus');
        $page = (int) $request->input('page', 1);

        // Construire la requête de base
        $query = User::where(function($query) use ($tenant_id) {
            // Utilisateurs propriétaires directement liés au tenant
            $query->whereHas('owner', function($q) use ($tenant_id) {
                $q->where('tenant_id', $tenant_id);
            })
            // Utilisateurs liés via les campus du tenant
            ->orWhereHas('seller', function($q) use ($tenant_id) {
                $q->whereHas('campus', function($c) use ($tenant_id) {
                    $c->where('tenant_id', $tenant_id);
                });
            })
            ->orWhereHas('manager', function($q) use ($tenant_id) {
                $q->whereHas('campus', function($c) use ($tenant_id) {
                    $c->where('tenant_id', $tenant_id);
                });
            })
            ->orWhereHas('treasurer', function($q) use ($tenant_id) {
                $q->whereHas('campus', function($c) use ($tenant_id) {
                    $c->where('tenant_id', $tenant_id);
                });
            });
        })
        ->with(['owner', 'seller.campus', 'manager.campus', 'treasurer.campus', 'role']); // Charger les relations

        // Ajouter la recherche si un terme est fourni
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('matricule', 'LIKE', "%{$search}%")
                ->orWhereHas('role', function($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('seller.campus', function($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('manager.campus', function($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('treasurer.campus', function($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%");
                });
            });
        }
        if ($searchByCampus) {
            $query->where(function($q) use ($searchByCampus) {
                $q->whereHas('seller.campus', function($query) use ($searchByCampus) {
                    $query->where('id', $searchByCampus);
                })
                ->orWhereHas('manager.campus', function($query) use ($searchByCampus) {
                    $query->where('id', $searchByCampus);
                })
                ->orWhereHas('treasurer.campus', function($query) use ($searchByCampus) {
                    $query->where('id', $searchByCampus);
                });
            });
        }
    

        // Tri par date de création décroissante
        $query->orderBy('created_at', 'desc');

        // Paginer les résultats
        $users = $query->paginate($perPage, ['*'], 'page', $page);

        // Retourner la réponse
        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ]
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function store(UserRequest $request)
    {
        try {
            DB::beginTransaction();

            if ($request->hasFile('profile_photo_path')) {
                $this->validateLogo($request->file('profile_photo_path'));
            }

            $role = Role::find($request->role_id);
            $matricule = User::generateUserMatricule($request->campus_id, $role->name);
            // Créer l'utilisateur
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number_one' => $request->phone_number_one,
                'phone_number_two' => $request->phone_number_two,
                'address' => $request->address,
                'gender' => $request->gender,
                'status' => true,
                'password' => Hash::make('password'),
                'role_id' => $request->role_id,
                'matricule' => $matricule,
                'profile_photo_path' => $request->profile_photo_path
            ]);

            // Gérer la photo de profil
            if ($request->hasFile('profile_photo_path')) {
                $user->addMedia($request->file('profile_photo_path'))
                    ->preservingOriginal()
                    ->toMediaCollection('profile_picture', 'profile-pictures');
            }

            // Créer l'association selon le rôle
            switch($role->name) {
                case 'Seller':
                    Seller::create([
                        'user_id' => $user->id,
                        'campus_id' => $request->campus_id
                    ]);
                    break;
                case 'Treasurer':
                    Treasurer::create([
                        'user_id' => $user->id,
                        'campus_id' => $request->campus_id
                    ]);
                    break;
                case 'Manager':
                    Manager::create([
                        'user_id' => $user->id,
                        'campus_id' => $request->campus_id
                    ]);
                    break;
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'User created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateUser(UserRequest $request, $user_id)
    {
        try {
            DB::beginTransaction();

            $user = User::find($user_id);
            // Mettre à jour l'utilisateur
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number_one' => $request->phone_number_one,
                'phone_number_two' => $request->phone_number_two,
                'address' => $request->address,
                'gender' => $request->gender,
                'status' => true,
                'role_id' => $request->role_id
            ]);

            // Gérer la photo de profil
            if ($request->hasFile('profile_photo_path')) {
                $user->clearMediaCollection('profile_picture');
                $user->addMedia($request->file('profile_photo_path'))
                    ->preservingOriginal()
                    ->toMediaCollection('profile_picture', 'profile-pictures');
            }

            // Mettre à jour l'association selon le rôle
            $role = Role::find($request->role_id);
            switch($role->name) {
                case 'seller':
                    $user->seller()->updateOrCreate(
                        ['user_id' => $user->id],
                        ['campus_id' => $request->campus_id]
                    );
                    // Supprimer les autres rôles
                    $user->treasurer()->delete();
                    $user->manager()->delete();
                    break;
                
                case 'Treasurer':
                    $user->treasurer()->updateOrCreate(
                        ['user_id' => $user->id],
                        ['campus_id' => $request->campus_id]
                    );
                    // Supprimer les autres rôles
                    $user->seller()->delete();
                    $user->manager()->delete();
                    break;
                
                case 'Manager':
                    $user->manager()->updateOrCreate(
                        ['user_id' => $user->id],
                        ['campus_id' => $request->campus_id]
                    );
                    // Supprimer les autres rôles
                    $user->seller()->delete();
                    $user->treasurer()->delete();
                    break;
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $user = User::find($id);
            
            $user->update([
                'status' => !$user->status
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating user status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();

            // Supprimer les associations selon le rôle
            switch($user->role->name) {
                case 'Seller':
                    $user->seller()->delete();
                    break;
                case 'Treasurer':
                    $user->treasurer()->delete();
                    break;
                case 'Manager':
                    $user->manager()->delete();
                    break;
            }

            // Supprimer les médias
            $user->clearMediaCollection('profile_picture');
            
            // Soft delete l'utilisateur
            $user->delete();

            DB::commit();
            return response()->json([
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function validateLogo($logo)
    {
        $validation = MediaValidator::validate($logo, 1);
        if (!$validation['isValid']) {
            throw new \Exception($validation['message']);
        }
    }
}
