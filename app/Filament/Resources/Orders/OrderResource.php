<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('rider_id')
                    ->relationship('rider', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('order_number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('customer_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('customer_phone')
                    ->required()
                    ->maxLength(30),
                TextInput::make('customer_email')
                    ->email()
                    ->maxLength(255),
                Textarea::make('delivery_address')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('delivery_fee')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Select::make('payment_method')
                    ->options([
                        'cod' => 'Cash on delivery',
                    ])
                    ->default('cod')
                    ->required(),
                Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->default('pending')
                    ->required(),
                Select::make('order_status')
                    ->options(Order::STATUSES)
                    ->default('pending')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('restaurant.name')
                    ->toggleable(),
                TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge(),
                TextColumn::make('order_status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('order_status')
                    ->options(Order::STATUSES),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOrders::route('/'),
        ];
    }
}
