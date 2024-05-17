<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\DetailHampers;
use App\Models\DetailTransaksi;
use App\Models\Hampers;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
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

        $limitOrStok = ($produk->status === 'PO') ? $produk->limit : $produk->stok;
        $remaining = $limitOrStok - (int) $totalJumlah;

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
            $limitOrStok = ($produk->status === 'PO') ? $produk->limit : $produk->stok;
            $remaining = $limitOrStok - (int) $totalTransaksiSum;

            $arrayCounter[] = [
                'id_produk' => $detail->id_produk,
                'id_kategori' => $produk->id_kategori,
                'nama_produk' => $produk->nama_produk,
                'ukuran' => $produk->ukuran,
                'status' => $produk->status,
                'limit' => $produk->limit,
                'stok' => $produk->stok,
                'count' => (int) $totalTransaksiSum,
                'remaining' => $remaining,
            ];
        }

        // Calculate the minimum remaining value based on the status
        $minRemaining = null;
        foreach ($arrayCounter as $item) {
            if ($item['status'] === 'PO') {
                $value = $item['limit'] - $item['count'];
            } else {
                $value = $item['stok'] - $item['count'];
            }
            if ($minRemaining === null || $value < $minRemaining) {
                $minRemaining = $value;
            }
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $arrayCounter,
            'min' => $minRemaining,
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
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'tanggal_ambil' => 'sometimes|date',
            'is_using_poin' => 'required|boolean',
            'total' => 'required|numeric',
            'tipe_delivery' => 'required|string',
            'status' => 'required|string',
            'nama_penerima' => 'required|string',
            'no_telp_penerima' => 'required|string',
            'lokasi' => 'sometimes|string',
            'keterangan' => 'sometimes|string',
        ], [
            'id_user.required' => 'ID user tidak boleh kosong',
            'id_user.exists' => 'ID user tidak ditemukan',
            'tanggal_ambil.required' => 'Tanggal ambil tidak boleh kosong',
            'tanggal_ambil.date' => 'Tanggal ambil harus berupa tanggal',
            'is_using_poin.required' => 'Penggunaan poin tidak boleh kosong',
            'is_using_poin.boolean' => 'Penggunaan poin harus berupa boolean',
            'total.required' => 'Total tidak boleh kosong',
            'total.numeric' => 'Total harus berupa angka',
            'tipe_delivery.required' => 'Tipe delivery tidak boleh kosong',
            'tipe_delivery.string' => 'Tipe delivery harus berupa teks',
            'status.required' => 'Status tidak boleh kosong',
            'status.string' => 'Status harus berupa teks',
            'nama_penerima.required' => 'Nama penerima tidak boleh kosong',
            'nama_penerima.string' => 'Nama penerima harus berupa teks',
            'no_telp_penerima.required' => 'Nomor telepon penerima tidak boleh kosong',
            'no_telp_penerima.string' => 'Nomor telepon penerima harus berupa teks',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $transaksi = new Transaksi();

        $latestNota = Transaksi::latest('no_nota')->first()->no_nota;
        $number = (int) substr($latestNota, 6, 3);
        $number++;
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);
        $transaksi->no_nota = date('y') . '.' . date('m') . '.' . $number;

        $transaksi->id_user = $user->id_user;
        $transaksi->tanggal_pesan = date('Y-m-d H:i:s');

        if ($request->tanggal_ambil) {
            $transaksi->tanggal_ambil = $request->tanggal_ambil;
        }

        $transaksi->penggunaan_poin = $request->is_using_poin ? $user->poin : 0;

        $transaksi->total = $request->is_using_poin ? max(0, $request->total - ($user->poin * 100)) : $request->total;
        $transaksi->radius = 0;
        $transaksi->ongkir = 0;
        $transaksi->tip = 0;

        $transaksi->tipe_delivery = $request->tipe_delivery;
        $transaksi->status = $request->status;

        $transaksi->nama_penerima = $request->nama_penerima;
        $transaksi->no_telp_penerima = $request->no_telp_penerima;

        if ($request->lokasi) {
            $transaksi->lokasi = $request->lokasi;
        }

        if ($request->keterangan) {
            $transaksi->keterangan = $request->keterangan;
        }

        DB::beginTransaction();

        try {
            $transaksi->save();

            $cartData = Cart::where('id_user', $user->id_user)->get();

            foreach ($cartData as $cart) {
                $detailTransaksi = new DetailTransaksi();
                $detailTransaksi->no_nota = $transaksi->no_nota;
                $detailTransaksi->id_produk = $cart->id_produk ?? null;
                $detailTransaksi->id_hampers = $cart->id_hampers ?? null;
                $detailTransaksi->jumlah = $cart->jumlah;
                $detailTransaksi->harga_saat_beli = $cart->produk->harga ?? $cart->hampers->harga;
                $detailTransaksi->save();

                if ($cart->id_produk) {
                    $produk = Produk::find($cart->id_produk);
                    if ($produk->status === 'READY') {
                        $produk->stok -= $cart->jumlah;
                        $produk->save();
                    }
                } else if ($cart->id_hampers) {
                    $dt = DetailHampers::where('id_hampers', $cart->id_hampers)->get();
                    foreach ($dt as $detail) {
                        if ($detail->id_produk === null) {
                            continue;
                        }

                        $produk = Produk::find($detail->id_produk);
                        if ($produk->status === 'READY') {
                            $produk->stok -= $cart->jumlah * $detail->jumlah;
                            $produk->save();
                        }
                    }
                }
            }

            Cart::where('id_user', $user->id_user)->delete();

            try {
                $points = DB::select("SELECT p3l.calculate_points(?) AS points;", [$transaksi->no_nota]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 500);
            }

            $transaksi->penambahan_poin = $points[0]->points;

            $user = User::find($user->id_user);
            $transaksi->poin_sebelum_penambahan = $user->poin;
            $user->poin -= $transaksi->penggunaan_poin;
            $transaksi->poin_setelah_penambahan = $user->poin + $transaksi->penambahan_poin - $transaksi->penggunaan_poin;

            $transaksi->save();
            $user->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to save data',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data successfully saved',
            'data' => $transaksi,
            'produk' => $cartData,
        ], 201);
    }

    public function batalTransaksi(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'no_nota' => 'required|exists:transaksi,no_nota',
        ], [
            'no_nota.required' => 'No nota tidak boleh kosong',
            'no_nota.exists' => 'No nota tidak ditemukan',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $transaksi = Transaksi::where('no_nota', $request->no_nota)->first();

        if ($transaksi->status === 'Terkirim') {
            return response()->json([
                'message' => 'Transaksi tidak dapat dibatalkan'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $transaksi->status = 'Dibatalkan';
            $transaksi->tanggal_ambil = null;
            $transaksi->save();

            $detailTransaksi = DetailTransaksi::where('no_nota', $request->no_nota)->get();

            foreach ($detailTransaksi as $detail) {
                if ($detail->id_produk) {
                    $produk = Produk::find($detail->id_produk);
                    if ($produk->status === 'READY') {
                        $produk->stok += $detail->jumlah;
                        $produk->save();
                    }
                } else if ($detail->id_hampers) {
                    $dt = DetailHampers::where('id_hampers', $detail->id_hampers)->get();
                    foreach ($dt as $item) {
                        if ($item->id_produk === null) {
                            continue;
                        }

                        $produk = Produk::find($item->id_produk);
                        if ($produk->status === 'READY') {
                            $produk->stok += $detail->jumlah * $item->jumlah;
                            $produk->save();
                        }
                    }
                }
            }

            $user = User::find($transaksi->id_user);
            $user->poin = $transaksi->poin_sebelum_penggunaan;
            $user->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membatalkan transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Transaksi berhasil dibatalkan',
            'data' => $transaksi,
        ], 200);
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