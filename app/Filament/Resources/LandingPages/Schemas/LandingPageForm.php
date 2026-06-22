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

                TextInput::make('value_text')
                    ->label('Value (Text)')
                    ->maxLength(500)
                    ->visibleJs("\$wire.data.type === 'text'")
                    ->placeholder('Enter text value')
                    ->dehydrated(false),

                RichEditor::make('value_textarea')
                    ->label('Value (Rich Text)')
                    ->columnSpanFull()
                    ->visibleJs("\$wire.data.type === 'textarea'")
                    ->dehydrated(false),

                FileUpload::make('value_image')
                    ->label('Value (Image)')
                    ->directory('landing')
                    ->disk('public')
                    ->image()
                    ->maxSize(5120) // 5MB
                    ->visibleJs("\$wire.data.type === 'image'"),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first within the group'),
            ]);
    }
}
