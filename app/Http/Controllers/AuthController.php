<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request) {
        $user = new User;

        $user->name = $request->input("name");
        $user->email = $request->input("email");
        $user->password = Crypt::encrypt($request->input("password"));
        $user->save();

        $token = Auth::login($user);

        return response()->json(["status" => "OK", 'data' => ["token" => $token]]);
    }

    public function login(Request $request) {
        $user = User::where('email', $request->input("email"))->first();

        if (!$user) {
            abort(401);
        }

        if (Crypt::decrypt($user->password) !=$request->input("password")) {
            abort(401);
        }

        $token = Auth::login($user);

        return response()->json(["status" => "OK", 'data' => ["token" => $token]]);
    }

    public function me(Request $request) {
        $user = User::find($request->user_id);

        if (!$user) {
            abort(404);
        }

        return $user;
    }
}
