<?php

namespace App\Filament\AdminPanel\Resources\Orders;

use App\Filament\AdminPanel\Resources\Orders\Pages\CreateOrder;
use App\Filament\AdminPanel\Resources\Orders\Pages\EditOrder;
use App\Filament\AdminPanel\Resources\Orders\Pages\ListOrders;
use App\Filament\AdminPanel\Resources\Orders\Pages\ViewOrder;
use App\Filament\AdminPanel\Resources\Orders\Schemas\OrderForm;
use App\Filament\AdminPanel\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\AdminPanel\Resources\Orders\Tables\OrdersTable;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $recordTitleAttribute = 'Order';
    protected static ?string $navigationLabel = 'الطلبات';
    protected static ?string $modelLabel = 'طلب';
    protected static ?string $pluralModelLabel = 'الطلبات';

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم')
                    ->sortable(),

                SelectColumn::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'     => 'pending',
                        'placed'      => 'placed',
                        'confirmed'   => 'confirmed',
                        'shipped'     => 'shipped',
                        'cancelled'   => 'cancelled',
                        'forDelivery' => 'forDelivery',
                        'delivered'   => 'delivered',
                        'completed'   => 'completed',
                    ])
                    ->sortable(),

                TextColumn::make('total_price')
                    ->label('المجموع كامل')
                    ->suffix(' د.ع')
                    ->sortable(),

                TextColumn::make('address.city')
                    ->label('المدينة'),

                TextColumn::make('address.phone')
                    ->label('الهاتف'),
            ])
            ->actions([
                Action::make('viewProducts')
                    ->label('عرض المنتجات')
                    ->color('info')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (Order $record) => "منتجات الطلب #{$record->id}")
                    ->modalContent(fn (Order $record) => view(
                        'filament.modals.order-products',
                        ['order' => $record->load('items.product')]
                    ))
                    ->modalSubmitAction(false),

                // ✅ صح الآن
                ViewAction::make()
                    ->label('عرض الطلب')
                    ->color('danger'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),   // ✅ مباشرة بدون Pages::
            'view'  => ViewOrder::route('/{record}'),
        ];
    }
}