<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\HistoriSaldo;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoriSaldoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = HistoriSaldo::with('user')->get();

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

    public function indexSelf()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $data = HistoriSaldo::with('user')->where('id_user', $user->id)->get();

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
        $data = HistoriSaldo::with('user')
        ->orderBy('tanggal' ,'asc')
        ->paginate(10);

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
        $request->validate([
            'saldo' => 'required|numeric',
            'nama_bank' => 'required|string',
            'no_rek' => 'required|string',
        ]);

        if ($request->saldo % 50000 != 0) {
            return response()->json([
                'message' => 'Saldo harus kelipatan 50.000',
            ], 400);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        DB::beginTransaction();

        try {
            HistoriSaldo::create([
                'id_user' => $user->id,
                'saldo' => $request->saldo,
                'nama_bank' => $request->nama_bank,
                'no_rek' => $request->no_rek,
            ]);
            $user->update([
                'saldo' => $user->saldo - $request->saldo,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Data tidak berhasil disimpan',
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil disimpan',
        ], 200);
    }

    public function konfirmasi(string $id)
    {
        $data = HistoriSaldo::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        if ($data->tanggal != null) {
            return response()->json([
                'message' => 'Data sudah dikonfirmasi',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data->update([
                'tanggal' => Carbon::now(),
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Data tidak berhasil dikonfirmasi',
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dikonfirmasi',
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = HistoriSaldo::with('user')->find($id);

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
        $data = HistoriSaldo::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'tanggal' => 'required|date',
            'saldo' => 'required|numeric',
            'nama_bank' => 'required|string',
            'no_rek' => 'required|string',
        ]);

        if ($request->saldo % 50000 != 0) {
            return response()->json([
                'message' => 'Saldo harus kelipatan 50.000',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $data->update([
                'tanggal' => $request->tanggal,
                'saldo' => $request->saldo,
                'nama_bank' => $request->nama_bank,
                'no_rek' => $request->no_rek,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Data tidak berhasil diubah',
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil diubah',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = HistoriSaldo::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
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
}