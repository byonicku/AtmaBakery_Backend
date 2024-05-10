<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AlamatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Alamat::all();

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

    public function paginate()
    {
        $data = Alamat::paginate(10);

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

        $data = Alamat::where('id_user', '=', $id_user)->paginate(10);

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

    public function search(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'data' => 'required|string',
        ], [
            'data.required' => 'Search harus diisi',
            'data.string' => 'Search harus berupa text',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 404);
        }

        $data = $request->data;

        $data = Alamat::whereAny(['nama_lengkap', 'lokasi', 'keterangan'], 'LIKE', '%' . $data . '%')->get();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
        ], 200);
    }

    public function searchSelf(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'data' => 'required|string',
        ], [
            'data.required' => 'Search harus diisi',
            'data.string' => 'Search harus berupa text',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 404);
        }

        $data = $request->data;
        $id_user = $user->id_user;

        $data = Alamat::where('id_user', '=', $id_user)
            ->whereAny(['nama_lengkap', 'lokasi', 'keterangan'], 'LIKE', '%' . $data . '%')->get();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_user' => 'required|exists:user,id_user',
            'nama_lengkap' => 'required|string',
            'no_telp' => 'required|digits_between:10,13|regex:/^(?:\+?08)(?:\d{2,3})?[ -]?\d{3,4}[ -]?\d{4}$/',
            'lokasi' => 'required|string',
            'keterangan' => 'required|string',
        ], [
            'required' => ':attribute harus diisi',
            'no_telp.regex' => 'Nomor telepon tidak valid, pastikan mulai dari 08',
            'no_telp.digits_between' => 'Nomor telepon harus berisi 10-13 digit',
            'nama_lengkap.string' => 'Nama lengkap harus berupa text',
            'lokasi.string' => 'Lokasi harus berupa text',
            'keterangan.string' => 'Keterangan harus berupa text',
            'id_user.exists' => 'User tidak ditemukan',

        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();
        try {
            $data = Alamat::create([
                'id_user' => $request->id_user,
                'nama_lengkap' => $request->nama_lengkap,
                'no_telp' => $request->no_telp,
                'lokasi' => $request->lokasi,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Data gagal dibuat',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dibuat',
            'data' => $data,
        ], 201);
    }

    public function storeSelf(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string',
            'no_telp' => 'required|digits_between:10,13|regex:/^(?:\+?08)(?:\d{2,3})?[ -]?\d{3,4}[ -]?\d{4}$/',
            'lokasi' => 'required|string',
            'keterangan' => 'required|string',
        ], [
            'required' => ':attribute harus diisi',
            'no_telp.regex' => 'Nomor telepon tidak valid, pastikan mulai dari 08',
            'no_telp.digits_between' => 'Nomor telepon harus berisi 10-13 digit',
            'nama_lengkap.string' => 'Nama lengkap harus berupa text',
            'lokasi.string' => 'Lokasi harus berupa text',
            'keterangan.string' => 'Keterangan harus berupa text',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();
        try {
            $data = Alamat::create([
                'id_user' => $user->id_user,
                'nama_lengkap' => $request->nama_lengkap,
                'no_telp' => $request->no_telp,
                'lokasi' => $request->lokasi,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Data gagal dibuat',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dibuat',
            'data' => $data,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Alamat::find($id);

        if ($data == null) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
        ], 200);
    }

    public function showSelf()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $id_user = $user->id_user;

        $data = Alamat::where('id_user', "=", $id_user)->get();

        if ($data == null) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = Alamat::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'id_user' => 'required|exists:user,id_user',
            'nama_lengkap' => 'sometimes|string',
            'no_telp' => 'sometimes|digits_between:10,13|regex:/^(?:\+?08)(?:\d{2,3})?[ -]?\d{3,4}[ -]?\d{4}$/',
            'lokasi' => 'sometimes|string',
            'keterangan' => 'sometimes|string',
        ], [
            'required' => ':attribute harus diisi',
            'no_telp.regex' => 'Nomor telepon tidak valid, pastikan mulai dari 08',
            'no_telp.digits_between' => 'Nomor telepon harus berisi 10-13 digit',
            'nama_lengkap.string' => 'Nama lengkap harus berupa text',
            'lokasi.string' => 'Lokasi harus berupa text',
            'keterangan.string' => 'Keterangan harus berupa text',
            'id_user.exists' => 'User tidak ditemukan',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'id_user',
            'nama_lengkap',
            'no_telp',
            'lokasi',
            'keterangan',
        ];

        $updateData = (new FunctionHelper())
            ->updateDataMaker($fillableAttributes, $request);

        DB::beginTransaction();

        try {
            $data->update($updateData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal melakukan update',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil diupdate',
            'data' => $data,
        ], 200);
    }

    public function updateSelf(Request $request, string $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $data = Alamat::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        if ($data->id_user != $user->id_user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $validate = Validator::make($request->all(), [
            'nama_lengkap' => 'sometimes|string',
            'no_telp' => 'sometimes|digits_between:10,13|regex:/^(?:\+?08)(?:\d{2,3})?[ -]?\d{3,4}[ -]?\d{4}$/',
            'lokasi' => 'sometimes|string',
            'keterangan' => 'sometimes|string',
        ], [
            'required' => ':attribute harus diisi',
            'no_telp.regex' => 'Nomor telepon tidak valid, pastikan mulai dari 08',
            'no_telp.digits_between' => 'Nomor telepon harus berisi 10-13 digit',
            'nama_lengkap.string' => 'Nama lengkap harus berupa text',
            'lokasi.string' => 'Lokasi harus berupa text',
            'keterangan.string' => 'Keterangan harus berupa text',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'nama_lengkap',
            'no_telp',
            'lokasi',
            'keterangan',
        ];

        $updateData = (new FunctionHelper())
            ->updateDataMaker($fillableAttributes, $request);

        DB::beginTransaction();

        try {
            $data->update($updateData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal melakukan update',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil diupdate',
            'data' => $data,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Alamat::find($id);

        if ($data == null) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $data->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dihapus',
        ], 200);
    }

    public function destroySelf(string $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $data = Alamat::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        if ($data->id_user != $user->id_user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        DB::beginTransaction();
        try {
            $data->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Data tidak berhasil dihapus',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dihapus',
        ], 200);
    }
}
