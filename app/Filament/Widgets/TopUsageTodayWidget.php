<?php

namespace App\Filament\Widgets;

use App\Models\UsageDaily;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopUsageTodayWidget extends BaseWidget
{
    protected static ?string $heading = 'Top 10 clientes por uso hoje';

    protected function getTableQuery(): Builder
    {
        return UsageDaily::query()
            ->where('date', now()->toDateString())
            ->with('client')
            ->orderByDesc('messages_in')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('client.name')->label('Cliente'),
            TextColumn::make('client.email')->label('Email'),
            TextColumn::make('messages_in')->label('Mensagens hoje'),
        ];
    }
}
