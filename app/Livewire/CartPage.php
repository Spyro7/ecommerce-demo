<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title("Cart Page")]
class CartPage extends Component
{
    public $cart_items = [];
    public $grand_total = 0;

    public function increaseQty($product_id)
    {
        $this->cart_items = CartManagement::incrementQuantityToCartItem($product_id);
        $this->grand_total = CartManagement::calculateTotalPrice($this->cart_items);
    }
    public function decreaseQty($product_id){
        $this->cart_items = CartManagement::decrementQuantityToCartItem($product_id);
        $this->grand_total = CartManagement::calculateTotalPrice($this->cart_items);
    }
    public function mount(){
        $this->cart_items = CartManagement::getCartItemsFromCookie();
        $this->grand_total = CartManagement::calculateTotalPrice($this->cart_items);
    }
    public function removeItem($product_id)
    {
        $this->cart_items = CartManagement::removeCartItem($product_id);
        $this->grand_total = CartManagement::calculateTotalPrice($this->cart_items);
        $this->dispatch('update-cart-count', total_count: count($this->cart_items))->to(Navbar::class);
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}
