<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\HistoriBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class HistoriBahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = HistoriBahanBaku::all();

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
        $data = HistoriBahanBaku::paginate(10);

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
        $data = HistoriBahanBaku::whereHas('bahan_baku', function ($query) use ($data) {
            $query->where('nama_bahan_baku', 'LIKE', '%' . $data . '%');
        })->orWhere('jumlah', 'LIKE', '%' . $data . '%')
            ->orWhere('tanggal_pakai', 'LIKE', '%' . $data . '%')
            ->with('bahan_baku')
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_bahan_baku' => 'required|exists:bahan_baku,id_bahan_baku',
            'jumlah' => 'required|gte:0|numeric',
            'tanggal_pakai' => 'required|date',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data = HistoriBahanBaku::create([
                'id_bahan_baku' => $request->id_bahan_baku,
                'jumlah' => $request->jumlah,
                'tanggal_pakai' => $request->tanggal_pakai,
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
        $data = HistoriBahanBaku::find($id);

        if (!$data) {
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = HistoriBahanBaku::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data is not found',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'id_bahan_baku' => 'sometimes|exists:bahan_baku,id_bahan_baku',
            'jumlah' => 'sometimes|gte:0|numeric',
            'tanggal_pakai' => 'sometimes|date',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'id_bahan_baku',
            'jumlah',
            'tanggal_pakai',
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
        $data = HistoriBahanBaku::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data is not found',
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