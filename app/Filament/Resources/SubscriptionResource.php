<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\UsageDaily;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Contratos';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'trialing' => 'trialing',
                        'active' => 'active',
                        'past_due' => 'past_due',
                        'canceled' => 'canceled',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('cancel_at_period_end'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(static::getEloquentQuery()->with(['client', 'plan', 'items']))
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.phone_e164')
                    ->label('Telefone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plano'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Trial até')
                    ->date(),
                Tables\Columns\TextColumn::make('next_renewal_at')
                    ->label('Próx. renovação')
                    ->date(),
                Tables\Columns\TextColumn::make('messages_today')
                    ->label('Mensagens hoje')
                    ->getStateUsing(function (Subscription $record) {
                        return UsageDaily::query()
                            ->where('client_id', $record->client_id)
                            ->where('date', now()->toDateString())
                            ->value('messages_in') ?? 0;
                    }),
                Tables\Columns\TextColumn::make('percent_of_limit')
                    ->label('% do limite')
                    ->getStateUsing(function (Subscription $record) {
                        $messages = UsageDaily::query()
                            ->where('client_id', $record->client_id)
                            ->where('date', now()->toDateString())
                            ->value('messages_in') ?? 0;
                        $limit = static::messageLimit($record);
                        if ($limit <= 0) {
                            return '0%';
                        }
                        return (int) round(($messages / $limit) * 100) . '%';
                    }),
                Tables\Columns\TextColumn::make('last_activity_at')
                    ->label('Última atividade')
                    ->getStateUsing(function (Subscription $record) {
                        return $record->client->messageLogs()
                            ->latest('created_at')
                            ->value('created_at');
                    })
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'trialing' => 'trialing',
                        'active' => 'active',
                        'past_due' => 'past_due',
                        'canceled' => 'canceled',
                    ]),
                SelectFilter::make('plan_id')
                    ->label('Plano')
                    ->options(fn () => Plan::query()->pluck('name', 'id')->all()),
                Filter::make('whatsapp_verified')
                    ->label('WhatsApp verificado')
                    ->query(fn (Builder $query) => $query->whereHas('client', fn (Builder $q) => $q->whereNotNull('whatsapp_verified_at'))),
                Filter::make('trial_ending_soon')
                    ->label('Trial termina em 7 dias')
                    ->query(fn (Builder $query) => $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '<=', now()->addDays(7))),
                Filter::make('high_usage_today')
                    ->label('Uso alto hoje')
                    ->query(function (Builder $query) {
                        return $query->whereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('usage_daily')
                                ->whereColumn('usage_daily.client_id', 'subscriptions.client_id')
                                ->where('usage_daily.date', now()->toDateString())
                                ->whereRaw('usage_daily.messages_in >= 0.8 * ?',
                                    [static::defaultMessagesLimit()]);
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('started_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'view' => Pages\ViewSubscription::route('/{record}'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }

    private static function messageLimit(Subscription $subscription): int
    {
        $override = $subscription->items()
            ->where('item_type', 'feature')
            ->where('item_code', 'messages_per_day_override')
            ->first();

        if ($override) {
            return (int) ($override->meta_json['value_int'] ?? $override->meta_json['value'] ?? 0);
        }

        $default = $subscription->items()
            ->where('item_type', 'feature')
            ->where('item_code', 'messages_per_day')
            ->first();

        return (int) ($default->meta_json['value_int'] ?? $default->meta_json['value'] ?? 0);
    }

    private static function defaultMessagesLimit(): int
    {
        return 30;
    }
}
