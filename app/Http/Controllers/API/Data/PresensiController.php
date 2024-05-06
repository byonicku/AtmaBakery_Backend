<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PresensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Presensi::join('karyawan', 'karyawan.id_karyawan', '=', 'presensi.id_karyawan')->get();

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

    public function indexByDate(string $date)
    {
        $validate = Validator::make(['date' => $date], [
            'date' => [
                'required',
                'date',
                Rule::exists('presensi', 'tanggal')
            ]
        ], [
            'date.exists' => 'Data tidak ditemukan'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 404);
        }

        $data = Presensi::join('karyawan', 'karyawan.id_karyawan', '=', 'presensi.id_karyawan')
            ->where('tanggal', "=", $date)->get();

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

    public function search(string $data, string $date)
    {
        $validate = Validator::make(['date' => $date], [
            'date' => [
                'required',
                'date',
                Rule::exists('presensi', 'tanggal')
            ]
        ], [
            'date.exists' => 'Data tidak ditemukan'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 404);
        }

        $data = Presensi::join('karyawan', 'karyawan.id_karyawan', '=', 'presensi.id_karyawan')
            ->whereAny(['nama', 'no_telp', 'email'], 'LIKE', '%' . $data . '%')
            ->where('tanggal', "=", $date)
            ->get();

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
        $validator = Validator::make($request->all(), [
            'id_karyawan' => 'required|exists:karyawan,id_karyawan',
            'tanggal' => 'required|date',
            'alasan' => 'sometimes|string',
            'status' => 'required|in:1,0'
        ], [
            'status.required' => 'Status harus diisi',
            'status.in' => 'Status harus berisi 1 (Hadir) atau 0 (Tidak Hadir)',
            'required' => ':attribute harus diisi',
            'exists' => ':attribute tidak ditemukan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            if ($request->alasan) {
                $data = Presensi::create([
                    'id_karyawan' => $request->id_karyawan,
                    'tanggal' => $request->tanggal,
                    'alasan' => $request->alasan,
                ]);
            } else {
                $data = Presensi::create([
                    'id_karyawan' => $request->id_karyawan,
                    'tanggal' => $request->tanggal,
                ]);
            }

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
        $data = Presensi::join('karyawan', 'karyawan.id_karyawan', '=', 'presensi.id_karyawan')
            ->find($id);

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
        $data = Presensi::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'alasan' => 'sometimes|string',
            'status' => 'sometimes|in:1,0'
        ], [
            'status.in' => 'Status harus berisi 1 (Hadir) atau 0 (Tidak Hadir)',
            'exists' => ':attribute tidak ditemukan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'alasan',
            'status'
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
                'message' => $e->getMessage(),
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
        $data = Presensi::find($id);

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
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dihapus',
        ], 200);
    }
}
