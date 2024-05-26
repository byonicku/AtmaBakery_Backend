<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Notifications\EmailVerify;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use Notification;
use Symfony\Component\Console\Output\NullOutput;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|max:255',
            'email' => 'required|email:rfc,dns|unique:user,email',
            'password' => 'required|min:8|confirmed', // pas post tambahin password_confirmation di formdata
            'no_telp' => 'required|digits_between:10,13|unique:user,no_telp|regex:/^(?:\+?08)(?:\d{2,3})?[ -]?\d{3,4}[ -]?\d{4}$/',
            'tanggal_lahir' => 'required|date',
        ], [
            'no_telp.regex' => 'Nomor telepon tidak valid, pastikan mulai dari 08',
            'no_telp.digits_between' => 'Nomor telepon harus berisi 10-13 digit',
            'password.confirmed' => 'Konfirmasi password tidak sesuai',
            'password.min' => 'Password minimal 8 karakter',
            'email.unique' => 'Email sudah terdaftar',
            'no_telp.unique' => 'Nomor telepon sudah terdaftar',
            'email.email' => 'Email tidak valid',
            'required' => ':attribute harus diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
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
            'website' => 'Atma Bakery',
            'datetime' => date('Y-m-d H:i:s'),
            'url' => 'https://atma-bakery.vercel.app/verify/' . $str,
        ];

        Notification::route('mail', $request->email)
            ->notify(new EmailVerify($details));

        return response()->json([
            'message' => 'Registrasi berhasil, silahkan cek Email Anda untuk verifikasi akun',
            'data' => $user,
        ], 200);
    }
    public function login(Request $request)
    {

        $isMobile = $request->header('is-mobile') === 'true';

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'required' => ':attribute harus diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $user = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (User::where('email', $request->email)->doesntExist()) {
            return response()->json([
                'message' => 'Login gagal, Email tidak terdaftar',
            ], 400);
        }

        if (!Auth::attempt($user)) {
            return response()->json([
                'message' => 'Login gagal, Email atau Password salah',
            ], 400);
        }

        $user = Auth::user();

        if (!$user->active && $user->id_role == 'CUST') {
            Auth::logout();

            return response()->json([
                'message' => 'Login gagal, Akun belum diverifikasi, silahkan cek Email Anda',
            ], 400);
        }

        if ($isMobile) {
            $user->fcm_token = $request->fcm;
            $user->save();
        }

        $abilities = [];

        switch ($user->id_role) {
            case 'ADM':
                $abilities[] = "admin";
                break;
            case 'MO':
                $abilities[] = "mo";
                break;
            case 'OWN':
                $abilities[] = "owner";
                break;
            case 'CUST':
                $abilities[] = "user";
                break;
            default:
                return response()->json([
                    'message' => 'Login gagal, Role tidak ditemukan',
                ], 400);
        }

        $po_date = Cart::all()->where('id_user', $user->id_user)->pluck('po_date')->whereNotNull()->first();

        return response()->json([
            'message' => 'Berhasil login',
            'data' => $user,
            'token' => $user->createToken('login', $abilities)->plainTextToken,
            'po_date' => $po_date,
        ], 200);
    }

    public function logout(Request $request)
    {

        $isMobile = $request->header('is-mobile') === 'true';
        $user = Auth::user();
        if ($isMobile) {
            $user->fcm_token = null;
            $user->save();
        }
        if ($request->user()->currentAccessToken()->delete()) {
            return response()->json([
                'message' => 'Berhasil logout',
            ], 200);
        }

        return response()->json([
            'message' => 'Gagal logout',
        ], 400);
    }

    public function verify(string $verify_key)
    {
        $keyCheck = User::select('verify_key')
            ->where('verify_key', $verify_key)
            ->exists();

        if (!$keyCheck) {
            return response()->json([
                'message' => 'Invalid',
                'state' => '-1'
            ], 200);
        }

        $checkAlready = User::select('active')
            ->where('verify_key', $verify_key)
            ->where('active', 1)
            ->exists();

        if ($checkAlready) {
            return response()->json([
                'message' => 'Akun sudah diverifikasi',
                'state' => '0'
            ], 200);
        }

        User::where('verify_key', $verify_key)
            ->update([
                'active' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);

        return response()->json([
            'message' => 'Akun berhasil diverifikasi',
            'state' => '1'
        ], 200);
    }

    public function verifyPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:password_reset_tokens,email',
            'token' => 'required',
        ], [
            'required' => ':attribute harus diisi',
            'email.exists' => 'Email tidak terdaftar',
            'email.email' => 'Email tidak valid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'state' => -1
            ], 200);
        }

        $data = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!Hash::check($request->token, $data->token)) {
            return response()->json([
                'message' => 'Invalid',
                'state' => -1
            ], 200);
        }

        $time = Carbon::parse($data->created_at);
        $endTime = $time->addMinutes(60);
        if (Carbon::now()->gt($endTime)) {
            return response()->json([
                'message' => 'Token expired',
                'state' => -1
            ], 400);
        }

        return response()->json([
            'message' => 'Token valid',
            'state' => 1
        ], 200);
    }
}
