<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class OrderStats extends BaseWidget
{

    protected static string $resource = OrderResource::class;

    protected int | string | array $columnSpan = 'full';
    protected function getStats(): array
    {
        return [
            Stat::make('All Orders', Order::all()->count()),
            Stat::make('New Orders', Order::query()->where('status', 'new')->count()),
            Stat::make('Processing Orders', Order::query()->where('status', 'processing')->count()),
            Stat::make('Shipped Orders', Order::query()->where('status', 'shipped')->count()),
            Stat::make('Delivered Orders', Order::query()->where('status', 'delivered')->count()),
            Stat::make('Cancelled Orders', Order::query()->where('status', 'cancelled')->count()),
            Stat::make('Average Price', Number::currency(Order::query()->whereNot('status', 'cancelled')->avg('grand_total') ?? 0, 'USD')),
            Stat::make('Total Price', Number::currency(Order::query()->whereNot('status', 'cancelled')->sum('grand_total') ?? 0, 'USD')),
        ];
    }
    protected function getColumns(): int
    {
        return 4;
    }
}
