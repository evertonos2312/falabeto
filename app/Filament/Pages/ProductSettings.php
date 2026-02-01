<?php

namespace App\Filament\Pages;

use App\Models\Admin;
use App\Support\Settings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ProductSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Configurações';

    protected static ?string $navigationLabel = 'Configurações do Produto';

    protected static ?string $title = 'Configurações do Produto';

    protected static ?string $slug = 'settings';

    protected static string $view = 'filament.pages.product-settings';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'branding.product_name' => Settings::get('branding.product_name', 'Fala Beto'),
            'branding.tagline' => Settings::get('branding.tagline', 'Seu controle financeiro no WhatsApp'),
            'branding.logo_path' => Settings::get('branding.logo_path'),
            'branding.favicon_path' => Settings::get('branding.favicon_path'),
            'commercial.trial_days_default' => Settings::get('commercial.trial_days_default', 14),
            'commercial.trial_enabled_default' => Settings::get('commercial.trial_enabled_default', true),
            'commercial.support_whatsapp_e164' => Settings::get('commercial.support_whatsapp_e164', '+55'),
            'commercial.support_email' => Settings::get('commercial.support_email', 'suporte@falabeto.test'),
            'security.log_message_body' => Settings::get('security.log_message_body', false),
            'security.message_logs_retention_days' => Settings::get('security.message_logs_retention_days', 30),
            'security.rate_limit_enabled' => Settings::get('security.rate_limit_enabled', true),
        ]);
    }

    public static function canAccess(): bool
    {
        $user = auth('admin')->user();

        return $user?->hasAnyRole(['owner', 'admin', 'support']) ?? false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Branding')
                    ->schema([
                        Forms\Components\TextInput::make('branding.product_name')
                            ->label('Nome do produto')
                            ->required()
                            ->disabled(fn () => $this->isReadOnly()),
                        Forms\Components\TextInput::make('branding.tagline')
                            ->label('Tagline')
                            ->disabled(fn () => $this->isReadOnly()),
                        Forms\Components\FileUpload::make('branding.logo_path')
                            ->label('Logo')
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->maxSize(512)
                            ->hidden(fn () => $this->isReadOnly()),
                        Forms\Components\FileUpload::make('branding.favicon_path')
                            ->label('Favicon')
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'])
                            ->maxSize(256)
                            ->hidden(fn () => $this->isReadOnly()),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Trial & Contato')
                    ->schema([
                        Forms\Components\TextInput::make('commercial.trial_days_default')
                            ->label('Dias de trial')
                            ->numeric()
                            ->minValue(1)
                            ->disabled(fn () => $this->isReadOnly()),
                        Forms\Components\Toggle::make('commercial.trial_enabled_default')
                            ->label('Trial habilitado')
                            ->disabled(fn () => $this->isReadOnly()),
                        Forms\Components\TextInput::make('commercial.support_whatsapp_e164')
                            ->label('WhatsApp suporte (E.164)')
                            ->disabled(fn () => $this->isReadOnly()),
                        Forms\Components\TextInput::make('commercial.support_email')
                            ->label('Email de suporte')
                            ->email()
                            ->disabled(fn () => $this->isReadOnly()),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Segurança & Logs')
                    ->schema([
                        Forms\Components\Toggle::make('security.log_message_body')
                            ->label('Salvar corpo da mensagem')
                            ->disabled(fn () => $this->isReadOnly()),
                        Forms\Components\TextInput::make('security.message_logs_retention_days')
                            ->label('Retenção de logs (dias)')
                            ->numeric()
                            ->minValue(1)
                            ->disabled(fn () => $this->isReadOnly()),
                        Forms\Components\Toggle::make('security.rate_limit_enabled')
                            ->label('Rate limit habilitado')
                            ->disabled(fn () => $this->isReadOnly()),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $data = $this->form->getState();
        $admin = auth('admin')->user();

        Settings::set('branding.product_name', $data['branding.product_name'] ?? 'Fala Beto', 'string', 'branding', $admin);
        Settings::set('branding.tagline', $data['branding.tagline'] ?? null, 'string', 'branding', $admin);
        Settings::set('branding.logo_path', $data['branding.logo_path'] ?? null, 'file', 'branding', $admin);
        Settings::set('branding.favicon_path', $data['branding.favicon_path'] ?? null, 'file', 'branding', $admin);

        Settings::set('commercial.trial_days_default', $data['commercial.trial_days_default'] ?? 14, 'int', 'commercial', $admin);
        Settings::set('commercial.trial_enabled_default', $data['commercial.trial_enabled_default'] ?? false, 'bool', 'commercial', $admin);
        Settings::set('commercial.support_whatsapp_e164', $data['commercial.support_whatsapp_e164'] ?? null, 'string', 'commercial', $admin);
        Settings::set('commercial.support_email', $data['commercial.support_email'] ?? null, 'string', 'commercial', $admin);

        Settings::set('security.log_message_body', $data['security.log_message_body'] ?? false, 'bool', 'security', $admin);
        Settings::set('security.message_logs_retention_days', $data['security.message_logs_retention_days'] ?? 30, 'int', 'security', $admin);
        Settings::set('security.rate_limit_enabled', $data['security.rate_limit_enabled'] ?? true, 'bool', 'security', $admin);

        Notification::make()
            ->title('Configurações salvas.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        if ($this->isReadOnly()) {
            return [];
        }

        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Salvar')
                ->submit('save'),
        ];
    }

    private function isReadOnly(): bool
    {
        /** @var Admin|null $user */
        $user = auth('admin')->user();

        return $user?->hasRole('support') ?? false;
    }
}
