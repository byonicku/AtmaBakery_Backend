<?php

namespace App\Http\Controllers\API\Data;

use App\Models\Karyawan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Karyawan::all();

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
            'nama' => 'required',
            'no_telp' => 'required',
            'email' => 'required|email|exists:karyawan,email',
            'hire_date' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
            ], 400);
        }

        DB::beginTransaction();
        try {
            $data = Karyawan::create([
                'nama' => $request->nama,
                'no_telp' => $request->no_telp,
                'email' => $request->email,
                'hire_date' => $request->hire_date,
                'gaji' => 0,
                'bonus' => 0
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
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Karyawan::find($id);

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
        $data = Karyawan::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama' => 'sometimes|max:255',
            'no_telp' => 'sometimes',
            'email' => 'sometimes|email|unique:karyawan,email',
            'hire_date' => 'sometimes',
            'gaji' => 'sometimes',
            'bonus' => 'sometimes',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'nama',
            'no_telp',
            'email',
            'hire_date',
            'gaji',
            'bonus',
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
        $data = Karyawan::find($id);

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
            ], 500);
        }

        return response()->json([
            'message' => 'Data successfully deleted',
        ], 200);
    }
}
