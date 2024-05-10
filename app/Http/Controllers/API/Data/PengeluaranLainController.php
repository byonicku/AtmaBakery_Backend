<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PengeluaranLainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Pengeluaran::all();

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
        $data = Pengeluaran::paginate(10);

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
        $data = Pengeluaran::whereAny(['nama', 'satuan', 'total', 'tanggal_pengeluaran'], 'LIKE', '%' . $data . '%')->get();

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

    public function filter(string $month, string $year)
    {
        if (!$year || !$month) {
            return response()->json([
                'message' => 'Tahun dan bulan harus diisi',
            ], 400);
        }

        if ($year < 2000 || $year > 2100) {
            return response()->json([
                'message' => 'Tanggal harus diantara tahun 2000 dan 2100',
            ], 400);
        }

        if ($month < 1 || $month > 12) {
            return response()->json([
                'message' => 'Bulan harus diantara 1 dan 12',
            ], 400);
        }

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $data = Pengeluaran::whereBetween('tanggal_pengeluaran', [$startDate, $endDate])
            ->paginate(10);

        if ($data->isEmpty()) {
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
            'nama' => 'required|max:255',
            'satuan' => 'required|max:255',
            'total' => 'required|numeric|gte:0',
            'tanggal_pengeluaran' => 'required|date',
        ], [
            'required' => ':attribute harus diisi',
            'gte' => ':attribute harus lebih dari atau sama dengan 0',
            'numeric' => ':attribute harus berupa angka',
            'date' => ':attribute harus berupa tanggal',
            'max' => ':attribute maksimal 255 karakter',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data = Pengeluaran::create([
                'nama' => $request->nama,
                'satuan' => $request->satuan,
                'total' => $request->total,
                'tanggal_pengeluaran' => $request->tanggal_pengeluaran,
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
        $data = Pengeluaran::find($id);

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
        $data = Pengeluaran::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama' => 'sometimes|max:255',
            'satuan' => 'sometimes|max:255',
            'total' => 'sometimes|numeric|gte:0',
            'tanggal_pengeluaran' => 'sometimes|date',
        ], [
            'gte' => ':attribute harus lebih dari atau sama dengan 0',
            'numeric' => ':attribute harus berupa angka',
            'date' => ':attribute harus berupa tanggal',
            'max' => ':attribute maksimal 255 karakter',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 400);
        }

        $fillableAttributes = [
            'nama',
            'satuan',
            'total',
            'tanggal_pengeluaran',
        ];

        $updateData = (new FunctionHelper())
            ->updateDataMaker($fillableAttributes, $request);

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
        $data = Pengeluaran::find($id);

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
