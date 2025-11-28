<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Models\Admin;
use App\Models\AdminLoginHistory;
use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request) {
        return $request->all();
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->with('permissions')->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$admin->status) {
            throw ValidationException::withMessages([
                'email' => 'The provided account is not active.',
            ]);
        }

        $admin->token = $admin->createToken('web')->plainTextToken;

        DB::table('personal_access_tokens')->where('tokenable_id', $admin->id)
            ->where('tokenable_type', 'App\Models\Admin')
            ->whereNull('lfm_token')
            ->update([
                'lfm_token' => bcrypt($admin->token)
            ]);

        AdminLoginHistory::create([
            'admin_id' => $admin->id,
            'ip' => $request->ip()
        ]);

        return new AuthResource($admin);
    }

    public function user(Request $request)
    {
        return $request->user();
    }

    public function logout() {
        Auth::guard('admin')->user()->currentAccessToken()->delete();
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'username'=>'required',
            'email'=> 'required|email|max:255|unique:users,email',
            'password'=>'required',
            'firstname'=>'required',
            'permissions'=>'required|array',
        ]);
        $user = new Admin();
        $user->first_name = $request->firstname;
        $user->email = $request->email;
        $user->name = $request->username;
        $user->status = $request->status;
        $user->password =  bcrypt($request->password);
        $user->save();

        foreach ($request->permissions as $perminsion){
            if($perminsion['status']) {
                $permit = Permission::find($perminsion['id']);
                if ($permit) {
                    UserPermission::create([
                        'module' => $permit->module,
                        'user_id' => $user->id,
                        'permission_id' => $permit->id,
                    ]);
                }
            }
        }

        return response()->json(['success'=> true, 'user'=>$user], 200);
    }

    public function getPermissions(Request $request)
    {
        return Permission::all();
    }

    public function getAuthUserPermission()
    {
        return UserPermission::where('user_id',Auth::guard('admin')->user()->id)->get();
    }
}
