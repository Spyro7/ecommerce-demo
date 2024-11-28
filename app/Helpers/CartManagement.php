<?php

namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Facades\Cookie;

class CartManagement
{
    static public function addItemToCart($product_id)
    {
        $cart_items = self::getCartItemsFromCookie();

        $existing_item_key = null;

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $existing_item_key = $key;
                break;
            }
        }

        if ($existing_item_key !== null) {
            $cart_items[$existing_item_key]['quantity']++;
            $cart_items[$existing_item_key]['total_price'] = $cart_items[$existing_item_key]['quantity'] * $cart_items[$existing_item_key]['price'];
        } else {
            $product = Product::where('id', $product_id)->first(['id', 'name', 'price', 'images']);
            if ($product !== null) {
                $cart_items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->images[0] ?? null,
                    'quantity' => 1,
                    'total_price' => $product->price,
                ];
            }
        }

        self::addCartItemsToCookie($cart_items);
        return count($cart_items);
    }

    static public function addItemToCartWithQty($product_id, $qty = 1)
    {
        $cart_items = self::getCartItemsFromCookie();

        $existing_item_key = null;

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $existing_item_key = $key;
                break;
            }
        }

        if ($existing_item_key !== null) {
            $cart_items[$existing_item_key]['quantity'] = $qty;
            $cart_items[$existing_item_key]['total_price'] = $cart_items[$existing_item_key]['quantity'] * $cart_items[$existing_item_key]['price'];
        } else {
            $product = Product::where('id', $product_id)->first(['id', 'name', 'price', 'images']);
            if ($product !== null) {
                $cart_items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->images[0] ?? null,
                    'quantity' => $qty,
                    'price' => $product->price,
                    'total_price' => $product->price,
                ];
            }
        }

        self::addCartItemsToCookie($cart_items);
        return count($cart_items);
    }
    static public function removeCartItem($product_id)
    {
        $cart_items = self::getCartItemsFromCookie();

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                unset($cart_items[$key]);
                break;
            }
        }

        self::addCartItemsToCookie($cart_items);
        return $cart_items;
    }

    static public function addCartItemsToCookie($cart_items)
    {
        Cookie::queue('cart_items', json_encode($cart_items), 60 * 24 * 30);
    }

    static public function clearCartItems()
    {
        Cookie::queue(Cookie::forget('cart_items'));
    }

    static public function getCartItemsFromCookie()
    {
        $cart_items = json_decode(Cookie::get('cart_items'), true);
        if (!$cart_items) {
            $cart_items = [];
        }
        return $cart_items;
    }

    static public function incrementQuantityToCartItem($product_id)
    {
        $cart_items = self::getCartItemsFromCookie();

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $cart_items[$key]['quantity']++;
                $cart_items[$key]['total_price'] = $cart_items[$key]['quantity'] * $cart_items[$key]['price'];
                break;
            }
        }

        self::addCartItemsToCookie($cart_items);
        return $cart_items;
    }

    static public function decrementQuantityToCartItem($product_id)
    {
        $cart_items = self::getCartItemsFromCookie();

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                if ($cart_items[$key]['quantity'] > 1) {
                    $cart_items[$key]['quantity']--;
                    $cart_items[$key]['total_price'] = $cart_items[$key]['quantity'] * $cart_items[$key]['price'];
                }
                break;
            }
        }

        self::addCartItemsToCookie($cart_items);
        return $cart_items;
    }

    static public function calculateTotalPrice($items)
    {
        return array_sum(array_column($items, 'total_price'));
    }
}
