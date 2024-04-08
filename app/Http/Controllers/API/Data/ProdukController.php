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

    public function paginate()
    {
        $data = Produk::paginate(10);

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
        // Gambar wajib dikirim dengan key 'foto[]'

        $validate = Validator::make($request->all(), ([
            'nama_produk' => 'required|max:255',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'ukuran' => 'required:in:1,1/2',
            'harga' => 'required|min:0',
            'stok' => 'required|min:0',
            'limit' => 'required|min:0',
            'foto' => 'required|array|min:1|max:5',
            'foto.*' => 'image|mimes:jpg,jpeg,png|max:1024',
            'id_penitip' => 'nullable|exists:penitip,id_penitip',
            'status' => 'required|in:PO,READY',
        ]));

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $num_success = 0;

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

            $picture = $request->file('foto');

            foreach ($picture as $pic) {
                $imageName = time() . "-produk";

                $url = (new FunctionHelper())
                    ->uploadImage($pic, $imageName);

                $data = Gambar::create([
                    'id_produk' => $request->id_produk,
                    'url' => $url,
                    'public_id' => $imageName,
                ]);

                if ($data) {
                    $num_success++;
                }
            }

            DB::commit();
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
            'img_count_success' => $num_success,
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
        $data = Produk::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        $validate = Validator::make($request->all(), ([
            'nama_produk' => 'sometimes|max:255',
            'id_kategori' => 'sometimes|exists:kategori,id_kategori',
            'ukuran' => 'sometimes:in:1,1/2',
            'harga' => 'sometimes|min:0',
            'stok' => 'sometimes|min:0',
            'limit' => 'sometimes|min:0',
            'id_penitip' => 'nullable|exists:penitip,id_penitip',
            'status' => 'sometimes|in:PO,READY',
        ]));

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'nama_produk',
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
            foreach ($data->gambar as $gambar) {
                app(GambarController::class)
                    ->destroy($gambar->id_gambar);
            }

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
}
