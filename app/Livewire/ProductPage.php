<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title("Product Page")]
class ProductPage extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Url]
    public array $selected_categories = [];

    #[Url]
    public array $selected_brands = [];
    #[Url]
    public $featured;
    #[Url]
    public $on_sale;
    #[Url]
    public $price_range = 5000;
    #[Url]
    public $sort = 'latest';

    public function addToCart($product_id)
    {
        $total_count = CartManagement::addItemToCart($product_id);

        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);

        $this->alert('success', 'Product added to cart successfully!', [
            'position' => 'top',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function render()
    {
        $productQuery = Product::query()->where('is_active', 1);

        if (!empty($this->selected_categories)) {
            $productQuery->whereIn('category_id', $this->selected_categories);
        }
        if (!empty($this->selected_brands)) {
            $productQuery->whereIn('brand_id', $this->selected_brands);
        }
        if ($this->featured) {
            $productQuery->where('is_featured', 1);
        }
        if ($this->on_sale) {
            $productQuery->where('on_sale', 1);
        }
        if ($this->price_range) {
            $productQuery->whereBetween('price', [0, $this->price_range]);
        }
        if ($this->sort == 'latest') {
            $productQuery->latest();
        }
        if ($this->sort == 'highest_price') {
            $productQuery->orderBy('price', 'desc');
        }
        if ($this->sort == 'lowest_price') {
            $productQuery->orderBy('price', 'asc');
        }


        return view('livewire.product-page', [
            'products' => $productQuery->paginate(9),
            'categories' => Category::where('is_active', 1)->get(['id', 'name', 'slug'])->sortBy('name'),
            'brands' => Brand::where('is_active', 1)->get(['id', 'name', 'slug']),
        ]);
    }

}