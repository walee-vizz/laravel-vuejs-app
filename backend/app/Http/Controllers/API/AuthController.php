<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PasswordResetToken;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\Providers\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = JWTAuth::user();
        return response()->json([
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    public function logout()
    {
        JWTAuth::logout();
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'user' => JWTAuth::user(),
            'authorisation' => [
                'token' => JWTAuth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }



    public function user()
    {
        // $token = JWTAuth::parseToken();
        $user = JWTAuth::parseToken()->authenticate();
        $user = JWTAuth::user();

        return response()->json(['user' => $user]);
    }



    public function password_reset_token(Request $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($user) {
            $token_already_exist = PasswordResetToken::where('email', $user->email)->first();
            if ($token_already_exist) {
                return json_encode(('http://127.0.0.1:8000/api/password/reset/' . $token_already_exist->token), JSON_UNESCAPED_SLASHES);
            } else {
                $token = Str::random(60);
                $pass_reset =  PasswordResetToken::create([
                    'email' => $email,
                    'token' => $token,
                ]);
                if ($pass_reset) {
                    return json_encode(('http://127.0.0.1:8000/api/password/reset/' . $pass_reset->token), JSON_UNESCAPED_SLASHES);
                }
            }
        } else {
            return response()->json(['error' => true, 'message' => 'Account not registered with us']);
        }
        // return json_encode($email);
    }


    public function reset_password(Request $request, $token)
    {
        // return response()->json(['message' => $request->all()]);

        $token = PasswordResetToken::where('token', $token)->first();
        if ($token) {
            // return json_encode($token->email);
            $data = [
                'password' => Hash::make($request->password),
            ];
            $is_password_updated = User::where('email', $token->email)->update($data);

            // return response()->json(['message' => $is_password_updated]);

            if ($is_password_updated) {
                $token = PasswordResetToken::where('email', $token->email)->delete();
                return response()->json(['message' => 'Password updated successfully']);
            } else {
                return response()->json(['message' => 'Could not update password']);
            }
        }
    }
}
