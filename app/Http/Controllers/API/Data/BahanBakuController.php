<?php

namespace App\Http\Controllers\API\Data;

use App\Models\BahanBaku;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = BahanBaku::all();

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
        $data = BahanBaku::onlyTrashed()->get();

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
        $data = BahanBaku::paginate(10);

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
        $data = BahanBaku::whereAny(['nama_bahan_baku', 'satuan', 'stok'], 'LIKE', '%' . $data . '%')->get();

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
            'nama_bahan_baku' => 'required|max:255',
            'satuan' => 'required|max:255|in:gram,butir,buah,ml',
            'stok' => 'required|gte:0',
        ], [
            'required' => ':attribute harus diisi',
            'gte' => ':attribute harus lebih dari atau sama dengan 0',
            'exists' => ':attribute tidak ditemukan',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        $message = 'Data berhasil dibuat';

        try {
            $data = BahanBaku::create([
                'nama_bahan_baku' => $request->nama_bahan_baku,
                'satuan' => $request->satuan,
                'stok' => $request->stok,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => $message,
            'data' => $data,
        ], 201);
    }

    public function restore(string $id)
    {
        $data = BahanBaku::onlyTrashed()->find($id);

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
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil direstore',
            'data' => $data,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = BahanBaku::find($id);

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
        $data = BahanBaku::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama_bahan_baku' => 'sometimes|max:255',
            'satuan' => 'sometimes|max:255|in:gram,butir,buah,ml',
            'stok' => 'sometimes|gte:0',
        ], [
            'gte' => ':attribute harus lebih dari atau sama dengan 0',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data->update([
                'nama_bahan_baku' => $request->nama_bahan_baku,
                'satuan' => $request->satuan,
                'stok' => $request->stok,
            ]);

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
        $data = BahanBaku::find($id);

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
