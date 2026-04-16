<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Schemas\Components\Image;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
// use Filament\Tables\Columns\BadgeColumn; // deprecated in favor of TextColumn with badge() method


class LatestAttendanceWidget extends TableWidget
{

    protected static ?string $heading = 'Kehadiran Hari Ini';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                ->with('user')
                    ->whereDate('attendance_date', Carbon::today())
                    ->latest('check_in_time')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.employee_id')
                    ->label('ID Karyawan')
                    ->searchable(),

                TextColumn::make('check_in_time')
                    ->label('Time-In')
                    ->dateTime('H:i')
                    ->placeholder('-')
                    ->AlignCenter()
                    ->sortable(),

                ImageColumn::make('check_in_photo')
                   ->label('Foto Check-in')
                    ->disk('public')
                    ->circular()
                    ->alignCenter()
                    ->ImageHeight(60)
                    ->ImageWidth(60)
                    ->placeholder('-'),

                TextColumn::make('check_out_time')
                    ->label('Time Out')
                    ->dateTime('H:i')
                    ->placeholder('-')
                    ->AlignCenter()
                    ->sortable(),

                ImageColumn::make('check_out_photo')
                    ->label('Foto Check-out')
                    ->disk('public')
                    ->circular()
                    ->ImageHeight(60)
                    ->alignCenter()
                    ->ImageWidth(60)
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge() // for badge of status
                    ->colors([
                        'success' => 'on_time',
                        'warning' => 'late',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'on_time' => 'Tepat Waktu',
                        'late' => 'Terlambat',
                        default => $state,
                    }),
            ])
           ->defaultSort('check_in_time', 'desc');
    }
}
