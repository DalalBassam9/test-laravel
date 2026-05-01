<?php

namespace App\Filament\AdminPanel\Resources\Orders\Pages;

use App\Filament\AdminPanel\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

// ✅ هذي الصح لـ Filament v4
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Forms\Components\Select;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateStatus')
                ->label('تغيير الحالة')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'pending'     => 'قيد الانتظار',
                            'placed'      => 'تم الطلب',
                            'confirmed'   => 'تم التأكيد',
                            'shipped'     => 'تم الشحن',
                            'forDelivery' => 'جاهز للتوصيل',
                            'delivered'   => 'تم التوصيل',
                            'completed'   => 'مكتمل',
                            'cancelled'   => 'ملغي',
                        ])
                        ->default(fn () => $this->record->status)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update(['status' => $data['status']]);
                    \Filament\Notifications\Notification::make()
                        ->title('تم تحديث الحالة بنجاح')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([

            // معلومات الطلب
            Section::make('معلومات الطلب')
                ->schema([
                    TextEntry::make('id')
                        ->label('رقم الطلب'),
                    TextEntry::make('status')
                        ->label('الحالة')
                        ->formatStateUsing(fn ($state) => match($state) {
                            'pending'     => 'قيد الانتظار',
                            'placed'      => 'تم الطلب',
                            'confirmed'   => 'تم التأكيد',
                            'shipped'     => 'تم الشحن',
                            'forDelivery' => 'جاهز للتوصيل',
                            'delivered'   => 'تم التوصيل',
                            'completed'   => 'مكتمل',
                            'cancelled'   => 'ملغي',
                            default       => $state,
                        })
                        ->badge()
                        ->color(fn ($state) => match($state) {
                            'pending'     => 'warning',
                            'confirmed'   => 'info',
                            'shipped'     => 'primary',
                            'delivered'   => 'success',
                            'completed'   => 'success',
                            'cancelled'   => 'danger',
                            default       => 'gray',
                        }),
                    TextEntry::make('total_price')
                        ->label('المجموع مع التوصيل')
                        ->suffix(' د.ع'),
                    TextEntry::make('delivery_fee')
                        ->label('رسوم التوصيل')
                        ->suffix(' د.ع'),
                    TextEntry::make('payment_method')
                        ->label('طريقة الدفع'),
                    TextEntry::make('created_at')
                        ->label('تاريخ الإنشاء')
                        ->dateTime(),
                    TextEntry::make('notes')
                        ->label('ملاحظات')
                        ->placeholder('لا توجد ملاحظات'),
                ])->columns(2),

            // العنوان
            Section::make('العنوان')
                ->schema([
                    TextEntry::make('address.name')
                        ->label('اسم المستلم'),
                    TextEntry::make('address.city')
                        ->label('المدينة'),
                    TextEntry::make('address.phone')
                        ->label('الهاتف'),
                    TextEntry::make('address.address')
                        ->label('العنوان'),
                ])->columns(2),

            // المنتجات
            Section::make('المنتجات')
                ->schema([
                    RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            ImageEntry::make('product.images.0.url')
                                ->label('الصورة')
                                ->height(80)
                                ->width(80),
                            TextEntry::make('product.name')
                                ->label('المنتج'),
                            TextEntry::make('quantity')
                                ->label('الكمية'),
                            TextEntry::make('price')
                                ->label('السعر')
                                ->suffix(' د.ع'),
                   
                        ])->columns(5),
                ]),
        ]);
    }
}