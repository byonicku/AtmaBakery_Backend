<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\PengadaanBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PengadaanBahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = PengadaanBahanBaku::join('bahan_baku', 'pengadaan_bahanbaku.id_bahan_baku', '=', 'bahan_baku.id_bahan_baku')
            ->get();

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
        $data = PengadaanBahanBaku::join('bahan_baku', 'pengadaan_bahanbaku.id_bahan_baku', '=', 'bahan_baku.id_bahan_baku')
            ->paginate(10);

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
        $data = PengadaanBahanBaku::join('bahan_baku', 'pengadaan_bahanbaku.id_bahan_baku', '=', 'bahan_baku.id_bahan_baku')
            ->whereAny(['id_bahan_baku', 'stok', 'harga', 'tanggal_pembelian'], 'LIKE', '%' . $data . '%')->get();

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
            'id_bahan_baku' => 'required|exists:bahan_baku,id_bahan_baku',
            'stok' => 'required|gte:0|numeric',
            'harga' => 'required|gte:0|numeric',
            'tanggal_pembelian' => 'required|date',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data = PengadaanBahanBaku::create([
                'id_bahan_baku' => $request->id_bahan_baku,
                'stok' => $request->stok,
                'harga' => $request->harga,
                'tanggal_pembelian' => $request->tanggal_pembelian,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
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
        $data = PengadaanBahanBaku::find($id);

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
        $data = PengadaanBahanBaku::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'id_bahan_baku' => [
                'sometimes',
                'exists:bahan_baku,id_bahan_baku',
            ],
            'stok' => 'sometimes|gte:0|numeric',
            'harga' => 'sometimes|gte:0|numeric',
            'tanggal_pembelian' => 'sometimes|date',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'id_bahan_baku',
            'stok',
            'harga',
            'tanggal_pembelian',
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
            'message' => 'Data successfully updated',
            'data' => $data,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = PengadaanBahanBaku::find($id);

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
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data successfully deleted',
        ], 200);
    }
}