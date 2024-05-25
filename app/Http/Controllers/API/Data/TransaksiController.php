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

    public function paginateHistorySelf(Request $request)
    {
        $data = Auth::user();

        if (!$data) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        if ($request->query('status')) {
            $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
                ->where('id_user', '=', $data->id_user)
                ->where('status', '=', $request->query('status'))
                ->orderByDesc('no_nota')
                ->paginate(10);
        } else {
            $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
                ->where('id_user', '=', $data->id_user)
                ->orderByDesc('no_nota')
                ->paginate(10);
        }

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

    public function paginateHistory(Request $request, string $id_user)
    {
        if ($request->query('status')) {
            $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
                ->where('id_user', '=', $id_user)
                ->where('status', '=', $request->query('status'))
                ->orderByDesc('no_nota')
                ->paginate(10);
        } else {
            $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
                ->where('id_user', '=', $id_user)
                ->orderByDesc('no_nota')
                ->paginate(10);
        }

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

    public function paginateHistoryAll(Request $request)
    {
        if ($request->query('status')) {
            $date = $request->query('status');

            if ($date === "date") {
                $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
                    ->whereDate('tanggal_ambil', '=', date('Y-m-d', strtotime('+1 day')))
                    ->where('status', '=', 'Pesanan Diterima')
                    ->paginate(10);
            } else if ($date === "ubah") {
                $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
                    ->where('status', '=', 'Siap Pick Up')
                    ->orWhere('status', '=', 'Sedang Diantar Kurir')
                    ->orWhere('status', '=', 'Sedang Diantar Ojol')
                    ->orWhere('status', '=', 'Sedang Diproses')
                    ->paginate(10);
            } else {
                $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
                    ->where('status', '=', $request->query('status'))
                    ->paginate(10);
            }
        } else {
            $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
                ->paginate(10);
        }

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
            $query->where('id_produk', $request->id_produk)
                ->where('status', null);
        })->whereDate('tanggal_ambil', $request->po_date)
            ->join('detail_transaksi', 'transaksi.no_nota', '=', 'detail_transaksi.no_nota')
            ->sum('detail_transaksi.jumlah');

        $hampersTransaksiSum = Transaksi::whereHas('detail_transaksi', function ($query) use ($request) {
            $query->whereHas('hampers.detail_hampers', function ($subQuery) use ($request) {
                $subQuery->where('id_produk', $request->id_produk);
            })->where('status', null);
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
                $query->where('id_produk', '=', $detail->id_produk)
                    ->where('status', null);
            })->whereDate('tanggal_ambil', '=', $request->po_date)
                ->join('detail_transaksi', 'transaksi.no_nota', '=', 'detail_transaksi.no_nota')
                ->sum('detail_transaksi.jumlah');

            $hampersTransaksiSum = Transaksi::whereHas('detail_transaksi', function ($query) use ($request) {
                $query->where('id_hampers', '=', $request->id_hampers)
                    ->where('status', null);
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

        if ($request->query('status')) {
            $transaksi = Transaksi::with('user', 'alamat')
                ->where('id_user', '=', $id_user)
                ->where('status', '=', $request->query('status'))
                ->whereAny([
                    'no_nota',
                    'tanggal_pesan',
                    'tanggal_lunas',
                    'tanggal_ambil',
                    'total',
                    'tipe_delivery',
                    'status',
                ], 'LIKE', '%' . $data . '%')
                ->orderByDesc('no_nota')
                ->get();
        } else {
            $transaksi = Transaksi::with('user', 'alamat')
                ->where('id_user', '=', $id_user)
                ->whereAny([
                    'no_nota',
                    'tanggal_pesan',
                    'tanggal_lunas',
                    'tanggal_ambil',
                    'total',
                    'tipe_delivery',
                    'status',
                ], 'LIKE', '%' . $data . '%')
                ->orderByDesc('no_nota')
                ->get();
        }

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

        if ($request->query('status')) {
            $transaksi = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
                ->where('id_user', '=', $user->id_user)
                ->where('status', '=', $request->query('status'))
                ->whereHas('detail_transaksi', function ($query) use ($data) {
                    $query->whereHas('produk', function ($query) use ($data) {
                        $query->where('nama_produk', 'LIKE', '%' . $data . '%');
                    })->orWhereHas('hampers', function ($query) use ($data) {
                        $query->where('nama_hampers', 'LIKE', '%' . $data . '%');
                    });
                })
                ->orderByDesc('no_nota')
                ->get();
        } else {
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
        }

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

    public function searchAll(Request $request)
    {
        $data = $request->data;

        if ($data == null) {
            return response()->json([
                'message' => 'Data kosong',
            ], 404);
        }

        if ($request->query('status')) {
            $date = $request->query('status');

            if ($date === "date") {
                $transaksi = Transaksi::with(['detail_transaksi.produk', 'detail_transaksi.hampers'])
                    ->whereDate('tanggal_ambil', '=', date('Y-m-d', strtotime('+1 day')))
                    ->where('status', '=', 'Pesanan Diterima')
                    ->where(function ($query) use ($data) {
                        $query->where('no_nota', 'LIKE', '%' . $data . '%')
                            ->orWhere('total', 'LIKE', '%' . $data . '%')
                            ->orWhere('tipe_delivery', 'LIKE', '%' . $data . '%')
                            ->orWhereHas('detail_transaksi', function ($query) use ($data) {
                                $query->whereHas('produk', function ($query) use ($data) {
                                    $query->where('nama_produk', 'LIKE', '%' . $data . '%');
                                })->orWhereHas('hampers', function ($query) use ($data) {
                                    $query->where('nama_hampers', 'LIKE', '%' . $data . '%');
                                });
                            });
                    })
                    ->get();
            } else if ($date === "ubah") {
                $transaksi = Transaksi::with(['detail_transaksi.produk', 'detail_transaksi.hampers'])
                    ->where('status', '=', 'Siap Pick Up')
                    ->orWhere('status', '=', 'Sedang Diantar Kurir')
                    ->orWhere('status', '=', 'Sedang Diantar Ojol')
                    ->orWhere('status', '=', 'Sedang Diproses')
                    ->where(function ($query) use ($data) {
                        $query->where('no_nota', 'LIKE', '%' . $data . '%')
                            ->orWhere('total', 'LIKE', '%' . $data . '%')
                            ->orWhere('tipe_delivery', 'LIKE', '%' . $data . '%')
                            ->orWhereHas('detail_transaksi', function ($query) use ($data) {
                                $query->whereHas('produk', function ($query) use ($data) {
                                    $query->where('nama_produk', 'LIKE', '%' . $data . '%');
                                })->orWhereHas('hampers', function ($query) use ($data) {
                                    $query->where('nama_hampers', 'LIKE', '%' . $data . '%');
                                });
                            });
                    })
                    ->get();
            } else {
                $transaksi = Transaksi::with(['detail_transaksi.produk', 'detail_transaksi.hampers'])
                    ->where('status', $request->query('status'))
                    ->where(function ($query) use ($data) {
                        $query->where('no_nota', 'LIKE', '%' . $data . '%')
                            ->orWhere('total', 'LIKE', '%' . $data . '%')
                            ->orWhere('tipe_delivery', 'LIKE', '%' . $data . '%')
                            ->orWhereHas('detail_transaksi', function ($query) use ($data) {
                                $query->whereHas('produk', function ($query) use ($data) {
                                    $query->where('nama_produk', 'LIKE', '%' . $data . '%');
                                })->orWhereHas('hampers', function ($query) use ($data) {
                                    $query->where('nama_hampers', 'LIKE', '%' . $data . '%');
                                });
                            });
                    })
                    ->get();
            }
        } else {
            $transaksi = Transaksi::with(['detail_transaksi.produk', 'detail_transaksi.hampers'])
                ->where(function ($query) use ($data) {
                    $query->where('no_nota', 'LIKE', '%' . $data . '%')
                        ->orWhere('total', 'LIKE', '%' . $data . '%')
                        ->orWhere('tipe_delivery', 'LIKE', '%' . $data . '%')
                        ->orWhereHas('detail_transaksi', function ($query) use ($data) {
                            $query->whereHas('produk', function ($query) use ($data) {
                                $query->where('nama_produk', 'LIKE', '%' . $data . '%');
                            })->orWhereHas('hampers', function ($query) use ($data) {
                                $query->where('nama_hampers', 'LIKE', '%' . $data . '%');
                            });
                        });
                })
                ->get();
        }

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 404);
        }

        if ($user->id_role !== "CUST") {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $validate = Validator::make($request->all(), [
            'tanggal_ambil' => 'sometimes|date',
            'penggunaan_poin' => 'required|numeric',
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
            'penggunaan_poin.required' => 'Penggunaan poin tidak boleh kosong',
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
        if (!$latestNota) {
            $latestNota = '00.00.000';
        }
        $number = (int) substr($latestNota, 6);
        $number++;
        $number = str_pad($number, 3, '0', STR_PAD_LEFT);
        $transaksi->no_nota = date('y') . '.' . date('m') . '.' . $number;

        $transaksi->id_user = $user->id_user;
        $transaksi->tanggal_pesan = date('Y-m-d H:i:s');

        $transaksi->tanggal_ambil = $request->tanggal_ambil;

        $transaksi->penggunaan_poin = $request->penggunaan_poin;

        $transaksi->total = max(0, $request->total - ($request->penggunaan_poin * 100));
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

                if ($cart->id_produk) {
                    $produk = Produk::find($cart->id_produk);
                    if ($produk->status === 'READY' || $cart->status === 'READY') {
                        if ($produk->stok < $cart->jumlah) {
                            DB::rollBack();
                            return response()->json([
                                'message' => 'Stok produk ' . $produk->nama_produk . ' tidak mencukupi, silahkan hapus produk dari keranjang anda',
                            ], 400);
                        }

                        $produk->stok -= $cart->jumlah;
                        if ($produk->status === "PO" && $cart->status === "READY") {
                            $detailTransaksi->status = "READY";
                        }

                        $produk->save();
                    } else {
                        $remaining = (new FunctionHelper())->countStok($produk->id_produk, $transaksi->tanggal_ambil);
                        if ($remaining <= 0) {
                            DB::rollBack();
                            return response()->json([
                                'message' => 'Limit produk ' . $produk->nama_produk . ' tidak mencukupi, silahkan hapus produk dari keranjang anda',
                            ], 400);
                        }
                    }
                } else if ($cart->id_hampers) {
                    $dt = DetailHampers::where('id_hampers', $cart->id_hampers)->get();
                    foreach ($dt as $detail) {
                        if ($detail->id_produk === null) {
                            continue;
                        }

                        $produk = Produk::find($detail->id_produk);
                        if ($produk->status === 'READY') {
                            if ($produk->stok < $cart->jumlah * $detail->jumlah) {
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Stok produk ' . $produk->nama_produk . ' di dalam hampers tidak mencukupi, silahkan hapus hampers dari keranjang anda',
                                ], 400);
                            }
                            $produk->stok -= $cart->jumlah * $detail->jumlah;
                            $produk->save();
                        } else {
                            $remaining = (new FunctionHelper())->countStok($produk->id_produk, $transaksi->tanggal_ambil);
                            if ($remaining <= 0) {
                                DB::rollBack();
                                return response()->json([
                                    'message' => 'Limit produk ' . $produk->nama_produk . ' di dalam hampers tidak mencukupi, silahkan hapus hampers dari keranjang anda',
                                ], 400);
                            }
                        }
                    }
                }

                $detailTransaksi->save();
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
            $transaksi->poin_setelah_penambahan = $user->poin + $transaksi->penambahan_poin;

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

    public function uploadBuktiBayar(Request $request)
    {
        $data = Auth::user();

        $validate = Validator::make($request->all(), [
            'no_nota' => 'required|exists:transaksi,no_nota',
            'bukti_bayar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'no_nota.required' => 'No nota tidak boleh kosong',
            'no_nota.exists' => 'No nota tidak ditemukan',
            'bukti_bayar.required' => 'Bukti bayar tidak boleh kosong',
            'bukti_bayar.image' => 'Bukti bayar harus berupa gambar',
            'bukti_bayar.mimes' => 'Bukti bayar harus berformat jpeg, png, jpg',
            'bukti_bayar.max' => 'Bukti bayar tidak boleh lebih dari 2MB',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $transaksi = Transaksi::where('no_nota', $request->no_nota)->first();

        if ($transaksi->id_user !== $data->id_user) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan, atau bukan transaksi anda',
            ], 404);
        }

        if ($transaksi->status !== 'Menunggu Pembayaran') {
            return response()->json([
                'message' => 'Transaksi tidak dapat diubah',
            ], 400);
        }

        $updateData = [];

        $updateData['status'] = 'Menunggu Konfirmasi Pembayaran';
        $updateData['tanggal_lunas'] = Carbon::now();

        if ($request->hasFile('bukti_bayar')) {
            $imageName = null;

            if ($transaksi->public_id == null) {
                $imageName = time() . "-bukti-bayar";
            } else {
                $imageName = $transaksi->public_id;
            }

            $uploadedFileUrl = (new FunctionHelper())
                ->uploadImage($request->file('bukti_bayar'), $imageName);

            if ($transaksi->public_id == null) {
                $updateData['public_id'] = $imageName;
            }

            $updateData['bukti_pembayaran'] = $uploadedFileUrl;
        }

        DB::beginTransaction();

        try {
            $transaksi->update($updateData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengunggah bukti bayar',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Bukti bayar berhasil diunggah',
            'data' => $transaksi,
        ], 200);
    }

    public function konfirmasiAddJarakAdmin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'no_nota' => 'required|exists:transaksi,no_nota',
            'radius' => 'required|numeric|gte:0',
            'ongkir' => 'required|numeric|gte:0',
        ], [
            'no_nota.required' => 'No nota tidak boleh kosong',
            'no_nota.exists' => 'No nota tidak ditemukan',
            'radius' => 'Jarak tidak boleh kosong',
            'radius.numeric' => 'Jarak harus berupa angka',
            'radius.gte' => 'Jarak tidak boleh kurang dari 0',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $transaksi = Transaksi::where('no_nota', $request->no_nota)->first();

        if ($transaksi->status !== 'Menunggu Perhitungan Ongkir') {
            return response()->json([
                'message' => 'Transaksi tidak dapat diubah',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $transaksi->status = 'Menunggu Pembayaran';
            $transaksi->radius = $request->radius;
            $transaksi->ongkir = $request->ongkir;
            $transaksi->total += $request->ongkir;
            $transaksi->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengkonfirmasi transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Transaksi berhasil dikonfirmasi',
            'data' => $transaksi,
        ], 200);
    }

    public function konfirmasiTransaksiAdmin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'no_nota' => 'required|exists:transaksi,no_nota',
            'tip' => 'required|numeric|gte:0',
        ], [
            'no_nota.required' => 'No nota tidak boleh kosong',
            'no_nota.exists' => 'No nota tidak ditemukan',
            'tip.required' => 'Tip tidak boleh kosong',
            'tip.numeric' => 'Tip harus berupa angka',
            'tip.gte' => 'Tip tidak boleh kurang dari 0',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $transaksi = Transaksi::where('no_nota', $request->no_nota)->first();

        if ($transaksi->status !== 'Menunggu Konfirmasi Pembayaran') {
            return response()->json([
                'message' => 'Transaksi tidak dapat diubah',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $transaksi->status = 'Menunggu Konfirmasi Pesanan';
            $transaksi->tanggal_lunas = Carbon::now();
            if ($request->tip) {
                $transaksi->tip = $request->tip;
            }
            $transaksi->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengkonfirmasi transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Transaksi berhasil dikonfirmasi',
            'data' => $transaksi,
        ], 200);
    }

    public function konfirmasiTransaksiMO(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'no_nota' => 'required|exists:transaksi,no_nota',
        ], [
            'no_nota.required' => 'No nota tidak boleh kosong',
            'no_nota.exists' => 'No nota tidak ditemukan'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        $transaksi = Transaksi::where('no_nota', $request->no_nota)->first();

        if ($transaksi->status !== 'Menunggu Konfirmasi Pesanan') {
            return response()->json([
                'message' => 'Transaksi tidak dapat diubah',
            ], 400);
        }

        DB::statement('SET SESSION sql_require_primary_key=0');
        $bahan_baku_kurang = DB::select('CALL p3l.get_bahan_baku_details(?)', [$transaksi->no_nota]);

        DB::beginTransaction();

        try {
            $produk = $transaksi->detail_transaksi->pluck('produk');
            $hampers = $transaksi->detail_transaksi->pluck('hampers')->whereNotNull();
            $isHampersPO = false;

            foreach ($hampers as $item) {
                $detail_hampers = DetailHampers::with('produk')
                    ->where('id_hampers', $item->id_hampers)
                    ->where('id_produk', '!=', null)
                    ->get();

                $status = $detail_hampers->pluck('produk.status');

                if ($status->contains('PO')) {
                    $isHampersPO = true;
                    break;
                }
            }

            $detail_transaksi_status = $transaksi->detail_transaksi->pluck('status');
            $status = $produk->pluck('status');
            $cekStok = $produk->pluck('stok');

            if ($detail_transaksi_status->contains('READY') && !($status->contains('PO')) && !$isHampersPO) {
                if ($transaksi->tipe_delivery === 'Ambil') {
                    $transaksi->status = 'Siap Pick Up';
                } else if ($transaksi->tipe_delivery === 'Kurir') {
                    $transaksi->status = 'Sedang Diantar Kurir';
                } else {
                    $transaksi->status = 'Sedang Diantar Ojol';
                }
            } else {
                if (
                    $status->contains('PO') && ($cekStok->contains(0)
                        || $cekStok->contains(null))
                    || $isHampersPO
                ) {
                    $transaksi->status = 'Pesanan Diterima';
                } else {
                    if ($transaksi->tipe_delivery === 'Ambil') {
                        $transaksi->status = 'Siap Pick Up';
                    } else if ($transaksi->tipe_delivery === 'Kurir') {
                        $transaksi->status = 'Sedang Diantar Kurir';
                    } else {
                        $transaksi->status = 'Sedang Diantar Ojol';
                    }
                }
            }


            $user = User::find($transaksi->id_user);
            $user->poin += $transaksi->penambahan_poin;

            $user->save();
            $transaksi->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengkonfirmasi transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }

        if (count($bahan_baku_kurang) > 0) {
            return response()->json([
                'message' => 'Transaksi berhasil dikonfirmasi dan ada Stok bahan baku tidak mencukupi',
                'data' => $transaksi,
                'bahan_baku' => $bahan_baku_kurang
            ], 200);
        } else {
            return response()->json([
                'message' => 'Transaksi berhasil dikonfirmasi',
                'data' => $transaksi,
            ], 200);
        }
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

        if ($transaksi->status !== 'Menunggu Konfirmasi Pesanan' && $transaksi->status !== 'Menunggu Pembayaran') {
            return response()->json([
                'message' => 'Transaksi tidak dapat Ditolak'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $transaksiStatusBefore = $transaksi->status;
            $transaksi->status = 'Ditolak';
            $transaksi->tanggal_ambil = null;
            $transaksi->save();

            $detailTransaksi = DetailTransaksi::where('no_nota', $request->no_nota)->get();

            foreach ($detailTransaksi as $detail) {
                if ($detail->id_produk) {
                    $produk = Produk::find($detail->id_produk);
                    if ($produk->status === 'READY' || $detail->status === "READY") {
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

            if ($transaksi->penggunaan_poin > 0) {
                $user->poin += $transaksi->poin_sebelum_penambahan;
            }

            if ($transaksiStatusBefore === 'Menunggu Konfirmasi Pesanan') {
                $user->saldo += $transaksi->total + $transaksi->tip;
            }

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
            'message' => 'Transaksi berhasil Ditolak',
            'data' => $transaksi,
        ], 200);
    }

    public function konfirmasiPemrosesanMO(Request $request)
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

        $transaksi = Transaksi::with('detail_transaksi')->where('no_nota', $request->no_nota)->first();

        if ($transaksi->status !== 'Pesanan Diterima') {
            return response()->json([
                "message" => "Transaksi tidak dapat diubah",
            ], 400);
        }

        DB::beginTransaction();

        try {
            DB::statement('SET SESSION sql_require_primary_key=0');
            $bahan_baku_kurang = DB::select('CALL p3l.get_bahan_baku_details(?)', [$transaksi->no_nota]);

            if (count($bahan_baku_kurang) > 0) {
                return response()->json([
                    'message' => 'Transaksi tidak berhasil diproses karena stok bahan baku tidak mencukupi',
                    'data' => $transaksi,
                    'bahan_baku' => $bahan_baku_kurang
                ], 200);
            }

            $transaksi->status = 'Sedang Diproses';
            $histori = DB::select('CALL p3l.update_bahan_baku_dan_tambah_histori(?, ?)', [$transaksi->no_nota, Carbon::now()]);

            $transaksi->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Gagal mengkonfirmasi transaksi",
                "error" => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            "message" => "Transaksi berhasil dikonfirmasi",
            "data" => $transaksi,
            'histori' => $histori,
        ], 200);
    }

    public function updateStatusAdminKirimPickUp(Request $request)
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

        $transaksi = Transaksi::with('detail_transaksi')->where('no_nota', $request->no_nota)->first();

        if ($transaksi->status !== 'Sedang Diproses') {
            return response()->json([
                "message" => "Transaksi tidak dapat diubah",
            ], 400);
        }

        DB::beginTransaction();

        try {
            $detail_transaksi = $transaksi->detail_transaksi;

            foreach ($detail_transaksi as $detail) {
                if ($detail->id_produk) {
                    $produk = Produk::find($detail->id_produk);
                    if ($produk->ukuran === '1/2' && $detail->jumlah === 1 && $detail->status == null) {
                        $produk->stok += 1;
                        $produk->save();
                    }
                } else if ($detail->id_hampers) {
                    $dt = DetailHampers::where('id_hampers', $detail->id_hampers)->get();
                    foreach ($dt as $item) {
                        if ($item->id_produk === null) {
                            continue;
                        }

                        $produk = Produk::find($item->id_produk);
                        if ($produk->ukuran === '1/2' && ($detail->jumlah * $item->jumlah) === 1 && $detail->status == null) {
                            $produk->stok += 1;
                            $produk->save();
                        }
                    }
                }
            }

            $fcm_token = User::where('id_user', $transaksi->id_user)->first()->fcm_token;

            if ($transaksi->tipe_delivery === 'Ambil') {
                $transaksi->status = 'Siap Pick Up';
                if ($fcm_token) {
                    $notification =
                        (new FunctionHelper())
                            ->bulkSend('Pesananmu sudah dapat diambil', 'Mohon untuk dapat mengambil pesananmu secepatnya', $fcm_token);
                }

            } else if ($transaksi->tipe_delivery === 'Kurir') {
                $transaksi->status = 'Sedang Diantar Kurir';
                if ($fcm_token) {
                    $notification =
                        (new FunctionHelper())
                            ->bulkSend('Pesananmu sedang dikirim kurir', 'Mohon untuk standby pada alamat yang anda cantumkan', $fcm_token);
                }
            } else {
                $transaksi->status = 'Sedang Diantar Ojol';
                if ($fcm_token) {
                    $notification =
                        (new FunctionHelper())
                            ->bulkSend('Pesananmu sedang dikirim ojol', 'Mohon untuk standby pada alamat yang anda cantumkan', $fcm_token);
                }
            }

            $transaksi->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Gagal mengkonfirmasi transaksi",
                "error" => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            "message" => "Transaksi berhasil dikonfirmasi",
            "data" => $transaksi,
            "notification" => $notification,
        ], 200);
    }

    public function updateStatusSelesai(Request $request)
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

        DB::beginTransaction();

        try {
            $transaksi->status = 'Selesai';

            if ($transaksi->tipe_delivery === 'Ambil') {
                $transaksi->tanggal_ambil = Carbon::now();
            }

            $transaksi->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Gagal mengkonfirmasi transaksi",
                "error" => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            "message" => "Transaksi berhasil dikonfirmasi",
            "data" => $transaksi,
        ], 200);
    }

    public function updateStatusSelesaiSelf(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 404);
        }

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

        if ($transaksi->id_user !== $user->id_user) {
            return response()->json([
                'message' => 'Transaksi tidak ditemukan, atau bukan transaksi anda',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $transaksi->status = 'Selesai';

            if ($transaksi->tipe_delivery === 'Ambil') {
                $transaksi->tanggal_ambil = Carbon::now();
            }

            $transaksi->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Gagal mengkonfirmasi transaksi",
                "error" => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            "message" => "Transaksi berhasil dikonfirmasi",
            "data" => $transaksi,
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
