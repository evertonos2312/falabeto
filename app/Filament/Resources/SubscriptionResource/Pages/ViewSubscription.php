<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Models\MessageLog;
use App\Models\UsageDaily;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $record = $this->getRecord();

        $messagesToday = UsageDaily::query()
            ->where('client_id', $record->client_id)
            ->where('date', now()->toDateString())
            ->value('messages_in') ?? 0;

        $messagesMonth = UsageDaily::query()
            ->where('client_id', $record->client_id)
            ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('messages_in');

        $recentLogs = MessageLog::query()
            ->where('client_id', $record->client_id)
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(fn (MessageLog $log) => [
                'direction' => $log->direction,
                'snippet' => $log->body_snippet,
                'hash' => $log->body_hash,
                'action' => $log->meta_json['action'] ?? null,
                'llm_used' => $log->llm_used ? 'sim' : 'não',
                'created_at' => $log->created_at?->format('d/m H:i'),
            ])
            ->all();

        return $infolist->schema([
            Section::make('Status e datas')
                ->schema([
                    TextEntry::make('client.name')->label('Cliente'),
                    TextEntry::make('client.email')->label('Email'),
                    TextEntry::make('client.phone_e164')->label('Telefone'),
                    TextEntry::make('plan.name')->label('Plano'),
                    TextEntry::make('status')->label('Status'),
                    TextEntry::make('trial_ends_at')->date(),
                    TextEntry::make('next_renewal_at')->date(),
                ])->columns(3),
            Section::make('Snapshot')
                ->schema([
                    RepeatableEntry::make('items')
                        ->schema([
                            TextEntry::make('item_type')->label('Tipo'),
                            TextEntry::make('item_code')->label('Código'),
                            TextEntry::make('description')->label('Descrição'),
                            TextEntry::make('unit_price_cents')
                                ->label('Preço')
                                ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),
                        ])
                        ->columns(4),
                ]),
            Section::make('Consumo')
                ->schema([
                    TextEntry::make('messages_today')
                        ->label('Mensagens hoje')
                        ->state($messagesToday),
                    TextEntry::make('messages_month')
                        ->label('Mensagens no mês')
                        ->state($messagesMonth),
                ])->columns(2),
            Section::make('Últimas mensagens')
                ->schema([
                    RepeatableEntry::make('recent_logs')
                        ->state($recentLogs)
                        ->schema([
                            TextEntry::make('created_at')->label('Quando'),
                            TextEntry::make('direction')->label('Direção'),
                            TextEntry::make('action')->label('Ação'),
                            TextEntry::make('llm_used')->label('LLM'),
                            TextEntry::make('snippet')->label('Snippet'),
                            TextEntry::make('hash')->label('Hash'),
                        ])
                        ->columns(6),
                ]),
        ]);
    }
}
