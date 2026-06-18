<?php

namespace App\Filament\Resources\Payrolls;

use App\Filament\Resources\Payrolls\Pages\CreatePayroll;
use App\Filament\Resources\Payrolls\Pages\EditPayroll;
use App\Filament\Resources\Payrolls\Pages\ListPayrolls;
use App\Filament\Resources\Payrolls\Pages\ViewPayroll;
use App\Models\Payroll;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use App\Filament\Resources\Payrolls\RelationManagers\DetailsRelationManager;
use App\Filament\Resources\Payrolls\RelationManagers\ApprovalsRelationManager;
use App\Filament\Resources\Payrolls\RelationManagers\HistoryRelationManager;
use App\Services\PayrollApprovalService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user && $user->hasAnyRole([
            'super_admin', 'director', 'hr_manager', 'finance_manager',
            'manager', 'supervisor', 'hrd_it', 'operator',
        ]);
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static ?string $navigationLabel = 'Slip Gaji';
    protected static string|UnitEnum|null $navigationGroup = 'Payroll';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Payroll')
                    ->components([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required(),
                        Grid::make(3)
                            ->components([
                                TextInput::make('month')
                                    ->label('Bulan')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(12)
                                    ->required(),
                                TextInput::make('year')
                                    ->label('Tahun')
                                    ->numeric()
                                    ->required(),
                                Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'calculated' => 'Dihitung',
                                        'submitted' => 'Diajukan',
                                        'checked' => 'Diperiksa',
                                        'approved' => 'Disetujui',
                                        'paid' => 'Dibayar',
                                        'rejected' => 'Ditolak',
                                    ])
                                    ->required(),
                            ]),
                    ]),

                Section::make('Data Kehadiran')
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextInput::make('total_working_days')
                                    ->label('Total Hari Kerja')
                                    ->numeric()
                                    ->readOnly(),
                                TextInput::make('present_days')
                                    ->label('Hari Hadir')
                                    ->numeric()
                                    ->readOnly(),
                                TextInput::make('absent_days')
                                    ->label('Hari Absen')
                                    ->numeric()
                                    ->readOnly(),
                                TextInput::make('late_days')
                                    ->label('Hari Terlambat')
                                    ->numeric()
                                    ->readOnly(),
                                TextInput::make('paid_leave_days')
                                    ->label('Cuti Dibayar')
                                    ->numeric()
                                    ->readOnly(),
                                TextInput::make('unpaid_leave_days')
                                    ->label('Cuti Tidak Dibayar')
                                    ->numeric()
                                    ->readOnly(),
                            ]),
                    ]),

                Section::make('Perhitungan Gaji')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('basic_salary')
                                    ->label('Gaji Pokok')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('total_earnings')
                                    ->label('Total Penghasilan Tambahan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('gross_salary')
                                    ->label('Gaji Kotor')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('total_deductions')
                                    ->label('Total Potongan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('net_salary')
                                    ->label('Gaji Bersih')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->columnSpanFull()
                                    ->helperText('Gaji yang akan diterima karyawan'),
                            ]),
                    ]),

                Section::make('Pajak & BPJS')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('pph21')
                                    ->label('PPh 21')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('bpjs_kesehatan')
                                    ->label('BPJS Kesehatan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('bpjs_ketenagakerjaan')
                                    ->label('BPJS Ketenagakerjaan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('jht')
                                    ->label('JHT (Dana Pensiun)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                            ]),
                    ]),

                Section::make('Informasi Pembayaran')
                    ->components([
                        DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran'),
                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'transfer' => 'Transfer Bank',
                                'cash' => 'Tunai',
                                'check' => 'Cek',
                            ]),
                        TextInput::make('payment_reference')
                            ->label('Referensi Pembayaran'),
                    ]),

                Section::make('Catatan')
                    ->components([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gross_salary')
                    ->label('Gaji Kotor')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_salary')
                    ->label('Gaji Bersih')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'draft' => 'gray',
                        'calculated' => 'warning',
                        'submitted' => 'info',
                        'checked' => 'primary',
                        'approved' => 'success',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'draft' => 'Draft',
                        'calculated' => 'Dihitung',
                        'submitted' => 'Diajukan',
                        'checked' => 'Diperiksa',
                        'approved' => 'Disetujui',
                        'paid' => 'Dibayar',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'calculated' => 'Dihitung',
                        'submitted' => 'Diajukan',
                        'checked' => 'Diperiksa',
                        'approved' => 'Disetujui',
                        'paid' => 'Dibayar',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('year')
                    ->label('Tahun'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'rejected'])),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'draft'),

                Action::make('submit')
                    ->label('Ajukan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'calculated')
                    ->requiresConfirmation()
                    ->modalDescription('Ajukan payroll ini untuk persetujuan?')
                    ->action(function ($record) {
                        try {
                            app(PayrollApprovalService::class)->submitForApproval($record);
                            Notification::make()->success()->title('Payroll diajukan untuk persetujuan.')->send();
                        } catch (\Exception $e) {
                            Notification::make()->danger()->title('Gagal mengajukan')->body($e->getMessage())->send();
                        }
                    }),

                Action::make('check')
                    ->label('Periksa')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->visible(fn ($record) => $record->status === 'submitted')
                    ->form([
                        Textarea::make('remarks')->label('Catatan')->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            /** @var User|null $user */
                            $user = Auth::user();
                            app(PayrollApprovalService::class)->checkPayroll($record, $user, $data['remarks'] ?? '');
                            Notification::make()->success()->title('Payroll telah diperiksa.')->send();
                        } catch (\Exception $e) {
                            Notification::make()->danger()->title('Gagal memeriksa')->body($e->getMessage())->send();
                        }
                    }),

                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'checked')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('remarks')->label('Catatan')->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            /** @var User|null $user */
                            $user = Auth::user();
                            app(PayrollApprovalService::class)->approvePayroll($record, $user, $data['remarks'] ?? '');
                            Notification::make()->success()->title('Payroll disetujui.')->send();
                        } catch (\Exception $e) {
                            Notification::make()->danger()->title('Gagal menyetujui')->body($e->getMessage())->send();
                        }
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['submitted', 'checked']))
                    ->form([
                        Textarea::make('remarks')->label('Alasan Penolakan')->rows(2)->required(),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            /** @var User|null $user */
                            $user = Auth::user();
                            app(PayrollApprovalService::class)->rejectPayroll($record, $user, $data['remarks']);
                            Notification::make()->success()->title('Payroll ditolak.')->send();
                        } catch (\Exception $e) {
                            Notification::make()->danger()->title('Gagal menolak')->body($e->getMessage())->send();
                        }
                    }),

                Action::make('markPaid')
                    ->label('Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(function ($record) {
                        if ($record->status !== 'approved') return false;
                        /** @var User|null $user */
                        $user = Auth::user();
                        return $user && $user->hasAnyRole(['super_admin', 'finance_manager', 'hr_manager']);
                    })
                    ->form([
                        DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->required()
                            ->default(now()),
                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'transfer' => 'Transfer Bank',
                                'cash' => 'Tunai',
                                'check' => 'Cek',
                            ])
                            ->default('transfer')
                            ->required(),
                        TextInput::make('payment_reference')
                            ->label('No. Rekening Karyawan')
                            ->default(fn ($record) => $record->user?->bank_account_number)
                            ->readOnly()
                            ->helperText(fn ($record) => $record->user?->bank_name
                                ? "Bank: {$record->user->bank_name} — {$record->user->bank_account_number}"
                                : 'Data rekening karyawan belum diisi di profil.')
                            ->placeholder('Auto-filled from employee profile'),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            /** @var User|null $user */
                            $user = Auth::user();
                            app(PayrollApprovalService::class)->markAsPaid(
                                $record,
                                $user,
                                $data['payment_date'],
                                $data['payment_method'],
                                $data['payment_reference'] ?? null,
                            );
                            Notification::make()->success()->title('Payroll ditandai sebagai dibayar.')->send();
                        } catch (\Exception $e) {
                            Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('batchSubmit')
                        ->label('Ajukan yang Dipilih')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $service = app(PayrollApprovalService::class);
                            $success = 0;
                            $failed = 0;
                            foreach ($records as $record) {
                                try {
                                    $service->submitForApproval($record);
                                    $success++;
                                } catch (\Exception $e) {
                                    $failed++;
                                }
                            }
                            Notification::make()
                                ->title("Submit Selesai: {$success} berhasil, {$failed} gagal")
                                ->{$failed > 0 ? 'warning' : 'success'}()
                                ->send();
                        }),
                    BulkAction::make('batchApprove')
                        ->label('Setujui yang Dipilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $service = app(PayrollApprovalService::class);
                            /** @var User|null $user */
                            $user = Auth::user();
                            $success = 0;
                            $failed = 0;
                            foreach ($records as $record) {
                                try {
                                    $service->approvePayroll($record, $user);
                                    $success++;
                                } catch (\Exception $e) {
                                    $failed++;
                                }
                            }
                            Notification::make()
                                ->title("Approval Selesai: {$success} berhasil, {$failed} gagal")
                                ->{$failed > 0 ? 'warning' : 'success'}()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DetailsRelationManager::class,
            ApprovalsRelationManager::class,
            HistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrolls::route('/'),
            'create' => CreatePayroll::route('/create'),
            'view' => ViewPayroll::route('/{record}'),
            'edit' => EditPayroll::route('/{record}/edit'),
        ];
    }
}
