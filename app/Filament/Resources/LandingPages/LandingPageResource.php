<?php

namespace App\Filament\Resources\LandingPages;

use App\Filament\Resources\LandingPages\Pages\CreateLandingPage;
use App\Filament\Resources\LandingPages\Pages\EditLandingPage;
use App\Filament\Resources\LandingPages\Pages\ListLandingPages;
use App\Filament\Resources\LandingPages\Schemas\LandingPageForm;
use App\Filament\Resources\LandingPages\Tables\LandingPagesTable;
use App\Models\LandingSetting;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LandingPageResource extends Resource
{
    protected static ?string $model = LandingSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Landing Page';

    protected static ?string $navigationLabel = 'Page Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Landing Page Setting';

    protected static ?string $slug = 'landing-pages';

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && $user->hasRole('super_admin');
    }

    public static function form(Schema $schema): Schema
    {
        return LandingPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LandingPagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLandingPages::route('/'),
            'create' => CreateLandingPage::route('/create'),
            'edit' => EditLandingPage::route('/{record}/edit'),
        ];
    }
}
