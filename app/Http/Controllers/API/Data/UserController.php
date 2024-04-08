<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\Data\FunctionHelper;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = User::all();

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
        $data = User::paginate(10);

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

    public function showSelf()
    {
        $data = Auth::user();

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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = User::find($id);

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
        $data = User::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama' => 'sometimes|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('user')->ignore($data->email, 'email'),
            ],
            'no_telp' => 'sometimes|digits_between:10,13|unique:user,no_telp|regex:/^(?:\+?08)(?:\d{2,3})?[ -]?\d{3,4}[ -]?\d{4}$/',
            'foto_profil' => 'sometimes|image|mimes:jpeg,png,jpg|max:1024',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'nama',
            'email',
            'no_telp',
        ];

        $updateData = (new FunctionHelper())
                        ->updateDataMaker($fillableAttributes, $request);

        if ($request->hasFile('foto_profil')) {
            $imageName = null;

            if ($data->public_id == null) {
                $imageName = time() . "-profile";
            } else {
                $imageName = $data->public_id;
            }

            $uploadedFileUrl = (new FunctionHelper())
                                ->uploadImage($request->file('foto_profil'), $imageName);

            if ($data->public_id == null) {
                $updateData['public_id'] = $imageName;
            }

            $updateData['foto_profil'] = $uploadedFileUrl;
        }

        DB::beginTransaction();

        try {
            $data->update($updateData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update data',
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
        $data = User::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
            ], 404);
        }

        if ($data->foto_profil != null) {
            $publicId = $data->public_id;
            cloudinary()->destroy('atma-bakery/'. $publicId, [
                'invalidate' => true,
            ]);
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
