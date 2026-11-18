<?php

namespace App\Providers\Filament;

use App\Filament\Crm\Pages\ManageRecords;
use App\Models\CustomObject;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CrmPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('crm')
            ->path('crm')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverPages(
                in: app_path('Filament/Crm/Pages'),
                for: 'App\Filament\Crm\Pages',
            )
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Crm/Widgets'),
                for: 'App\Filament\Crm\Widgets',
            )
            ->widgets([
                AccountWidget::class,
            ])
            ->navigationItems($this->customObjectNavigation())
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
            ]);
    }

    protected function customObjectNavigation(): array
    {
        if (! Schema::hasTable('custom_objects')) {
            return [];
        }

        return CustomObject::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get()
            ->map(fn (CustomObject $object) => NavigationItem::make($object->title)
                ->url(fn () => ManageRecords::getUrl(['objectKey' => $object->key], panel: 'crm'))
                ->isActiveWhen(fn () => request()->route('objectKey') === $object->key))
            ->values()
            ->all();
    }
}