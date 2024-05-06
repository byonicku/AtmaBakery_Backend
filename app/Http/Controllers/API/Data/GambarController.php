<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\Gambar;
use App\Models\Hampers;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GambarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Gambar::all();

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_produk' => 'sometimes|numeric|exists:produk,id_produk',
            'id_hampers' => 'sometimes|numeric|exists:hampers,id_hampers',
            'url' => 'required',
            'public_id' => 'required',
        ], [
            'required' => ':attribute harus diisi',
            'numeric' => ':attribute harus berupa angka',
            'exists' => ':attribute tidak ditemukan',
        ]);

        if (!$request->id_produk && !$request->id_hampers) {
            return response()->json([
                'message' => 'id_produk atau id_hampers harus diisi',
            ], 400);
        }

        if ($request->id_produk && $request->id_hampers) {
            return response()->json([
                'message' => 'id_produk dan id_hampers tidak boleh diisi bersamaan',
            ], 400);
        }

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $count = 0;

        if ($request->id_produk) {
            $count = Gambar::all()->where('id_produk', '=', $request->id_produk)->count();
        } else {
            $count = Gambar::all()->where('id_hampers', '=', $request->id_hampers)->count();
        }

        if ($count >= 5) {
            return response()->json([
                'message' => 'Maksimal 5 gambar',
                'left' => (5 - $count),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data = Gambar::create([
                'id_produk' => $request->id_produk ?? null,
                'id_hampers' => $request->id_hampers ?? null,
                'url' => $request->url,
                'public_id' => $request->public_id,
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
            'message' => 'Image successfully added',
            'data' => $data,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Gambar::find($id);

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
    public function showProduk(string $id)
    {
        $data = Gambar::find($id, 'id_produk');

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

    public function showHampers(string $id)
    {
        $data = Gambar::find($id, 'id_hampers');

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
    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_gambar' => 'required|numeric|exists:gambar,id_gambar',
            'foto' => 'required',
        ], [
            'required' => ':attribute harus diisi',
            'numeric' => ':attribute harus berupa angka',
            'exists' => ':attribute tidak ditemukan',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $data = Gambar::find($request->id_gambar);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $picture = $request->file('foto');

        DB::beginTransaction();

        try {
            $imageName = $data->public_id;

            $url = (new FunctionHelper())
                ->uploadImage($picture, $imageName);

            $data->update([
                'url' => $url,
            ]);

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
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Gambar::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        DB::beginTransaction();
        $response = null;

        try {
            $response = (new FunctionHelper())
                ->deleteImage($data->public_id);
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
            'response' => $response,
        ], 200);
    }
}
