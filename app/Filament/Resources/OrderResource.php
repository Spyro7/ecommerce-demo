<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Order Information')->schema([
                        Select::make('user_id')
                            ->label('Customer')
                            ->placeholder('Select a Customer')
                            ->relationship('users', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('payment_method')
                            ->searchable()
                            ->options([
                                'cod' => 'Cash on Delivery',
                                'stripe' => 'Stripe',
                            ])
                            ->required(),
                        Select::make('payment_status')
                            ->searchable()
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                        ToggleButtons::make('status')
                            ->inline()
                            ->default('new')
                            ->options([
                                'new' => 'New',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->colors([
                                'new' => 'success',
                                'processing' => 'warning',
                                'shipped' => 'info',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            ])
                            ->icons([
                                'processing' => 'heroicon-m-arrow-path',
                                'shipped' => 'heroicon-m-truck',
                                'delivered' => 'heroicon-m-check-badge',
                                'cancelled' => 'heroicon-m-x-circle',
                            ]),
                        Select::make('currency')
                            ->options([
                                'usd' => 'USD',
                                'eur' => 'EUR',
                                'sum' => 'SUM',
                            ])
                            ->default('usd')
                            ->required(),
                        Select::make('shipping_method')
                            ->options([
                                'fedex' => 'Fedex',
                                'ups' => 'UPS',
                                'dhl' => 'DHL',
                                'yandex' => 'YANDEX',
                            ]),
                        Textarea::make('note')
                            ->columnSpanFull()
                    ])->columns(2),

                    Section::make('Order Items')->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(5)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Set $set) => $set('price', Product::find($state)->price ?? 0))
                                    ->afterStateUpdated(fn($state, Set $set) => $set('total_price', Product::find($state)->price ?? 0)),
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->required()
                                    ->default(1)
                                    ->step(1)
                                    ->minValue(1)
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Set $set, Get $get) => $set('total_price', $state * $get('price')))
                                    ->columnSpan(1),
                                TextInput::make('price')
                                    ->label('Price')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(3),
                                TextInput::make('total_price')
                                    ->label('Total Price')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->columnSpan(3),
                            ])->columns(12),
                        Placeholder::make('grand_total_placeholder')
                            ->label('Grand Total')
                            ->content(function (Get $get, Set $set) {
                                $total = 0;
                                if (!$repeaters = $get('items')) {
                                    return $total;
                                }

                                foreach ($repeaters as $key => $repeater) {
                                    $total += $get("items.{$key}.total_price");
                                }
                                $set('grand_total', $total);
                                return Number::currency($total, $get('currency'));
                            }),
                        Hidden::make('grand_total')
                            ->default(0),
                    ])

                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->numeric()
                    ->sortable()
                    ->money('USD'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => match($state) {
                        'cod' => 'Cash on Delivery',
                        'stripe' => 'Stripe',
                        default => $state,
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('shipping_method')
                    ->label('Shipping')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('H:i:s d.m.y')
                    ->sortable()
                    ->toggledHiddenByDefault(true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('H:i:s d.m.y')
                    ->sortable()
                    ->toggledHiddenByDefault(true),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AddressRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationSort(): ?int
    {
        return 4; // Birinchi navbatda
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
