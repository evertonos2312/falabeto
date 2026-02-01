<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource\RelationManagers\RedemptionsRelationManager;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Cupons';

    protected static ?string $navigationGroup = 'Comercial';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('Cupom')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Código')
                        ->required()
                        ->maxLength(64)
                        ->regex('/^[A-Z0-9_]+$/')
                        ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper($state) : null)
                        ->disabled(fn (?Coupon $record) => $record !== null),
                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'percent' => 'Percentual',
                            'fixed' => 'Valor fixo',
                        ])
                        ->required()
                        ->reactive(),
                    Forms\Components\TextInput::make('value_int')
                        ->label('Valor')
                        ->numeric()
                        ->required()
                        ->helperText(fn (Get $get) => $get('type') === 'percent'
                            ? 'Percentual (1-100)'
                            : 'Valor em centavos (ex: 1000 = R$ 10,00)')
                        ->rules(function (Get $get) {
                            if ($get('type') === 'percent') {
                                return ['integer', 'min:1', 'max:100'];
                            }

                            return ['integer', 'min:100'];
                        }),
                    Forms\Components\Select::make('duration')
                        ->label('Duração')
                        ->options([
                            'once' => 'Uma vez',
                            'repeating' => 'Recorrente',
                            'forever' => 'Para sempre',
                        ])
                        ->default('once')
                        ->required()
                        ->reactive(),
                    Forms\Components\TextInput::make('duration_months')
                        ->label('Meses de duração')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(24)
                        ->visible(fn (Get $get) => $get('duration') === 'repeating')
                        ->required(fn (Get $get) => $get('duration') === 'repeating'),
                    Forms\Components\TextInput::make('max_redemptions')
                        ->label('Máx. utilizações')
                        ->numeric()
                        ->minValue(1)
                        ->rules(function (?Coupon $record) {
                            if (! $record) {
                                return [];
                            }
                            return ['gte:' . (int) $record->redeemed_count];
                        }),
                    Forms\Components\DateTimePicker::make('valid_from')
                        ->label('Válido a partir de'),
                    Forms\Components\DateTimePicker::make('valid_until')
                        ->label('Válido até')
                        ->rules([
                            function (Get $get) {
                                return function (string $attribute, $value, $fail) use ($get) {
                                    $from = $get('valid_from');
                                    if ($from && $value) {
                                        $fromDate = \Carbon\Carbon::parse($from);
                                        $toDate = \Carbon\Carbon::parse($value);
                                        if ($toDate->lt($fromDate)) {
                                            $fail('A data final deve ser maior ou igual à data inicial.');
                                        }
                                    }
                                };
                            },
                        ]),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Ativo')
                        ->default(true),
                    Forms\Components\MultiSelect::make('allowed_plan_codes')
                        ->label('Planos permitidos')
                        ->options(fn () => Plan::query()->pluck('name', 'code')->all())
                        ->helperText('Se vazio, vale para todos os planos.')
                        ->searchable(),
                    Forms\Components\Toggle::make('first_purchase_only')
                        ->label('Somente primeira compra'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => $state === 'percent' ? '%' : 'R$')
                    ->sortable(),
                Tables\Columns\TextColumn::make('value_int')
                    ->label('Valor')
                    ->formatStateUsing(function (Coupon $record) {
                        if ($record->type === 'percent') {
                            return $record->value_int . '%';
                        }
                        return 'R$ ' . number_format($record->value_int / 100, 2, ',', '.');
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('validity')
                    ->label('Validade')
                    ->getStateUsing(function (Coupon $record) {
                        $from = $record->valid_from?->format('d/m/Y');
                        $until = $record->valid_until?->format('d/m/Y');

                        if (! $from && ! $until) {
                            return 'Sem validade';
                        }

                        return trim(($from ? $from : '') . ' - ' . ($until ? $until : ''));
                    }),
                Tables\Columns\TextColumn::make('uses')
                    ->label('Usos')
                    ->getStateUsing(function (Coupon $record) {
                        $max = $record->max_redemptions ? '/' . $record->max_redemptions : '';
                        return $record->redeemed_count . $max;
                    }),
                Tables\Columns\TextColumn::make('allowed_plan_codes')
                    ->label('Planos')
                    ->formatStateUsing(function ($state) {
                        if (! is_array($state) || empty($state)) {
                            return 'Todos';
                        }
                        return Str::upper(implode(', ', $state));
                    }),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativo'),
                Tables\Filters\Filter::make('expired')
                    ->label('Expirado')
                    ->query(fn ($query) => $query->whereNotNull('valid_until')->where('valid_until', '<', now())),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percent' => 'Percentual',
                        'fixed' => 'Valor fixo',
                    ]),
                Tables\Filters\SelectFilter::make('plan')
                    ->label('Plano')
                    ->options(fn () => Plan::query()->pluck('name', 'code')->all())
                    ->query(function ($query, $value) {
                        if (! $value) {
                            return $query;
                        }
                        return $query->whereJsonContains('allowed_plan_codes', $value);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Coupon $record) => $record->is_active ? 'Desativar' : 'Ativar')
                    ->action(fn (Coupon $record) => $record->update(['is_active' => ! $record->is_active])),
                Tables\Actions\Action::make('end_now')
                    ->label('Encerrar agora')
                    ->requiresConfirmation()
                    ->action(fn (Coupon $record) => $record->update(['valid_until' => now()])),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicar')
                    ->requiresConfirmation()
                    ->action(function (Coupon $record) {
                        $copy = $record->replicate(['code', 'redeemed_count', 'created_at', 'updated_at']);
                        $copy->code = $record->code . '_COPY_' . now()->format('His');
                        $copy->redeemed_count = 0;
                        $copy->save();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RedemptionsRelationManager::class,
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Métricas')
                ->schema([
                    TextEntry::make('redeemed_count')
                        ->label('Total de usos'),
                    TextEntry::make('uses_last_7')
                        ->label('Usos (7 dias)')
                        ->getStateUsing(function (Coupon $record) {
                            return CouponRedemption::query()
                                ->where('coupon_id', $record->id)
                                ->where('redeemed_at', '>=', now()->subDays(7))
                                ->count();
                        }),
                    TextEntry::make('uses_last_30')
                        ->label('Usos (30 dias)')
                        ->getStateUsing(function (Coupon $record) {
                            return CouponRedemption::query()
                                ->where('coupon_id', $record->id)
                                ->where('redeemed_at', '>=', now()->subDays(30))
                                ->count();
                        }),
                ])
                ->columns(3),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'view' => Pages\ViewCoupon::route('/{record}'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_admin_id'] = auth('admin')->id();
        $data['code'] = strtoupper($data['code'] ?? '');

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        return $data;
    }
}
