<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use App\Filament\Auth\CustomLogin;
use Filament\Support\Colors\Color;
use Awcodes\Overlook\OverlookPlugin;
use App\Filament\Auth\CustomPasswordReset;
use Filament\Http\Middleware\Authenticate;
use Awcodes\Overlook\Widgets\OverlookWidget;
use App\Filament\Resources\SuratIzinResource;
use App\Filament\Resources\IzinLemburResource;
use App\Filament\Widgets\TotalIzinCutiOverview;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Widgets\TotalSuratIzinOverview;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Widgets\TotalIzinLemburOverview;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            // ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarCollapsibleOnDesktop()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class)
            ->passwordReset(CustomPasswordReset::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                // OverlookWidget::class,
            ])
            ->databaseNotifications()
            ->font('Poppins')
            ->spa()
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
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class,
            ])
            ->plugins(
                [
                    \Hasnayeen\Themes\ThemesPlugin::make(),
                    FilamentEditProfilePlugin::make()
                        ->slug('my-profile')
                        ->setTitle('Profil Saya')
                        ->setNavigationLabel('Profil Saya')
                        ->setNavigationGroup('Pengaturan')
                        ->setSort(53)
                        ->setIcon('heroicon-o-user')
                        ->shouldShowDeleteAccountForm(false)
                        ->shouldShowAvatarForm(
                            directory: 'avatars',
                        ),
                    \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                    // OverlookPlugin::make()
                    //     ->sort(2)
                    //     ->columns([
                    //         'default' => 1,
                    //         'sm' => 2,
                    //         'md' => 2,
                    //         'lg' => 3,
                    //         'xl' => 3,
                    //         '2xl' => null,
                    //     ])
                    //     ->includes([
                    //         SuratIzinResource::class,
                    //         IzinLemburResource::class,
                    //     ])
                    //     ->icons([
                    //         'heroicon-o-heart' => SuratIzinResource::class,
                    //         'heroicon-o-newspaper' => IzinLemburResource::class,
                    //     ]),
                ]
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
