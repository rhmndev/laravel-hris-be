<?php

namespace App\Filament\Resources\Payrolls\Pages;

use App\Filament\Resources\Payrolls\PayrollResource;
use App\Services\PayrollCalculationService;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CreatePayroll extends CreateRecord
{
    protected static string $resource = PayrollResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hitung Payroll Karyawan')
                    ->description('Pilih karyawan, bulan, dan tahun untuk menghitung payroll secara otomatis.')
                    ->components([
                        Select::make('user_id')
                            ->label('Karyawan')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
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
                                    ->required(),
                                TextInput::make('year')
                                    ->label('Tahun')
                                    ->numeric()
                                    ->default(date('Y'))
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $service = app(PayrollCalculationService::class);
            $payroll = $service->calculatePayroll(
                $data['user_id'],
                (int) $data['month'],
                (int) $data['year']
            );

            Notification::make()
                ->success()
                ->title('Payroll berhasil dihitung')
                ->send();

            return $payroll;
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Gagal menghitung payroll')
                ->body($e->getMessage())
                ->send();

            throw new \Filament\Support\Exceptions\Halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return PayrollResource::getUrl();
    }
}
