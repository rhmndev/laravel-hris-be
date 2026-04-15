<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
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
                    ->label('Check-in')
                    ->dateTime('H:i')
                    ->sortable(),

                TextColumn::make('check_out_time')
                    ->label('Check-out')
                    ->dateTime('H:i')
                    ->placeholder('-')
                    ->sortable(),


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
