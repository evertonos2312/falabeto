<?php

namespace App\Filament\Resources\PlanResource\RelationManagers;

use App\Models\PlanItem;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class PlanItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Itens do Plano';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('item_code')
                ->label('Código')
                ->options([
                    'messages_per_day' => 'Mensagens por dia',
                    'scheduled_transactions_limit' => 'Limite transações agendadas',
                    'export_csv' => 'Exportar CSV',
                    'reports_level' => 'Nível de relatórios',
                    'ai_mode' => 'Modo IA',
                ])
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $map = [
                        'messages_per_day' => 'int',
                        'scheduled_transactions_limit' => 'int',
                        'export_csv' => 'bool',
                        'reports_level' => 'int',
                        'ai_mode' => 'string',
                    ];
                    if (isset($map[$state])) {
                        $set('item_type', $map[$state]);
                    }
                })
                ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                    return $rule->where('plan_id', $this->getOwnerRecord()->id);
                }),
            Forms\Components\Select::make('item_type')
                ->options([
                    'int' => 'int',
                    'bool' => 'bool',
                    'string' => 'string',
                ])
                ->required()
                ->reactive(),
            Forms\Components\TextInput::make('value_int')
                ->label('Valor (int)')
                ->numeric()
                ->visible(fn (Forms\Get $get) => $get('item_type') === 'int')
                ->required(fn (Forms\Get $get) => $get('item_type') === 'int'),
            Forms\Components\Toggle::make('value_bool')
                ->label('Valor (bool)')
                ->visible(fn (Forms\Get $get) => $get('item_type') === 'bool')
                ->required(fn (Forms\Get $get) => $get('item_type') === 'bool'),
            Forms\Components\TextInput::make('value_string')
                ->label('Valor (string)')
                ->visible(fn (Forms\Get $get) => $get('item_type') === 'string')
                ->required(fn (Forms\Get $get) => $get('item_type') === 'string'),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_code')
                    ->label('Item')
                    ->formatStateUsing(fn (string $state) => $this->labelForCode($state)),
                Tables\Columns\TextColumn::make('item_type')
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('value_display')
                    ->label('Valor')
                    ->getStateUsing(function (PlanItem $record) {
                        return match ($record->item_type) {
                            'int' => (string) $record->value_int,
                            'bool' => $record->value_bool ? 'Sim' : 'Não',
                            'string' => (string) $record->value_string,
                            default => '-',
                        };
                    }),
            ])
            ->defaultSort('item_code')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->mutateValues($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->mutateValues($data);
    }

    public function mutateValues(array $data): array
    {
        if (($data['item_type'] ?? null) === 'int') {
            $data['value_bool'] = null;
            $data['value_string'] = null;
        } elseif (($data['item_type'] ?? null) === 'bool') {
            $data['value_int'] = null;
            $data['value_string'] = null;
        } elseif (($data['item_type'] ?? null) === 'string') {
            $data['value_int'] = null;
            $data['value_bool'] = null;
        }

        return $data;
    }

    private function labelForCode(string $code): string
    {
        return [
            'messages_per_day' => 'Mensagens por dia',
            'scheduled_transactions_limit' => 'Limite transações agendadas',
            'export_csv' => 'Exportar CSV',
            'reports_level' => 'Nível de relatórios',
            'ai_mode' => 'Modo IA',
        ][$code] ?? $code;
    }
}
