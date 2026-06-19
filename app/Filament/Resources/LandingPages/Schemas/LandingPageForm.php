<?php

namespace App\Filament\Resources\LandingPages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;


class LandingPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Setting Key')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->placeholder('hero_title')
                    ->helperText('Unique identifier for this setting (e.g. hero_title, feature_1_icon)'),

                TextInput::make('label')
                    ->label('Display Label')
                    ->maxLength(150)
                    ->placeholder('Hero Title')
                    ->helperText('Admin display label for easy identification'),

                Select::make('type')
                    ->label('Value Type')
                    ->options([
                        'text' => 'Text (single line)',
                        'textarea' => 'Textarea (rich text)',
                        'image' => 'Image (file upload)',
                    ])
                    ->default('text')
                    ->required()
                    ->reactive(),

                Select::make('group')
                    ->label('Section Group')
                    ->options([
                        'hero' => 'Hero Section',
                        'features' => 'Features Section',
                        'screenshots' => 'Screenshots Section',
                        'about' => 'About Section',
                        'footer' => 'Footer Section',
                    ])
                    ->default('hero')
                    ->required()
                    ->searchable(),

                TextInput::make('value')
                    ->label('Value (Text)')
                    ->maxLength(500)
                    ->visible(fn (callable $get) => $get('type') === 'text')
                    ->placeholder('Enter text value'),

                RichEditor::make('value')
                    ->label('Value (Rich Text)')
                    ->columnSpanFull()
                    ->visible(fn (callable $get) => $get('type') === 'textarea'),

                FileUpload::make('value')
                    ->label('Value (Image)')
                    ->directory('landing')
                    ->disk('public')
                    ->image()
                    ->maxSize(5120) // 5MB
                    ->visible(fn (callable $get) => $get('type') === 'image')
                    ->formatStateUsing(function ($state) {
                        // Unwrap array state back to string (FileUpload internally wraps in array)
                        if (is_array($state)) {
                            return $state[0] ?? null;
                        }
                        return is_string($state) ? $state : null;
                    })
                    ->dehydrateStateUsing(function ($state) {
                        // Always store as plain string, not array
                        if (is_array($state)) {
                            return $state[0] ?? null;
                        }
                        return is_string($state) ? $state : null;
                    }),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first within the group'),
            ]);
    }
}
