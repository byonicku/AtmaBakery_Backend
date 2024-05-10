<?php

namespace App\Http\Controllers\API\Data;

use App\Models\Karyawan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Karyawan::all();

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

    public function indexOnlyTrashed()
    {
        $data = Karyawan::onlyTrashed()->get();

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
        $data = Karyawan::paginate(10);

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

    public function search(string $data)
    {
        $data = Karyawan::whereAny(['nama', 'no_telp', 'email', 'hire_date', 'gaji', 'bonus'], 'LIKE', '%' . $data . '%')->get();

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

    public function restore(string $id)
    {
        $data = Karyawan::onlyTrashed()->find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $data->restore();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Data tidak berhasil direstore',
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil direstore',
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'nama' => 'required|max:255',
            'no_telp' => 'required|digits_between:10,13|unique:karyawan,no_telp|regex:/^(?:\+?08)(?:\d{2,3})?[ -]?\d{3,4}[ -]?\d{4}$/',
            'email' => 'required|email:rfc,dns|unique:karyawan,email',
            'hire_date' => 'required|date',
        ], [
            'required' => ':attribute harus diisi',
            'max' => ':attribute maksimal 255 karakter',
            'digits_between' => ':attribute harus berupa angka dan panjang karakter antara 10 sampai 13',
            'unique' => ':attribute sudah terdaftar',
            'no_telp.regex' => 'Nomor telepon tidak valid, pastikan mulai dari 08',
            'email' => ':attribute harus berupa email',
            'date' => ':attribute harus berupa tanggal',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();
        try {
            $data = Karyawan::create([
                'nama' => $request->nama,
                'no_telp' => $request->no_telp,
                'email' => $request->email,
                'hire_date' => $request->hire_date,
                'gaji' => 0,
                'bonus' => 0
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
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
        $data = Karyawan::find($id);

        if (!$data) {
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
        $data = Karyawan::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama' => 'sometimes|max:255',
            'no_telp' => [
                'sometimes',
                'digits_between:10,13',
                Rule::unique('karyawan')->ignore($data->no_telp, 'no_telp'),
                'regex:/^(?:\+?08)(?:\d{2,3})?[ -]?\d{3,4}[ -]?\d{4}$/',
            ],
            'email' => [
                'sometimes',
                'email:rfc,dns',
                Rule::unique('karyawan')->ignore($data->email, 'email')
            ],
            'hire_date' => 'sometimes|date',
            'gaji' => 'sometimes|numeric|min:0',
            'bonus' => 'sometimes|numeric|min:0',
        ], [
            'max' => ':attribute maksimal 255 karakter',
            'digits_between' => ':attribute harus berupa angka dan panjang karakter antara 10 sampai 13',
            'unique' => ':attribute sudah terdaftar',
            'no_telp.regex' => 'Nomor telepon tidak valid, pastikan mulai dari 08',
            'email' => ':attribute harus berupa email',
            'date' => ':attribute harus berupa tanggal',
            'numeric' => ':attribute harus berupa angka',
            'min' => ':attribute minimal 0',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'nama',
            'no_telp',
            'email',
            'hire_date',
            'gaji',
            'bonus',
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
                'message' => 'Data gagal dibuat',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dibuat',
            'data' => $data,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Karyawan::find($id);

        if (!$data) {
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
                'message' => 'Data tidak berhasil dihapus',
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dihapus',
        ], 200);
    }
}
