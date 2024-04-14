<?php

namespace App\Http\Controllers\API\Data;

use App\Models\DetailHampers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DetailHampersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = DetailHampers::all();

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
        $validate = Validator::make($request->all(), [
            'id_produk' => 'sometimes|exists:produk,id_produk',
            'id_hampers' => 'required|exists:hampers,id_hampers',
            'id_bahan_baku' => 'sometimes|exists:bahan_baku,id_bahan_baku',
            'jumlah' => 'required|gt:0',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        if (!$request->id_produk && !$request->id_hampers) {
            return response()->json([
                'message' => 'id_produk or id_hampers is required',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data = DetailHampers::create([
                'id_produk' => $request->id_produk ?? null,
                'id_hampers' => $request->id_hampers,
                'id_bahan_baku' => $request->id_bahan_baku ?? null,
                'jumlah' => $request->jumlah,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to store data',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data successfully stored',
            'data' => $data,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id_produk)
    {
        $data = DetailHampers::all()->where($id_produk, 'id_produk')->values();

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
        $data = DetailHampers::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'id_produk' => 'sometimes|exists:produk,id_produk',
            'id_hampers' => 'sometimes|exists:hampers,id_hampers',
            'id_bahan_baku' => 'sometimes|exists:bahan_baku,id_bahan_baku',
            'jumlah' => 'sometimes|gt:0',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        if (!$request->id_produk && !$request->id_hampers) {
            return response()->json([
                'message' => 'id_produk or id_hampers is required',
            ], 400);
        }

        $fillableAttributes = [
            'id_produk',
            'id_hampers',
            'id_bahan_baku',
            'jumlah',
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
        $data = DetailHampers::find($id);

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
    }

    public function destroyAll(string $id_hampers)
    {
        $data = DetailHampers::where('id_hampers', $id_hampers)->get();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($data as $detailHampers) {
                $detailHampers->delete();
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
