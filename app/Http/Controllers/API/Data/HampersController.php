<?php

namespace App\Http\Controllers\API\Data;

use App\Models\DetailHampers;
use App\Models\Gambar;
use App\Models\Hampers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HampersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Hampers::all()->load('gambar');

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
        $data = Hampers::with(['detail_hampers' => function ($query) {
                    $query->whereNotNull('id_produk');
                }, 'detail_hampers.produk:id_produk,id_kategori,nama_produk,ukuran', 'gambar'])
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
        $data = Hampers::with('detail_hampers')
            ->whereAny(['id_hampers', 'nama_hampers', 'harga'], 'LIKE', '%'.$data.'%')
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
            'nama_hampers' => 'required|max:255',
            'harga' => 'required|numeric|gte:0',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data = Hampers::create([
                'nama_hampers' => $request->nama_hampers,
                'harga' => $request->harga,
            ]);

            DetailHampers::create([
                'id_hampers' => $data->id_hampers,
                'id_produk' => null,
                'id_bahan_baku' => 25,
                'jumlah' => 1,
            ]);

            DetailHampers::create([
                'id_hampers' => $data->id_hampers,
                'id_produk' => null,
                'id_bahan_baku' => 26,
                'jumlah' => 1,
            ]);

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
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Hampers::with('gambar')->find($id);

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
        $data = Hampers::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama_hampers' => 'sometimes|max:255',
            'harga' => 'sometimes|numeric|gte:0',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'nama_hampers',
            'harga',
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
                'message' => 'Failed to create data',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data successfully created',
            'data' => $data,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Hampers::find($id);

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

            app(DetailHampersController::class)
                ->destroyAll($data->id_hampers);

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
