<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\Hampers;
use App\Models\Produk;
use App\Models\Transaksi;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function indexHistorySelf()
    {
        $data = Auth::user();

        if ($data == null) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
            ->where('id_user', '=', $data->id_user)
            ->orderByDesc('no_nota')
            ->get();

        if (count($transaksi) == 0) {
            return response()->json([
                'message' => 'Data kosong',
            ], 404);
        }

        $data = $transaksi->map(function ($trans) {
            $trans->detail_transaksi = $trans->detail_transaksi->map(function ($detail) {
                if ($detail->produk !== null) {
                    $detail->subtotal = $detail->jumlah * $detail->harga_saat_beli;
                    $detail->nama_produk = $detail->produk->nama_produk;
                } else if ($detail->hampers !== null) {
                    $detail->subtotal = $detail->jumlah * $detail->harga_saat_beli;
                    $detail->nama_produk = $detail->hampers->nama_hampers;
                } else {
                    $detail->subtotal = null;
                    $detail->nama_produk = null;
                }

                unset ($detail->produk);
                unset ($detail->hampers);

                return $detail;
            });

            return $trans;
        });

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
        ], 200);
    }

    public function paginateHistorySelf()
    {
        $data = Auth::user();

        $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
            ->where('id_user', '=', $data->id_user)
            ->orderByDesc('no_nota')
            ->paginate(10);

        if (count($transaksi) == 0) {
            return response()->json([
                'message' => 'Data kosong',
            ], 404);
        }

        $data = $transaksi->map(function ($trans) {
            $trans->detail_transaksi = $trans->detail_transaksi->map(function ($detail) {
                if ($detail->produk !== null) {
                    $detail->subtotal = $detail->jumlah * $detail->harga_saat_beli;
                    $detail->nama_produk = $detail->produk->nama_produk;
                } else if ($detail->hampers !== null) {
                    $detail->subtotal = $detail->jumlah * $detail->harga_saat_beli;
                    $detail->nama_produk = $detail->hampers->nama_hampers;
                } else {
                    $detail->subtotal = null;
                    $detail->nama_produk = null;
                }

                unset ($detail->produk);
                unset ($detail->hampers);

                return $detail;
            });

            return $trans;
        });

        $transaksi->data = $data;

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $transaksi,
        ], 200);
    }

    public function paginateHistory(string $id_user)
    {
        // Fetch transactions with their detail transactions including product info
        $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
            ->where('id_user', '=', $id_user)
            ->orderByDesc('no_nota')
            ->paginate(10);

        if (count($transaksi) == 0) {
            return response()->json([
                'message' => 'Data kosong',
            ], 404);
        }

        $data = $transaksi->map(function ($trans) {
            $trans->detail_transaksi = $trans->detail_transaksi->map(function ($detail) {
                if ($detail->produk !== null) {
                    $detail->subtotal = $detail->jumlah * $detail->harga_saat_beli;
                    $detail->nama_produk = $detail->produk->nama_produk;
                } else if ($detail->hampers !== null) {
                    $detail->subtotal = $detail->jumlah * $detail->harga_saat_beli;
                    $detail->nama_produk = $detail->hampers->nama_hampers;
                } else {
                    $detail->subtotal = null;
                    $detail->nama_produk = null;
                }

                unset ($detail->produk);
                unset ($detail->hampers);

                return $detail;
            });

            return $trans;
        });

        $transaksi->data = $data;

        // Return the data
        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
        ], 200);
    }

    public function countTransaksi(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_produk' => 'required|exists:produk,id_produk',
            'po_date' => 'required|date',
        ], [
            'id_produk.required' => 'ID produk tidak boleh kosong',
            'id_produk.exists' => 'ID produk tidak ditemukan',
            'po_date.required' => 'Tanggal PO tidak boleh kosong',
            'po_date.date' => 'Tanggal PO harus berupa tanggal',
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => $validate->errors()->first()], 400);
        }

        $produk = Produk::find($request->id_produk);

        $directTransaksiSum = Transaksi::whereHas('detail_transaksi', function ($query) use ($request) {
            $query->where('id_produk', $request->id_produk);
        })->whereDate('tanggal_ambil', $request->po_date)
            ->join('detail_transaksi', 'transaksi.no_nota', '=', 'detail_transaksi.no_nota')
            ->sum('detail_transaksi.jumlah');

        $hampersTransaksiSum = Transaksi::whereHas('detail_transaksi', function ($query) use ($request) {
            $query->whereHas('hampers.detail_hampers', function ($subQuery) use ($request) {
                $subQuery->where('id_produk', $request->id_produk);
            });
        })->whereDate('tanggal_ambil', $request->po_date)
            ->join('detail_transaksi as dt', 'transaksi.no_nota', '=', 'dt.no_nota')
            ->join('hampers', 'hampers.id_hampers', '=', 'dt.id_hampers')
            ->join('detail_hampers as dh', 'hampers.id_hampers', '=', 'dh.id_hampers')
            ->where('dh.id_produk', $request->id_produk)
            ->sum(DB::raw('dt.jumlah * dh.jumlah'));

        $totalJumlah = $directTransaksiSum + $hampersTransaksiSum;

        $remaining = $produk->limit - $totalJumlah;

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => [
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'ukuran' => $produk->ukuran,
                'status' => $produk->status,
                'limit' => $produk->limit,
                'stok' => $produk->stok,
                'remaining' => $remaining,
            ],
        ], 200);
    }


    public function countTransaksiWithHampers(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_hampers' => 'required|exists:hampers,id_hampers',
            'po_date' => 'required|date',
        ], [
            'id_hampers.required' => 'ID hampers tidak boleh kosong',
            'id_hampers.exists' => 'ID hampers tidak ditemukan',
            'po_date.required' => 'Tanggal PO tidak boleh kosong',
            'po_date.date' => 'Tanggal PO harus berupa tanggal',
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => $validate->errors()->first()], 400);
        }

        $hampers = Hampers::with(['detail_hampers.produk'])->find($request->id_hampers);

        $arrayCounter = [];

        foreach ($hampers->detail_hampers as $detail) {
            if ($detail->produk === null) {
                continue;
            }

            $directTransaksiSum = Transaksi::whereHas('detail_transaksi', function ($query) use ($detail) {
                $query->where('id_produk', '=', $detail->id_produk);
            })->whereDate('tanggal_ambil', '=', $request->po_date)
                ->join('detail_transaksi', 'transaksi.no_nota', '=', 'detail_transaksi.no_nota')
                ->sum('detail_transaksi.jumlah');

            $hampersTransaksiSum = Transaksi::whereHas('detail_transaksi', function ($query) use ($request) {
                $query->where('id_hampers', '=', $request->id_hampers);
            })->whereDate('tanggal_ambil', '=', $request->po_date)
                ->join('detail_transaksi as dt', 'transaksi.no_nota', '=', 'dt.no_nota')
                ->join('detail_hampers as dh', 'dh.id_hampers', '=', 'dt.id_hampers')
                ->where('dh.id_produk', '=', $detail->id_produk)
                ->sum(DB::raw('dt.jumlah * dh.jumlah'));

            $totalTransaksiSum = $directTransaksiSum + $hampersTransaksiSum;

            $produk = $detail->produk;
            $arrayCounter[] = [
                'id_produk' => $detail->id_produk,
                'id_kategori' => $produk->id_kategori,
                'nama_produk' => $produk->nama_produk,
                'ukuran' => $produk->ukuran,
                'status' => $produk->status,
                'limit' => $produk->limit,
                'stok' => $produk->stok,
                'count' => (int) $totalTransaksiSum,
                'remaining' => $produk->limit - (int) $totalTransaksiSum,
            ];
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $arrayCounter,
        ], 200);
    }

    public function search(Request $request, string $id_user)
    {
        $data = $request->data;

        if ($data == null) {
            return response()->json([
                'message' => 'Data kosong',
            ], 404);
        }

        $transaksi = Transaksi::with('user', 'alamat')
            ->where('id_user', '=', $id_user)
            ->whereAny([
                'no_nota',
                'id_user',
                'id_alamat',
                'tanggal_pesan',
                'tanggal_lunas',
                'tanggal_ambil',
                'penggunaan_poin',
                'total',
                'radius',
                'tip',
                'tipe_delivery',
                'status',
            ], 'LIKE', '%' . $data . '%')
            ->orderByDesc('no_nota')
            ->get();

        if (count($transaksi) == 0) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $transaksi,
        ], 200);
    }

    public function searchSelf(Request $request)
    {
        $user = Auth::user();

        if ($user == null) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'data' => 'required|string',
        ], [
            'data.required' => 'Data tidak boleh kosong',
            'data.string' => 'Data harus berupa teks',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $data = $request->data;

        $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
            ->where('id_user', '=', $user->id_user)
            ->whereHas('detail_transaksi', function ($query) use ($data) {
                $query->whereHas('produk', function ($query) use ($data) {
                    $query->where('nama_produk', 'LIKE', '%' . $data . '%');
                })->orWhereHas('hampers', function ($query) use ($data) {
                    $query->where('nama_hampers', 'LIKE', '%' . $data . '%');
                });
            })
            ->orderByDesc('no_nota')
            ->get();

        if (count($transaksi) == 0) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $data = $transaksi->map(function ($trans) {
            $trans->detail_transaksi = $trans->detail_transaksi->map(function ($detail) {
                if ($detail->produk !== null) {
                    $detail->subtotal = $detail->jumlah * $detail->harga_saat_beli;
                    $detail->nama_produk = $detail->produk->nama_produk;
                } else if ($detail->hampers !== null) {
                    $detail->subtotal = $detail->jumlah * $detail->harga_saat_beli;
                    $detail->nama_produk = $detail->hampers->nama_hampers;
                } else {
                    $detail->subtotal = null;
                    $detail->nama_produk = null;
                }

                unset ($detail->produk);
                unset ($detail->hampers);

                return $detail;
            });

            return $trans;
        });

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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}