<?php

namespace App\Http\Controllers\API\Procedure;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProcedureController extends Controller
{
    public function getNotaPemesanan(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'no_nota' => 'required|exists:transaksi,no_nota',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        try {
            $nota = DB::select("CALL p3l.get_nota_pemesanan(?);", [$request['no_nota']]);
            $barang = DB::select("CALL p3l.get_produk_for_nota(?);", [$request['no_nota']]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $result = [
            'no_nota' => $nota[0]->data_pemesanan,
            'nama' => $nota[4]->data_pemesanan,
            'email' => $nota[5]->data_pemesanan,
            'no_telp' => $nota[16]->data_pemesanan,
            'lokasi' => $nota[6]->data_pemesanan,
            'keterangan' => $nota[7]->data_pemesanan,
            'tanggal_pesan' => $nota[1]->data_pemesanan,
            'tanggal_lunas' => $nota[2]->data_pemesanan,
            'tanggal_ambil' => $nota[3]->data_pemesanan,
            'tipe_delivery' => $nota[8]->data_pemesanan,
            'penggunaan_poin' => (int) $nota[9]->data_pemesanan,
            'total' => (double) $nota[10]->data_pemesanan,
            'radius' => (int) $nota[11]->data_pemesanan,
            'ongkir' => (int) $nota[12]->data_pemesanan,
            'status' => $nota[15]->data_pemesanan,
            'penambahan_poin' => (int) $nota[13]->data_pemesanan,
            'poin_user_setelah_penambahan' => (int) $nota[14]->data_pemesanan,
            'produk' => []
        ];

        foreach ($barang as $item) {
            $result['produk'][] = [
                'nama_produk' => $item->nama,
                'ukuran' => $item->ukuran,
                'harga_saat_beli' => $item->harga_saat_beli,
                'jumlah' => $item->jumlah,
                'subtotal' => $item->total_harga,
                'id_kategori' => $item->id_kategori,
            ];
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }

    public function getNotaPemesananSelf(Request $request)
    {
        $user = Auth::user();

        $validate = Validator::make($request->all(), [
            'no_nota' => 'required|exists:transaksi,no_nota',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 400);
        }

        try {
            $nota = DB::select("CALL p3l.get_nota_pemesanan(?);", [$request['no_nota']]);

            if ($user->id_user != $nota[17]->data_pemesanan) {
                return response()->json([
                    'message' => 'Unauthorized Bukan Pemesan Anda',
                ], 401);
            }

            $barang = DB::select("CALL p3l.get_produk_for_nota(?);", [$request['no_nota']]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $result = [
            'no_nota' => $nota[0]->data_pemesanan,
            'nama' => $nota[4]->data_pemesanan,
            'email' => $nota[5]->data_pemesanan,
            'no_telp' => $nota[16]->data_pemesanan,
            'lokasi' => $nota[6]->data_pemesanan,
            'keterangan' => $nota[7]->data_pemesanan,
            'tanggal_pesan' => $nota[1]->data_pemesanan,
            'tanggal_lunas' => $nota[2]->data_pemesanan,
            'tanggal_ambil' => $nota[3]->data_pemesanan,
            'tipe_delivery' => $nota[8]->data_pemesanan,
            'penggunaan_poin' => (int) $nota[9]->data_pemesanan,
            'total' => (double) $nota[10]->data_pemesanan,
            'radius' => (int) $nota[11]->data_pemesanan,
            'ongkir' => (int) $nota[12]->data_pemesanan,
            'status' => $nota[15]->data_pemesanan,
            'penambahan_poin' => (int) $nota[13]->data_pemesanan,
            'poin_user_setelah_penambahan' => (int) $nota[14]->data_pemesanan,
            'produk' => []
        ];

        foreach ($barang as $item) {
            $result['produk'][] = [
                'nama_produk' => $item->nama,
                'ukuran' => $item->ukuran,
                'harga_saat_beli' => $item->harga_saat_beli,
                'jumlah' => $item->jumlah,
                'subtotal' => $item->total_harga,
                'id_kategori' => $item->id_kategori,
            ];
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }

    public function getLaporanBulananKeseluruhan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun' => 'required|numeric|between:2000,2100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $laporan = DB::select("CALL p3l.get_laporan_bulanan_keseluruhan(?);", [$request['tahun']]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $result = [];
        $total = 0;

        foreach ($laporan as $item) {
            if ($item->bulan == "Total") {
                $total = $item->total_pendapatan;
                continue;
            }

            $result[] = [
                'bulan' => $item->bulan,
                'total_transaksi' => (int) $item->total_transaksi,
                'total_pendapatan' => (double) $item->total_pendapatan,
            ];
        }

        return response()->json([
            'tanggal_cetak' => date('Y-m-d'),
            'data' => $result,
            'total_pendapatan_keseluruhan' => $total,
        ], 200);
    }

    public function getLaporanBulananPerProduk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulan' => 'required|numeric|between:1,12',
            'tahun' => 'required|numeric|between:2000,2100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $laporan = DB::select("CALL p3l.get_laporan_bulanan_per_produk(?, ?);", [$request['bulan'], $request['tahun']]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $result = [];
        $total = 0;

        foreach ($laporan as $item) {
            if ($item->nama == "Total") {
                $total = $item->total_harga;
                continue;
            }

            $result[] = [
                'nama_produk' => $item->nama,
                'ukuran' => $item->ukuran,
                'harga' => (int) $item->harga_saat_beli,
                'total_harga' => (int) $item->total_harga,
            ];
        }

        return response()->json([
            'tanggal_cetak' => date('Y-m-d'),
            'data' => $result,
            'total_keseluruhan' => $total,
        ], 200);
    }

    public function getLaporanStokBahanBaku()
    {
        try {
            $laporan = DB::select("CALL p3l.get_bahan_baku();");
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $result = [];

        foreach ($laporan as $item) {
            $result[] = [
                'nama_bahan_baku' => $item->nama_bahan_baku,
                'satuan' => $item->satuan,
                'stok' => (int) $item->stok,
            ];
        }

        return response()->json([
            'tanggal_cetak' => date('Y-m-d'),
            'data' => $result,
        ], 200);
    }

    public function getLaporanStokBahanBakuPeriode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $laporan = DB::select("CALL p3l.get_laporan_penggunaan_periode(?, ?);", [$request['tanggal_awal'], $request['tanggal_akhir']]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $result = [];

        foreach ($laporan as $item) {
            $result[] = [
                'nama_bahan_baku' => $item->nama_bahan_baku,
                'satuan' => $item->satuan,
                'digunakan' => (int) $item->digunakan,
            ];
        }

        return response()->json([
            'tanggal_cetak' => date('Y-m-d'),
            'data' => $result,
        ], 200);
    }

    public function getLaporanPemasukanDanPengeluaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulan' => 'required|numeric|between:1,12',
            'tahun' => 'required|numeric|between:2000,2100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $laporan = DB::select("CALL p3l.get_pemasukan_dan_pengeluaran(?, ?);", [$request['bulan'], $request['tahun']]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $pemasukanData = [];
        $pengeluaranData = [];

        $pemasukan = 0;
        $pengeluaran = 0;

        foreach ($laporan as $item) {
            if ($item->nama == "Total") {
                $pemasukan = $item->pemasukan;
                $pengeluaran = $item->pengeluaran;
                continue;
            }

            if ($item->pemasukan == null) {
                $pengeluaranData[] = [
                    'nama' => $item->nama,
                    'jumlah' => (int) $item->pengeluaran,
                ];
            } else {
                $pemasukanData[] = [
                    'nama' => $item->nama,
                    'jumlah' => (int) $item->pemasukan,
                ];
            }
        }

        return response()->json([
            'tanggal_cetak' => date('Y-m-d'),
            'pemasukan' => $pemasukanData,
            'pengeluaran' => $pengeluaranData,
            'total_pemasukan' => $pemasukan,
            'total_pengeluaran' => $pengeluaran,
        ], 200);
    }

    public function getLaporanPresensiKaryawan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulan' => 'required|numeric|between:1,12',
            'tahun' => 'required|numeric|between:2000,2100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $laporan = DB::select("CALL p3l.get_laporan_presensi_karyawan(?, ?);", [$request['bulan'], $request['tahun']]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $result = [];

        $total = 0;

        foreach ($laporan as $item) {
            if ($item->nama == "Total") {
                $total = $item->total;
                continue;
            }

            $result[] = [
                'nama' => $item->nama,
                'jumlah_hadir' => (int) $item->jumlah_hadir,
                'jumlah_bolos' => (int) $item->jumlah_bolos,
                'honor_harian' => (int) $item->honor_harian,
                'bonus' => (int) $item->bonus,
                'total' => (int) $item->total,
            ];
        }

        return response()->json([
            'tanggal_cetak' => date('Y-m-d'),
            'data' => $result,
            'total_gaji' => $total,
        ], 200);
    }

    public function getLaporanTransaksiPenitip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bulan' => 'required|numeric|between:1,12',
            'tahun' => 'required|numeric|between:2000,2100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $laporan = DB::select("CALL p3l.get_all_laporan_transaksi_penitip2(?, ?);", [$request['bulan'], $request['tahun']]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        $result = [];
        $penitipData = [];

        foreach ($laporan as $item) {
            if (!isset($penitipData[$item->id_penitip])) {
                $penitipData[$item->id_penitip] = [
                    'id_penitip' => $item->id_penitip,
                    'nama' => $item->nama,
                    'data' => [],
                    'total_pendapatan' => 0,
                ];
            }

            $penitipData[$item->id_penitip]['data'][] = [
                'nama_produk' => $item->nama_produk,
                'kuantitas' => (int) $item->kuantitas,
                'harga' => (int) $item->harga_saat_beli,
                'total' => (int) $item->total,
                'komisi' => (int) $item->komisi,
                'yang_diterima' => (int) $item->yang_diterima,
            ];

            $penitipData[$item->id_penitip]['total_pendapatan'] += (int) $item->yang_diterima;
        }

        $result = array_values($penitipData);

        return response()->json([
            'tanggal_cetak' => date('Y-m-d'),
            'data' => $result,
        ], 200);
    }

    public function getRekapProduk()
    {
        try {
            // $result = DB::select("CALL p3l.get_rekap_pesanan(?);", [date('Y-m-d', strtotime('+1 day'))]);
            $result = DB::select("CALL p3l.get_rekap_pesanan(?);", [('2024-05-24')]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }

    public function getRekapProdukPerluDibuat()
    {
        try {
            // $result = DB::select("CALL p3l.get_pesanan_perlu_dibuat(?);", [date('Y-m-d', strtotime('+1 day'))]);
            $result = DB::select("CALL p3l.get_pesanan_perlu_dibuat(?);", [('2024-05-24')]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }

    public function getRekapBahanBaku()
    {
        try {
            DB::statement('SET SESSION sql_require_primary_key=0');
            // $result = DB::select("CALL p3l.get_rekap_bahan_baku(?);", [date('Y-m-d', strtotime('+1 day'))]);
            $result = DB::select("CALL p3l.get_rekap_bahan_baku(?);", [('2024-05-24')]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }

    public function getRekapBahanBakuPerProduk()
    {
        try {
            DB::statement('SET SESSION sql_require_primary_key=0');
            // $result = DB::select("CALL p3l.get_rekap_bahan_baku_per_produk(?);", [date('Y-m-d', strtotime('+1 day'))]);
            $result = DB::select("CALL p3l.get_rekap_bahan_baku_per_produk(?);", [('2024-05-24')]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }

    public function getRekapNotaHarian()
    {
        try {
            $result = Transaksi::with('detail_transaksi.produk', 'detail_transaksi.hampers')
                ->whereDate('tanggal_ambil', '=', '2024-05-24')
                ->where('status', '=', 'Pesanan Diterima');
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'data' => $result,
        ], 200);
    }
}
