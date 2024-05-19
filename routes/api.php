<?php

use App\Http\Controllers\API\Data\AlamatController;
use App\Http\Controllers\API\Data\BahanBakuController;
use App\Http\Controllers\API\Data\CartController;
use App\Http\Controllers\API\Data\DetailHampersController;
use App\Http\Controllers\API\Data\GambarController;
use App\Http\Controllers\API\Data\HampersController;
use App\Http\Controllers\API\Data\KaryawanController;
use App\Http\Controllers\API\Data\PengeluaranLainController;
use App\Http\Controllers\API\Data\PenitipController;
use App\Http\Controllers\API\Data\PresensiController;
use App\Http\Controllers\API\Data\ResepController;
use App\Http\Controllers\API\Data\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\UserAuthController;
use App\Http\Controllers\API\Auth\ForgotPasswordAPIController;
use App\Http\Controllers\API\Auth\ResetPasswordAPIController;
use App\Http\Controllers\API\Data\ProdukController;
use App\Http\Controllers\API\Procedure\ProcedureController;
use App\Http\Controllers\API\Data\PengadaanBahanBakuController;
use App\Http\Controllers\API\Data\TransaksiController;

Route::controller(UserAuthController::class)
    ->group(function () {
        Route::post('/login', 'login')->name('login');
        Route::post('/register', 'register')->name('register');
        Route::post('/logout', 'logout')->name('logout')->middleware('auth:sanctum');
        Route::post('/verify/{verify_key}', 'verify')->name('verify');
        Route::post('/password/verify', 'verifyPassword')->name('verify-password');
    })->name('authentications');


Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
Route::get('/produk/{id}', [ProdukController::class, 'show'])->name('produk.show');
Route::get('/public/paginate/produk', [ProdukController::class, 'paginatePublic'])->name('produk.paginate');

Route::get('/hampers', [HampersController::class, 'index'])->name('hampers.index');
Route::get('/hampers/{id}', [HampersController::class, 'show'])->name('hampers.show');

Route::apiResource('transaksi', TransaksiController::class)->only(['store'])->middleware('auth:sanctum');
Route::post('/transaksi/count', [TransaksiController::class, 'countTransaksi'])->name('transaksi.count');
Route::post('/transaksi/hampers/count', [TransaksiController::class, 'countTransaksiWithHampers'])->name('transaksi.count-hampers');

// Self User - Digunakan untuk user yang sedang login
Route::get('/users/self', [UserController::class, 'showSelf'])->name('users.self')
    ->middleware('auth:sanctum');
Route::post('/users/self', [UserController::class, 'updateSelf'])->name('users.update-self')
    ->middleware('auth:sanctum');
Route::delete('/users/self/pfp', [UserController::class, 'destroyProfilePicSelf'])->name('users.destroy-self')
    ->middleware('auth:sanctum');
Route::get('/transaksi/self/history', [TransaksiController::class, 'indexHistorySelf'])->name('users.show-self')
    ->middleware('auth:sanctum');
Route::get('/paginate/transaksi/self/history', [TransaksiController::class, 'paginateHistorySelf'])->name('users.paginate-self')
    ->middleware('auth:sanctum');
Route::post('/transaksi/search/self', [TransaksiController::class, 'searchSelf'])->name('users.search-self')
    ->middleware('auth:sanctum');

Route::post('/get-nota/self', [ProcedureController::class, 'getNotaPemesananSelf'])->name('get-nota-self')
    ->middleware('auth:sanctum');

Route::get('/paginate/transaksi/history/{id_user}', [TransaksiController::class, 'paginateHistory'])->name('users.paginate-self')
    ->middleware(['auth:sanctum', 'ability:admin']);
Route::post('/transaksi/search/{id_user}', [TransaksiController::class, 'search'])->name('users.show-self')
    ->middleware(['auth:sanctum', 'ability:admin']);

Route::controller(CartController::class)
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource('cart', CartController::class);
        Route::put('/cart/logout', 'updateWhenLogout')->name('cart.update-logout');
        Route::delete('/all/cart', 'destroyAll')->name('cart.destroy-all');
    })->name('cart');

// Self Alamat - Digunakan untuk CRUDS alamat user yang sedang login
Route::controller(AlamatController::class)
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/alamat/self', 'showSelf')->name('alamat-self.show-self');
        Route::post('/alamat/self/search', 'searchSelf')->name('alamat-self.search');
        Route::get('/paginate/alamat/self', 'paginateSelf')->name('alamat-self.paginate');
        Route::post('/alamat/self', 'storeSelf')->name('alamat-self.store');
        Route::put('/alamat/self/{id}', 'updateSelf')->name('alamat-self.update');
        Route::delete('/alamat/self/{id}', 'destroySelf')->name('alamat-self.destroy');
    })->name('alamat-self');

Route::post('/users/self/password', [UserController::class, 'updateSelfPassword'])->name('users.update-self-password')
    ->middleware(['auth:sanctum', 'ability:mo,owner,admin']);

/*
    Post dari front-end
    api/password/email?email={email}
*/
Route::post('password/email', [ForgotPasswordAPIController::class, 'sendResetLinkEmail'])
    ->name('sent-reset-link-email');

/*
    Post dari front-end
    api/password/reset?token={token}&password={pass}&password_confirmation={pass_conf}
*/
Route::post('password/reset', [ResetPasswordAPIController::class, 'reset'])
    ->name('password-reset');

// ProcedureController routes
Route::middleware(['auth:sanctum', 'ability:mo,owner,admin'])
    ->controller(ProcedureController::class)
    ->group(function () {
        Route::post('/get-nota', 'getNotaPemesanan')->name('get-nota');
    })->name('laporan');

Route::middleware(['auth:sanctum', 'ability:mo,owner'])
    ->group(function () {
        // KaryawanController routes
        Route::controller(KaryawanController::class)->group(function () {
            Route::apiResource('karyawan', KaryawanController::class);
            Route::get('/paginate/karyawan', 'paginate')->name('karyawan.paginate');
            Route::get('/karyawan/search/{data}', 'search')->name('karyawan.search');
            Route::get('/trash/karyawan', 'indexOnlyTrashed')->name('karyawan.trash');
            Route::get('/karyawan/restore/{id}', 'restore')->name('karyawan.restore');
        })->name('karyawan');
    });

Route::get('/penitip', [PenitipController::class, 'index'])->name('penitip.index')
    ->middleware(['auth:sanctum', 'ability:mo,admin']);

Route::get('/bahan_baku', [BahanBakuController::class, 'index'])->name('bahan_baku.index')
    ->middleware(['auth:sanctum', 'ability:mo,admin']);

Route::middleware(['auth:sanctum', 'ability:mo'])
    ->group(function () {
        // PenitipController routes
        Route::controller(PenitipController::class)->group(function () {
            Route::apiResource('penitip', PenitipController::class, ['except' => ['index']]);
            Route::get('/paginate/penitip', 'paginate')->name('penitip.paginate');
            Route::get('/penitip/search/{data}', 'search')->name('penitip.search');
            Route::get('/trash/penitip', 'indexOnlyTrashed')->name('penitip.trash');
            Route::get('/penitip/restore/{id}', 'restore')->name('penitip.restore');
        });

        // PengeluaranLain routes
        Route::controller(PengeluaranLainController::class)->group(function () {
            Route::apiResource('pengeluaran_lain', PengeluaranLainController::class);
            Route::get('/paginate/pengeluaran_lain', 'paginate')->name('pengeluaran_lain.paginate');
            Route::get('/pengeluaran_lain/search/{data}', 'search')->name('pengeluaran_lain.search');
            Route::get('/pengeluaran_lain/filter/{month}/{year}', 'filter')->name('pengeluaran_lain.filter');
        });

        // PembelianBahanBaku routes
        Route::controller(PengadaanBahanBakuController::class)->group(function () {
            Route::apiResource('pembelian_bahan_baku', PengadaanBahanBakuController::class);
            Route::get('/paginate/pembelian_bahan_baku', 'paginate')->name('pembelian_bahan_baku.paginate');
            Route::get('/pembelian_bahan_baku/search/{data}', 'search')->name('pembelian_bahan_baku.search');
        });

        // Presensi routes
        Route::controller(PresensiController::class)->group(function () {
            Route::apiResource('presensi', PresensiController::class);
            Route::get('/presensi/date/{date}', 'indexByDate')->name('presensi.date');
            Route::get('/presensi/search/{data}/{date}', 'search')->name('presensi.search');
        });

        Route::controller(TransaksiController::class)->group(function () {
            Route::post('/konfirmasi/transaksi/pesanan', 'konfirmasiTransaksiMO')->name('transaksi.konfirmasi-transaksi-mo');
            Route::post('/tolak/transaksi/pesanan', 'batalTransaksi')->name('transaksi.tolak-transaksi');
        });
    });

Route::middleware(['auth:sanctum', 'ability:admin'])
    ->group(function () {
        // ResepController routes
        Route::controller(ResepController::class)->group(function () {
            Route::get('/paginate/resep', 'paginate')->name('resep.paginate');
            Route::get('/resep/search/{data}', 'search')->name('resep.search');
            Route::apiResource('resep', ResepController::class, ['except' => ['destroy', 'update']]);
            Route::put('/resep', 'update')->name('resep.update');
            Route::delete('/resep/{id_resep}', 'destroy')->name('resep.destroy');
            Route::delete('/resep/all/{id_produk}', 'destroyAll')->name('resep.destroy-all');
        });

        // GambarController routes
        Route::controller(GambarController::class)->group(function () {
            Route::apiResource('gambar', GambarController::class, ['except' => ['update']]);
            Route::put('/gambar', 'update')->name('gambar.update');
            Route::get('/gambar/produk/{id}', 'showProduk')->name('gambar.produk');
            Route::get('/gambar/hampers/{id}', 'showHampers')->name('gambar.hampers');
        });

        // ProdukController routes
        Route::controller(ProdukController::class)->group(function () {
            Route::apiResource('produk', ProdukController::class, ['except' => ['index', 'show']]);
            Route::get('/paginate/produk', 'paginate')->name('produk.paginate');
            Route::post('/produk/search', 'search')->name('produk.search');
            Route::get('/trash/produk', 'indexOnlyTrashed')->name('produk.trash');
            Route::get('/produk/restore/{id}', 'restore')->name('produk.restore');
        });

        // BahanBakuController routes
        Route::controller(BahanBakuController::class)->group(function () {
            Route::apiResource('bahan_baku', BahanBakuController::class, ['except' => ['index']]);
            Route::get('/trash/bahan_baku', 'indexOnlyTrashed')->name('bahan_baku.trash');
            Route::get('/bahan_baku/restore/{id}', 'restore')->name('bahan_baku.restore');
            Route::get('/paginate/bahan_baku', 'paginate')->name('bahan_baku.paginate');
            Route::get('/bahan_baku/search/{data}', 'search')->name('bahan_baku.search');
        });

        // HampersController routes
        Route::controller(HampersController::class)->group(function () {
            Route::apiResource('hampers', HampersController::class, ['except' => ['index', 'show']]);
            Route::get('/paginate/hampers', 'paginate')->name('hampers.paginate');
            Route::get('/hampers/search/{data}', 'search')->name('hampers.search');
            Route::get('/trash/hampers', 'indexOnlyTrashed')->name('hampers.trash');
            Route::get('/hampers/restore/{id}', 'restore')->name('hampers.restore');
        });

        // DetailHampersController routes
        Route::controller(DetailHampersController::class)->group(function () {
            Route::apiResource('detail_hampers', DetailHampersController::class);
            Route::delete('/detail_hampers/all/{id_hampers}', 'destroyAll')->name('detail_hampers.destroy-all');
        });

        // UserController routes
        Route::controller(UserController::class)->group(function () {
            Route::get('/users', 'index')->name('users.index');
            Route::get('/users/{id}', 'show')->name('users.show');
            Route::put('/users/{id}', 'update')->name('users.update');
            Route::delete('/users/{id}', 'destroy')->name('users.delete');
            Route::get('/paginate/users', 'paginate')->name('users.paginate');
            Route::get('/users/search/{data}', 'search')->name('users.search');
        });

        // AlamatController routes - Admin (berbeda dari user only)
        Route::controller(AlamatController::class)->group(function () {
            Route::apiResource('alamat', AlamatController::class);
            Route::get('/paginate/alamat', 'paginate')->name('alamat.paginate');
            Route::post('/alamat/search', 'search')->name('alamat.search');
        });

        Route::controller(TransaksiController::class)->group(function () {
            Route::post('/konfirmasi/transaksi/ongkir', 'konfirmasiAddJarakAdmin')->name('transaksi.konfirmasi-ongkir');
            Route::post('/konfirmasi/transaksi/pembayaran', 'konfirmasiTransaksiAdmin')->name('transaksi.konfirmasi-transaksi-admin');
        });
    });

Route::get('/cron', function () {
    $providedToken = request()->header('cron-secret');
    $expectedToken = env('CRON_SECRET');

    if ($providedToken !== $expectedToken) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    Artisan::call('add-presensi');
    $output = Artisan::output();
    Mail::raw($output, function ($message) {
        $message->to('nicoherlim2003@gmail.com')->subject('Presensi Karyawan');
    });
    return $output;
});