<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceChartWidget extends ChartWidget
{
    protected ?string $heading = 'Statistik Kehadiran 7 Hari Terakhir';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

     public static function canView(): bool
    {
        $user = Auth::user();

        return $user && method_exists($user, 'hasRole') && $user->hasRole('super_admin');
    }

   protected function getData(): array
{
    $labels = collect();
    $lateData = collect();
    $onTimeData = collect();

    for ($i = 6; $i >= 0; $i--) {
        $date = Carbon::today()->subDays($i);
        $labels->push($date->format('d M'));

        // Buat query dasar
        $baseQuery = Attendance::whereDate('attendance_date', $date);

        // Gunakan clone agar filter status tidak bercampur
        $onTimeCount = (clone $baseQuery)->where('status', 'on_time')->count();
        $lateCount = (clone $baseQuery)->where('status', 'late')->count();

        $onTimeData->push($onTimeCount);
        $lateData->push($lateCount);
    }

    return [
        'labels' => $labels->toArray(),
        'datasets' => [
            [
                'label' => 'Tepat Waktu',
                'data' => $onTimeData->toArray(),
                'backgroundColor' => '#34D399',
                'borderColor' => '#34D399',
            ],
            [
                'label' => 'Terlambat',
                'data' => $lateData->toArray(),
                'backgroundColor' => '#F87171',
                'borderColor' => '#F87171',
            ],
        ],
    ];
}

    protected function getType(): string
    {
        return 'bar';
    }
}
