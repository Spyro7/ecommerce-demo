<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Product information')->schema([
                        TextInput::make('name')
                            ->columnSpanFull()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                            })
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->columnSpanFull()
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(),
                        MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('products'),
                    ])->columns(2),
                    Section::make('Product images')->schema([
                        FileUpload::make('images')
                            ->required()
                            ->multiple()
                            ->maxSize(1024)
                            ->directory('products')
                            ->maxFiles(6)
                            ->minFiles(1)
                            ->panelLayout('grid')
                            ->imageResizeMode('cover')
                            ->appendFiles()
                            ->reorderable(),
                    ]),
                ])->columnSpan(2),
                Group::make()->schema([
                    Section::make('Product details')->schema([
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$'),
                        TextInput::make('discount')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('%'),
                    ]),
                    Section::make('Associations')->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->placeholder('Select a category')
                            ->searchable()
                            ->preload()
                            ->relationship('category', 'name')
                            ->required(),
                        Select::make('brand_id')
                            ->label('Brand')
                            ->placeholder('Select a brand')
                            ->searchable()
                            ->preload()
                            ->relationship('brand', 'name')
                            ->required(),
                    ]),
                    Section::make('Status')->schema([
                        Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                        Toggle::make('in_stock')
                            ->default(true)
                            ->required(),
                        Toggle::make('is_featured')
                            ->required(),
                        Toggle::make('on_sale')
                            ->required(),
                    ])->columns(1)
                ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->wrap()
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable(),
//                Tables\Columns\TextColumn::make('slug')
//                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
//                Tables\Columns\TextColumn::make('discount')
//                    ->numeric()
//                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('in_stock')
                    ->boolean(),
                Tables\Columns\IconColumn::make('on_sale')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('H:i:s d.m.y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('H:i:s d.m.y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category.name')
                    ->relationship('category', 'name')
                    ->label('Category'),
                Tables\Filters\SelectFilter::make('brand.name')
                    ->relationship('brand', 'name')
                    ->label('Brand'),
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
            //
        ];
    }
    public static function getNavigationSort(): ?int
    {
        return 3; // Birinchi navbatda
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
