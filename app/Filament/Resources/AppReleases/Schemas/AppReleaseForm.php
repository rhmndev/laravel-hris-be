<?php

namespace App\Filament\Resources\AppReleases\Schemas;

use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AppReleaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('version')
                    ->label('Version (e.g. 1.2.0)')
                    ->required()
                    ->maxLength(20)
                    ->placeholder('1.0.0'),

                FileUpload::make('file_path')
                    ->label('APK File')
                    ->directory('releases')
                    ->disk('public')
                    ->acceptedFileTypes([
                        'application/vnd.android.package-archive',
                        'application/octet-stream',
                        'application/zip',
                    ])
                    ->rules([
                        function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if ($value && !str_ends_with(strtolower($value->getClientOriginalName() ?? ''), '.apk')) {
                                    $fail('Only .apk files are allowed.');
                                }
                            };
                        },
                    ])
                    ->maxSize(102400) // 100MB
                    ->required()
                    ->hint('Only .apk files are allowed'),

                RichEditor::make('release_notes')
                    ->label('Release Notes / Changelog')
                    ->columnSpanFull(),

                TextInput::make('min_android_version')
                    ->label('Minimum Android Version')
                    ->default('8.0')
                    ->maxLength(10),

                Toggle::make('is_published')
                    ->label('Published')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state && !request()->input('data.published_at')) {
                            $set('published_at', now());
                        }
                    }),

                DateTimePicker::make('published_at')
                    ->label('Published At')
                    ->native(false),
            ]);
    }
}
