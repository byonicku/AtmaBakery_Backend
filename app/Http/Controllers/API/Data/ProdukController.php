<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Produk::all();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data is empty',
            ], 404);
        }

        return response()->json([
            'message' => 'Data successfully retrieved',
            'data' => $data,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), ([
            'nama_produk' => 'required|max:255',
            'id_kategori' => 'required|in:CK, MNM, RT, TP',
            'ukuran' => 'required:in:1, 1/2',
            'harga' => 'required|min:0',
            'stok' => 'required|min:0',
            'limit' => 'required|min:0',
            'id_penitip' => 'nullable|numeric',
            'status' => 'required|in:PO,READY',
        ]));

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data = Produk::create([
                'nama_produk' => $request->nama_produk,
                'id_kategori' => strtoupper($request->id_kategori),
                'ukuran' => $request->ukuran,
                'harga' => $request->harga,
                'stok' => $request->stok,
                'limit' => $request->limit,
                'id_penitip' => $request->id_penitip,
                'status' => strtoupper($request->status),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create data',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data successfully created',
            'data' => $data,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Produk::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Data successfully retrieved',
            'data' => $data,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validate = Validator::make($request->all(), ([
            'nama_produk' => 'required|max:255',
            'id_kategori' => 'required|in:CK, MNM, RT, TP',
            'ukuran' => 'required:in:1, 1/2',
            'harga' => 'required|min:0',
            'stok' => 'required|min:0',
            'limit' => 'required|min:0',
            'id_penitip' => 'nullable|numeric',
            'status' => 'required|in:PO,READY',
        ]));

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 400);
        }

        $data = Produk::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $data->update([
                'nama_produk' => $request->nama_produk,
                'id_kategori' => strtoupper($request->id_kategori),
                'ukuran' => $request->ukuran,
                'harga' => $request->harga,
                'stok' => $request->stok,
                'limit' => $request->limit,
                'id_penitip' => $request->id_penitip,
                'status' => strtoupper($request->status),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update data',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data successfully updated',
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
                'message' => 'Data not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $data->delete();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete data',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data successfully deleted',
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateStok(Request $request, string $id)
    {
        $validate = Validator::make($request->all(), ([
            'stok' => 'required|min:0',
        ]));

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 400);
        }

        $data = Produk::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $data->update([
                'stok' => $request->stok,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update data',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data successfully updated',
            'data' => $data,
        ], 200);
    }

    public function updateLimit(Request $request, string $id)
    {
        $validate = Validator::make($request->all(), ([
            'limit' => 'required|min:0',
        ]));

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 400);
        }

        $data = Produk::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $data->update([
                'limit' => $request->limit,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update data',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data successfully updated',
            'data' => $data,
        ], 200);
    }
}
