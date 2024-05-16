<?php

namespace App\Http\Controllers\API\Data;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $data = Cart::where('id_user', '=', $user->id_user)
            ->with('produk.gambar', 'hampers.gambar')
            ->get();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data kosong',
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diterima',
            'data' => $data,
            'poin' => $user->poin,
        ], 200);
    }

    public function store(Request $request)
    {
        $rules = [
            'id_produk' => 'sometimes|exists:produk,id_produk',
            'id_hampers' => 'sometimes|exists:hampers,id_hampers',
            'jumlah' => 'required|integer|min:1',
            'status' => 'required|in:READY,PO',
            'po_date' => 'sometimes|date',
        ];

        $messages = [
            'id_produk.exists' => 'Produk tidak ditemukan',
            'id_hampers.exists' => 'Hampers tidak ditemukan',
            'po_date.date' => 'Tanggal PO harus berupa tanggal',
        ];

        $validate = Validator::make($request->all(), $rules, $messages);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first()
            ], 404);
        }

        if (!$request->id_produk && !$request->id_hampers) {
            return response()->json([
                'message' => 'id_produk atau id_hampers harus diisi'
            ], 404);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 404);
        }

        $dataCart = Cart::where('id_user', $user->id_user)->get();

        if ($dataCart->isNotEmpty()) {
            $dates = $dataCart->pluck('po_date');
            $status = $dataCart->pluck('status');

            if ($status->contains('READY') && $request->po_date && $dates->contains(null)) {
                $this->updateCartWhenReadyInserted($user->id_user, 'status', 'READY', $request->po_date);
                return $this->addToCart($user->id_user, $request->id_produk, $request->id_hampers, $request->jumlah, $request->po_date, $request->status);
            }

            if (!$dates->contains($request->po_date)) {
                return response()->json([
                    'message' => 'Tanggal PO harus sama'
                ], 404);
            }

            $isProductInCart = $request->id_produk && $dataCart->pluck('id_produk')->contains($request->id_produk);

            $isHampersInCart = $request->id_hampers && $dataCart->pluck('id_hampers')->contains($request->id_hampers);
            if ($isProductInCart) {
                return $this->updateCart($user->id_user, 'id_produk', $request->id_produk, $request->jumlah);
            }

            if ($isHampersInCart) {
                return $this->updateCart($user->id_user, 'id_hampers', $request->id_hampers, $request->jumlah);
            }
        }

        return $this->addToCart($user->id_user, $request->id_produk, $request->id_hampers, $request->jumlah, $request->po_date, $request->status);
    }
    private function updateCart($userId, $column, $value, $quantity)
    {
        try {
            DB::beginTransaction();
            $data = Cart::where('id_user', $userId)->where($column, $value)->first();
            $data->update(['jumlah' => $quantity]);
            DB::commit();

            return response()->json(['message' => 'Data berhasil diubah', 'data' => $data], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function updateCartWhenReadyInserted($userId, $column, $value, $date)
    {
        try {
            DB::beginTransaction();
            $data = Cart::where('id_user', $userId)->where($column, $value)->get();
            foreach ($data as $cart) {
                $cart->update(['po_date' => $date]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function addToCart($userId, $idProduk, $idHampers, $quantity, $poDate, $status)
    {
        try {
            DB::beginTransaction();
            $data = Cart::create([
                'id_user' => $userId,
                'id_produk' => $idProduk,
                'id_hampers' => $idHampers,
                'jumlah' => $quantity,
                'status' => $status,
                'po_date' => $poDate,
            ]);
            DB::commit();

            return response()->json(['message' => 'Data berhasil ditambahkan', 'data' => $data], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $validate = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 404);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $data = Cart::where('id_user', '=', $user->id_user)
            ->where('id_cart', '=', $id)
            ->first();

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        try {
            DB::beginTransaction();
            $data->update([
                'jumlah' => $request->jumlah,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
        return response()->json([
            'message' => 'Data berhasil diubah',
            'data' => $data,
        ], 200);
    }

    public function updateWhenLogout(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'cart' => 'required|array',
            'cart.*.id_cart' => 'required|integer|exists:cart,id_cart',
            'cart.*.jumlah' => 'required|integer|min:1',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], 404);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $data = Cart::where('id_user', '=', $user->id_user)
            ->whereIn('id_cart', array_column($request->cart, 'id_cart'))
            ->get();

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        try {
            DB::beginTransaction();
            foreach ($data as $key => $cart) {
                if ($cart->jumlah == $request->cart[$key]['jumlah']) {
                    continue;
                }

                $cart->update([
                    'jumlah' => $request->cart[$key]['jumlah'],
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
        return response()->json([
            'message' => 'Data berhasil diubah',
            'data' => $data,
        ], 200);
    }

    public function destroy(string $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $data = Cart::where('id_user', '=', $user->id_user)
            ->where('id_cart', '=', $id)
            ->first();

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
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dihapus',
        ], 200);
    }

    public function destroyAll()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 404);
        }

        $data = Cart::where('id_user', '=', $user->id_user)
            ->get();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($data as $cart) {
                $cart->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Data berhasil dihapus',
        ], 200);
    }
}