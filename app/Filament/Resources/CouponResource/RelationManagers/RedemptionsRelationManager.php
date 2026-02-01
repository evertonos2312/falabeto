<?php

namespace App\Filament\Resources\CouponResource\RelationManagers;

use App\Models\CouponRedemption;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'redemptions';

    protected static ?string $title = 'Resgates';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('client.phone_e164')
                    ->label('Celular'),
                Tables\Columns\TextColumn::make('subscription_id')
                    ->label('Assinatura'),
                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('redeemed_at', 'desc');
    }
}
