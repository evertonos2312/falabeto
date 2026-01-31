<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Filament\Resources\PlanResource\RelationManagers\PlanItemsRelationManager;
use App\Models\Plan;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Str;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Planos';

    protected static ?string $navigationLabel = 'Planos';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('code')
                ->options([
                    'start' => 'start',
                    'intermediate' => 'intermediate',
                    'premium' => 'premium',
                ])
                ->searchable()
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('price_brl')
                ->label('Preço (R$)')
                ->placeholder('10,00')
                ->required()
                ->dehydrated(false)
                ->formatStateUsing(fn (?Plan $record) => $record ? number_format($record->price_cents / 100, 2, ',', '.') : null),
            Forms\Components\Toggle::make('is_active')
                ->default(true),
            Forms\Components\Toggle::make('trial_enabled')
                ->default(true)
                ->reactive(),
            Forms\Components\TextInput::make('trial_days')
                ->numeric()
                ->default(30)
                ->visible(fn (Forms\Get $get) => (bool) $get('trial_enabled')),
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('price_cents')
                    ->label('Preço')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('trial_enabled')
                    ->label('Trial')
                    ->formatStateUsing(fn (Plan $record) => $record->trial_enabled ? "Sim ({$record->trial_days}d)" : 'Não'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordem'),
            ])
            ->defaultSort('sort_order')
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            PlanItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['price_cents'] = static::priceToCents($data['price_brl'] ?? '0');
        unset($data['price_brl']);

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('price_brl', $data)) {
            $data['price_cents'] = static::priceToCents($data['price_brl']);
            unset($data['price_brl']);
        }

        return $data;
    }

    private static function priceToCents(string $value): int
    {
        $normalized = Str::of($value)->replace('.', '')->replace(',', '.')->toString();
        return (int) round((float) $normalized * 100);
    }
}
