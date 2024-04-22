<?php

namespace App\Http\Controllers\API\Data;

use App\Models\Produk;
use App\Models\Resep;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ResepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Produk::all()->load('resep.bahan_baku:id_bahan_baku,nama_bahan_baku')->where('id_kategori', '<>', 'TP');

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

    public function paginate()
    {
        $data = Produk::with('resep.bahan_baku:id_bahan_baku,nama_bahan_baku')
            ->where('id_kategori', '<>', 'TP')
            ->paginate(5);

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

    public function search(string $data)
    {
        $data = Produk::with('resep.bahan_baku:id_bahan_baku,nama_bahan_baku')
            ->whereAny([
                'id_produk',
                'nama_produk',
                'ukuran',
                'harga',
                'stok',
                'limit',
                'status'
            ], 'LIKE', '%' . $data . '%')
            ->where('id_kategori', '<>', 'TP')
            ->get();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data is not found',
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
        $validate = Validator::make($request->all(), [
            'id_produk' => 'required|exists:produk,id_produk',
            'id_bahan_baku' => 'required|exists:bahan_baku,id_bahan_baku',
            'kuantitas' => 'required|numeric|gte:0',
            'satuan' => 'required|max:255',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data = Resep::create([
                'id_produk' => $request->id_produk,
                'id_bahan_baku' => $request->id_bahan_baku,
                'kuantitas' => $request->kuantitas,
                'satuan' => $request->satuan,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to save data',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data successfully saved',
            'data' => $data,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id_produk)
    {
        $data = Produk::with('resep.bahan_baku:id_bahan_baku,nama_bahan_baku')->where('id_produk', $id_produk)->first();

        if ($data == null) {
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
    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_resep' => 'required|exists:resep,id_resep',
            'id_produk' => 'required|exists:produk,id_produk',
            'id_bahan_baku' => 'required|exists:bahan_baku,id_bahan_baku',
            'kuantitas' => 'sometimes|numeric|gte:0',
            'satuan' => 'sometimes|max:255',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $data = Resep::find($request->id_resep);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $data->update([
                'id_produk' => $request->id_produk,
                'id_bahan_baku' => $request->id_bahan_baku,
                'kuantitas' => $request->kuantitas,
                'satuan' => $request->satuan,
            ]);

            DB::commit();
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
    public function destroy($id_resep)
    {
        $data = Resep::find($id_resep);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $data->delete();
            DB::commit();
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

    public function destroyAll(string $id_produk)
    {
        $data = Resep::where('id_produk', $id_produk)->get();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($data as $item) {
                $item->delete();
            }

            DB::commit();
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
}