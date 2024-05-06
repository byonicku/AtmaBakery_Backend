<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
                'message' => 'Data kosong',
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
        ], 200);
    }

    public function paginate()
    {
        $data = User::where('id_role', '=', 'CUST')->paginate(10);

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

    public function search(string $data)
    {
        $data = User::where('id_role', '=', 'CUST')
            ->whereAny(['nama', 'email', 'no_telp'], 'LIKE', '%' . $data . '%')->get();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
        ], 200);
    }

    public function showSelf()
    {
        $data = Auth::user();

        if (!$data) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
        ], 200);
    }

    public function updateSelf(Request $request)
    {
        $data = Auth::user();

        if (!$data) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama' => 'sometimes|max:255',
            'no_telp' => [
                'sometimes',
                'digits_between:10,13',
                Rule::unique('user')->ignore($data->no_telp, 'no_telp'),
                'regex:/^(?:\+?08)(?:\d{2,3})?[ -]?\d{3,4}[ -]?\d{4}$/',
            ],
            'foto_profil' => 'sometimes|image|mimes:jpeg,png,jpg|max:1024',
            'jenis_kelamin' => 'sometimes|in:L,P',
        ], [
            'required' => ':attribute harus diisi',
            'no_telp.regex' => 'Nomor telepon tidak valid, pastikan mulai dari 08',
            'no_telp.digits_between' => 'Nomor telepon harus berisi 10-13 digit',
            'no_telp.unique' => 'Nomor telepon sudah terdaftar',
            'foto_profil.image' => 'Foto profil harus berupa gambar',
            'foto_profil.mimes' => 'Foto profil harus berformat jpeg, png, jpg',
            'foto_profil.max' => 'Ukuran foto profil maksimal 1 MB',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $fillableAttributes = [
            'nama',
            'no_telp',
            'jenis_kelamin',
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
                'message' => 'Gagal melakukan update',
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil diupdate',
            'data' => $data,
        ], 200);
    }


    public function updateSelfPassword(Request $request)
    {
        $data = Auth::user();

        if (!$data) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|min:8|confirmed', // pas post tambahin password_confirmation di formdata
        ], [
            'required' => ':attribute harus diisi',
            'password.confirmed' => 'Konfirmasi password tidak sesuai',
            'password.min' => 'Password minimal 8 karakter',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        if (!Hash::check($request->old_password, $data->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai',
            ], 400);
        }

        if (Hash::check($request->password, $data->password)) {
            return response()->json([
                'message' => 'Password baru tidak boleh sama dengan password lama',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data->update([
                'password' => Hash::make($request->password),
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal melakukan update',
            ], 500);
        }

        return response()->json([
            'message' => 'Password berhasil diupdate',
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
    public function update(Request $request, string $id)
    {
        $data = User::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nama' => 'sometimes|max:255',
            'no_telp' => 'sometimes|digits_between:10,13|unique:user,no_telp|regex:/^(?:\+?08)(?:\d{2,3})?[ -]?\d{3,4}[ -]?\d{4}$/',
            'foto_profil' => 'sometimes|image|mimes:jpeg,png,jpg|max:1024',
            'jenis_kelamin' => 'sometimes|in:L,P',
        ], [
            'required' => ':attribute harus diisi',
            'no_telp.regex' => 'Nomor telepon tidak valid, pastikan mulai dari 08',
            'no_telp.digits_between' => 'Nomor telepon harus berisi 10-13 digit',
            'no_telp.unique' => 'Nomor telepon sudah terdaftar',
            'foto_profil.image' => 'Foto profil harus berupa gambar',
            'foto_profil.mimes' => 'Foto profil harus berformat jpeg, png, jpg',
            'foto_profil.max' => 'Ukuran foto profil maksimal 1 MB',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P',
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
            'jenis_kelamin',
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
                'message' => 'Gagal melakukan update',
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil diupdate',
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
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        if ($data->foto_profil != null) {
            $publicId = $data->public_id;
            cloudinary()->destroy('atma-bakery/' . $publicId, [
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
                'message' => 'Data tidak berhasil dihapus',
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dihapus',
        ], 200);
    }

    public function destroyProfilePicSelf()
    {
        $data = Auth::user();

        if (!$data) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        if ($data->foto_profil == null) {
            return response()->json([
                'message' => 'Tidak ada foto profil yang dihapus',
            ], 404);
        }

        $publicId = $data->public_id;
        cloudinary()->destroy('atma-bakery/' . $publicId, [
            'invalidate' => true,
        ]);

        DB::beginTransaction();

        try {
            $data->update([
                'foto_profil' => null,
                'public_id' => null,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus foto profil',
            ], 500);
        }

        return response()->json([
            'message' => 'Foto profil berhasil dihapus',
        ], 200);
    }
}
