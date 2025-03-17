<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SendVerificationEmail;
use App\Models\Admin;
use App\Models\Editor;
use App\Models\Role;
use App\Models\User;
use App\Models\Viewer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    protected function validator(array $data)
    {
        $data = $this->setDefaultRole($data);

        return Validator::make($data, [
            'first_name' => ['required', 'string', 'min:2'],
            'last_name' => ['required', 'string', 'min:2'],
            'email' => [
                'required',
                'string',
                'email',
                'min:8',
                'max:254',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/',
                'unique:users'
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
            'role_id' => ['required', 'exists:roles,id'],

        ], [
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.min' => 'Email must be at least 8 characters.',
            'email.max' => 'Email cannot exceed 254 characters.',
            'email.regex' => 'Please enter a valid email address format.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password_confirmation.required' => 'Please confirm your password.',
            'role_id.required' => 'Role is required.',
            'role_id.exists' => 'The selected role is invalid.',
        ]);
    }

    /**
     * Set the default role to Admin if not provided
     */
    protected function setDefaultRole(array $data)
    {
        if (!isset($data['role_id']) || empty($data['role_id'])) {
            // Get the Admin role ID
            $adminRole = Role::where('name', 'Admin')->first();

            if ($adminRole) {
                $data['role_id'] = $adminRole->id;
            }
        }

        return $data;
    }

    protected function create(array $data)
    {
        try {
            DB::beginTransaction();

            // Make sure we have the default role set
            $data = $this->setDefaultRole($data);

            // Create the base user
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => $data['role_id']
            ]);

            // Get the role
            $role = Role::findOrFail($data['role_id']);

            // Create the specific user type based on role name
            switch ($role->name) {
                case 'Admin':
                    Admin::create([
                        'user_id' => $user->id
                    ]);
                    break;

                case 'Editor':
                    Editor::create([
                        'user_id' => $user->id
                    ]);
                    break;

                case 'Viewer':
                    Viewer::create([
                        'user_id' => $user->id
                    ]);
                    break;

                default:
                    // If no specific role matches, rollback
                    DB::rollBack();
                    throw new \Exception('Invalid role specified');
            }

            DB::commit();
            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function register(Request $request)
    {
        try {
            $data = $request->all();

            // Apply default role before validation
            $data = $this->setDefaultRole($data);

            $validator = $this->validator($data);

            if ($validator->fails()) {
                $firstError = $validator->errors()->first();
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'message' => $firstError,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $this->create($data);
            SendVerificationEmail::dispatch($user);

            // $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Please check your email to verify your account.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}