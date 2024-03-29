<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MailSend;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'email' => 'required|email|unique:user',
            'password' => 'required',
            'no_telp' => 'required|digits_between:8,11',
            'tanggal_lahir' => 'required|date',
        ]);

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'Email already exists',
            ], 400);
        }

        $str = Str::random(75);

        $user = User::create([
            'id_role' => 'CUST',
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_telp' => $request->no_telp,
            'tanggal_lahir' => $request->tanggal_lahir,
            'verify_key' => $str,
        ]);

        $details = [
            'nama' => $request->nama,
            'email' => $request->email,
            'website' => 'Atma Kitchen',
            'datetime' => date('Y-m-d H:i:s'),
            'url' => request()->getHttpHost() . '/verify/' . $str,
        ];

        Mail::to($request->email)->send(new MailSend($details));

        return response()->json([
            'message' => 'Successfully registered. Please check your email to verify your account',
            'data' => $user,
            'details' => $details,
        ], 200);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (!Auth::attempt($user)) {
            return response()->json([
                'message' => 'Login Failed, Username or Password is wrong',
            ], 400);
        }

        $user = Auth::user();

        if (!$user->active && $user->role == 'CUST') {
            Auth::logout();

            return response()->json([
                'message' => 'Login Failed, Account not verified',
            ], 400);
        }

        return response()->json([
            'message' => 'Successfully logged in',
            'data' => $user,
            'token' => $user->createToken('login', ['role:user'])->plainTextToken
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ], 200);
    }

    public function verify($verify_key)
    {
        $keyCheck = User::select('verify_key')
            ->where('verify_key', $verify_key)
            ->exists();

        if (!$keyCheck) {
            return View::make('FailedVerify');
        }

        $checkAlready = User::select('active')
            ->where('verify_key', $verify_key)
            ->where('active', 1)
            ->exists();

        if ($checkAlready) {
            return View::make('SuccessVerify');
        }

        User::where('verify_key', $verify_key)
            ->update([
                'active' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);

        return View::make('SuccessVerify');
    }
}
