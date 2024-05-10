<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Gambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\Data\FunctionHelper;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Produk::all()->load('gambar');

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
        $data = Produk::onlyTrashed()->get();

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
        $data = Produk::paginate(10);

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

        $data = Produk::join('kategori', 'produk.id_kategori', '=', 'kategori.id_kategori')->
            whereAny(['nama_produk', 'deskripsi', 'nama_kategori', 'ukuran', 'harga', 'stok', 'limit', 'id_penitip', 'status'], 'LIKE', '%' . $data . '%')->get();

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
        $data = Produk::withTrashed()->find($id);

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
                'error' => $e->getMessage(),
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
        // Gambar wajib dikirim dengan key 'foto[]'

        $validate = Validator::make($request->all(), [
            'nama_produk' => 'required|max:255',
            'deskripsi' => 'required|max:255',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'ukuran' => 'required:in:1,1/2',
            'harga' => 'required|gte:0',
            'stok' => 'sometimes|gte:0',
            'limit' => 'sometimes|gte:0',
            'id_penitip' => 'nullable|exists:penitip,id_penitip',
            'status' => 'required|in:PO,READY',
        ], [
            'id_kategori.exists' => 'Kategori tidak ditemukan',
            'ukuran.in' => 'Ukuran harus 1 atau 1/2',
            'status.in' => 'Status harus PO atau READY',
            'id_penitip.exists' => 'Penitip tidak ditemukan',
            'harga.gte' => 'Harga harus lebih dari atau sama dengan 0',
            'stok.gte' => 'Stok harus lebih dari atau sama dengan 0',
            'limit.gte' => 'Limit harus lebih dari atau sama dengan 0',
            'required' => ':attribute harus diisi',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        if ($request->id_kategori == "TP" && $request->id_penitip == null) {
            return response()->json([
                'message' => 'Penitip wajib diisi ketika kategori Titipan',
            ], 400);
        }

        if ($request->id_kategori == "TP" && $request->status == "PO") {
            return response()->json([
                'message' => 'Status wajib Ready Stok ketika kategori Titipan',
            ], 400);
        }

        if ($request->status == "READY" && $request->stok == null) {
            return response()->json([
                'message' => 'Stok wajib diisi ketika status READY',
            ], 400);
        }

        if ($request->status == "PO" && $request->limit == null) {
            return response()->json([
                'message' => 'Limit wajib diisi ketika status PO',
            ], 400);
        }

        if (($request->id_kategori == "MNM" || $request->id_kategori == "RT") && $request->ukuran == "1/2") {
            return response()->json([
                'message' => 'Ukuran 1/2 tidak dapat digunakan untuk kategori Minuman dan Roti',
            ], 400);
        }

        if ($request->status == "PO" && $request->stok != null) {
            $request->stok = 0;
        }

        if ($request->status == "READY" && $request->limit != null) {
            $request->limit = 0;
        }

        DB::beginTransaction();

        try {
            $data = Produk::create([
                'nama_produk' => $request->nama_produk,
                'deskripsi' => $request->deskripsi,
                'id_kategori' => strtoupper($request->id_kategori),
                'ukuran' => $request->ukuran,
                'harga' => $request->harga,
                'stok' => $request->stok,
                'limit' => $request->limit,
                'id_penitip' => $request->id_penitip,
                'status' => strtoupper($request->status),
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
        $data = Produk::with('gambar')->find($id);

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
        $data = Produk::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama_produk' => 'sometimes|max:255',
            'deskripsi' => 'sometimes|max:255',
            'id_kategori' => 'sometimes|exists:kategori,id_kategori',
            'ukuran' => 'sometimes:in:1,1/2',
            'harga' => 'sometimes|gte:0',
            'stok' => 'sometimes|gte:0',
            'limit' => 'sometimes|gte:0',
            'id_penitip' => 'nullable|exists:penitip,id_penitip',
            'status' => 'sometimes|in:PO,READY',
        ], [
            'id_kategori.exists' => 'Kategori tidak ditemukan',
            'ukuran.in' => 'Ukuran harus 1 atau 1/2',
            'status.in' => 'Status harus PO atau READY',
            'id_penitip.exists' => 'Penitip tidak ditemukan',
            'harga.gte' => 'Harga harus lebih dari atau sama dengan 0',
            'stok.gte' => 'Stok harus lebih dari atau sama dengan 0',
            'limit.gte' => 'Limit harus lebih dari atau sama dengan 0',
            'required' => ':attribute harus diisi',
        ]);

        if ($request->id_kategori == "TP" && $request->id_penitip == null) {
            return response()->json([
                'message' => 'Penitip wajib diisi ketika kategori Titipan',
            ], 400);
        }

        if ($request->id_kategori == "TP" && $request->status == "PO") {
            return response()->json([
                'message' => 'Status wajib Ready Stok ketika kategori Titipan',
            ], 400);
        }

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'nama_produk',
            'deskripsi',
            'id_kategori',
            'ukuran',
            'harga',
            'stok',
            'limit',
            'id_penitip',
            'status',
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
        $data = Produk::find($id);

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
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dihapus',
        ], 200);
    }
}
