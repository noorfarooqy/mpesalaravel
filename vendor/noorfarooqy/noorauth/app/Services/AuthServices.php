<?php

namespace Noorfarooqy\NoorAuth\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Noorfarooqy\NoorAuth\Events\UserRegisteredEvent;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServices extends NoorServices
{

    public function login($request)
    {
        $this->request = $request;
        $this->setResponseType();
        $this->rules = [
            'email' => 'required|email|max:125',
            'password' => 'required|string|max:125',
        ];

        $this->CustomValidate();

        if ($this->has_failed) {
            $this->setError($this->getMessage(), 40001);
            return $this->getResponse();
        }

        $data = $this->ValidatedData();

        $user = $user = User::where('email', $data['email'])->first();
        // $is_authentic = Hash::check($data['password'], $user?->password);
        if (Auth::attempt($data)) {
            $user = User::where('email', $data['email'])->first();
            $token = $this->createUserToken($user);
            $resp = [
                'user' => $user,
                'api_token' => $token->plainTextToken, // TO DO Set the scope for the token using user permissions
            ];

            $this->setError('', 0);
            $this->setSuccess('success');
            return $this->getResponse($resp);
        }

        $this->setError($m = "User email and password do not match ", 50001);
        //TO DO record auth failures
        return $this->getResponse();
    }
    public function register($request)
    {
        $this->request = $request;
        $this->setResponseType();
        $register_domain = config('noorauth.register_domain');
        $this->rules = [
            'name' => 'required|string|max:45',
            'email' => 'required|email|max:125|regex:/(.*)' . $register_domain . '/i|unique:users',
            'password' => 'required|string|max:125|confirmed',
        ];

        $this->CustomValidate();

        if ($this->has_failed) {
            $this->setError($this->getMessage(), 40001);
            return $this->getResponse();
        }

        $data = $this->ValidatedData();
        $data['password'] = Hash::make($data['password']);

        try {
            $user = User::create($data);

            $token = $this->createUserToken($user);
            $resp = [
                'user' => $user,
                'api_token' => $token->plainTextToken, // TO DO Set the scope for the token using user permissions
            ];

            UserRegisteredEvent::dispatch($user);

            return $this->getResponse($resp);
        } catch (\Throwable $th) {
            $this->setError(env('APP_DEBUG') ? $th->getMessage() : 'Oops! Something went wrong. Could not create user', 50001);
            return $this->getResponse();
        }
    }

    public function createUserToken($user)
    {
        //TO DO set user token scope
        return $user?->createToken('auth_token');
    }


    public function RunPermissions()
    {
        $modules = config('noorauth.modules', []);
        $permissions = config('noorauth.permissions', []);

        foreach ($modules as $key => $module) {
            foreach ($permissions as $key => $permission) {
                $action = $module . '_' . $permission;
                $existing_permission = Permission::where('name', $action)->get()->first();
                if (!$existing_permission) {
                    $new_permission = Permission::create(['name' => $action]);
                }
            }
        }
    }
    public function RunRoles()
    {
        $roles = config('noorauth.roles');
        foreach ($roles as $key => $role) {
            try {
                foreach ($role['allowed_permissions'] as $rkey => $allowed_permission) {
                    if (in_array('*', $allowed_permission['permissions'])) {
                        $all_permissions = config('noorauth.permissions', []);
                        foreach ($all_permissions as $pkey => $permission) {
                            $action = $allowed_permission['module'] . '_' . $permission;
                            $this->CreateRoleOrGivePermission($key, $action);
                        }
                    } else {
                        $given_permissions = $allowed_permission['permissions'];
                        foreach ($given_permissions as $pkey => $permission) {
                            $action = $allowed_permission['module'] . '_' . $permission;
                            $this->CreateRoleOrGivePermission($key, $action);
                        }
                    }
                }
            } catch (\Throwable $th) {
                Log::info('---Failed to creat role or permission ');
                Log::info($th->getMessage());
            }
        }
    }

    public function CreateRoleOrGivePermission($role, $permission)
    {
        $given_role = Role::where('name', $role)->get()->first();
        if (!$given_role) {
            $given_role = Role::create(['name' => $role]);
            echo 'created new role ' . $role;
        }

        if (!$given_role->hasPermissionTo($permission)) {
            $given_role->givePermissionTo($permission);
            echo 'given new permission ' . $permission . ' - ' . $role;
        }
    }
}
