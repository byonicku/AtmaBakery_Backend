<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotifikasiController extends Controller
{
    public function index()
    {
        $data = Notifikasi::all();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data kosong',
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
        ], 200);
    }

    public function paginateSelf()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $id_user = $user->id_user;

        $data = Notifikasi::where('id_user', '=', $id_user)->paginate(10);

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data kosong',
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
        ], 200);
    }

    public function storeNotification(Request $request, $title, $body)
    {
        $user = User::where('fcm_token', $request->fcm_token)->first();
    
        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'Unauthenticated',
                'code' => 404,
            ];
        }
    
        if ($user->id_role !== "CUST") {
            return [
                'status' => 'error',
                'message' => 'Unauthorized',
                'code' => 401,
            ];
        }
    
        $validate = Validator::make($request->all(), [
            'title' => 'required|string',
            'body' => 'required|string',
        ], [
            'required' => ':attribute harus diisi',
            'title.string' => 'Title harus berupa text',
            'body.string' => 'Body harus berupa text',
        ]);
    
        if ($validate->fails()) {
            return [
                'status' => 'error',
                'message' => $validate->errors()->first(),
                'code' => 400,
            ];
        }
    
        DB::beginTransaction();
        try {
            $data = Notifikasi::create([
                'id_user' => $user->id_user,
                'title' => $title,
                'body' => $body,
            ]);
    
            DB::commit();
    
            return [
                'status' => 'success',
                'message' => 'Data berhasil dibuat',
                'data' => $data,
                'code' => 201,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
    
            return [
                'status' => 'error',
                'message' => 'Data gagal dibuat',
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }
}