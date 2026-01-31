<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Models\SubscriptionItem;
use App\Services\SubscriptionRenewalService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changeStatus')
                ->label('Alterar status')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->options([
                            'trialing' => 'trialing',
                            'active' => 'active',
                            'past_due' => 'past_due',
                            'canceled' => 'canceled',
                        ])
                        ->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->record->update(['status' => $data['status']]);
                    Notification::make()->title('Status atualizado')->success()->send();
                }),
            Action::make('toggleCancel')
                ->label('Toggle cancelamento')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'cancel_at_period_end' => ! $this->record->cancel_at_period_end,
                    ]);
                    Notification::make()->title('Cancelamento atualizado')->success()->send();
                }),
            Action::make('forceRenewal')
                ->label('Forçar renovação')
                ->requiresConfirmation()
                ->action(function () {
                    app(SubscriptionRenewalService::class)->renewSubscription($this->record);
                    Notification::make()->title('Renovação aplicada')->success()->send();
                }),
            Action::make('changePlan')
                ->label('Trocar plano')
                ->form([
                    \Filament\Forms\Components\Select::make('plan_id')
                        ->label('Plano')
                        ->options(Plan::query()->pluck('name', 'id')->all())
                        ->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->record->update(['plan_id' => $data['plan_id']]);
                    app(SubscriptionRenewalService::class)->regenerateSnapshot($this->record->refresh(), true);
                    Notification::make()->title('Plano atualizado')->success()->send();
                }),
            Action::make('adjustLimit')
                ->label('Ajustar limite mensagens/dia')
                ->form([
                    \Filament\Forms\Components\TextInput::make('limit')
                        ->numeric()
                        ->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    SubscriptionItem::updateOrCreate(
                        [
                            'subscription_id' => $this->record->id,
                            'item_type' => 'feature',
                            'item_code' => 'messages_per_day_override',
                        ],
                        [
                            'description' => 'Override mensagens/dia',
                            'quantity' => 1,
                            'unit_price_cents' => 0,
                            'meta_json' => [
                                'type' => 'int',
                                'value_int' => (int) $data['limit'],
                            ],
                        ]
                    );
                    Notification::make()->title('Limite ajustado')->success()->send();
                }),
        ];
    }
}
