<?php

namespace App\Filament\Resources\Payrolls\Pages;

use App\Filament\Resources\Payrolls\PayrollResource;
use App\Models\User;
use App\Services\PayrollCalculationService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('batchCalculate')
                ->label('Hitung Semua Karyawan')
                ->icon('heroicon-o-calculator')
                ->color('success')
                ->form([
                    Grid::make(2)
                        ->components([
                            Select::make('month')
                                ->label('Bulan')
                                ->options([
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                    4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                    10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                                ])
                                ->default((int) date('n'))
                                ->required(),
                            TextInput::make('year')
                                ->label('Tahun')
                                ->numeric()
                                ->default(date('Y'))
                                ->required(),
                        ]),
                ])
                ->action(function (array $data): void {
                    $month = (int) $data['month'];
                    $year = (int) $data['year'];

                    $service = app(PayrollCalculationService::class);
                    $users = User::where('employee_status', 'aktif')
                        ->whereNotNull('employee_id')
                        ->get();

                    $success = 0;
                    $failed = 0;
                    $errors = [];

                    foreach ($users as $user) {
                        try {
                            $service->calculatePayroll($user->id, $month, $year);
                            $success++;
                        } catch (\Exception $e) {
                            $failed++;
                            $errors[] = "{$user->name}: {$e->getMessage()}";
                        }
                    }

                    $body = "Berhasil: {$success} karyawan\nGagal: {$failed} karyawan";
                    if (!empty($errors)) {
                        $body .= "\n\nDetail error:\n" . implode("\n", array_slice($errors, 0, 5));
                        if (count($errors) > 5) {
                            $body .= "\n...dan " . (count($errors) - 5) . " error lainnya";
                        }
                    }

                    Notification::make()
                        ->title("Batch Calculate Selesai ({$month}/{$year})")
                        ->body($body)
                        ->{$failed > 0 ? 'warning' : 'success'}()
                        ->persistent()
                        ->send();
                }),
        ];
    }
}
