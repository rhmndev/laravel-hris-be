<?php

namespace App\Filament\Resources\Payrolls\RelationManagers;

use App\Models\User;
use App\Services\PayrollApprovalService;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class ApprovalsRelationManager extends RelationManager
{
    protected static string $relationship = 'approvals';

    protected static ?string $title = 'Persetujuan Payroll';

    public function form(Schema $schema): Schema
    {
        // Form not used — approvals are managed via workflow actions
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('approver.name')
                    ->label('Approver')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Belum ditentukan'),

                TextColumn::make('approval_level')
                    ->label('Level')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match((int) $state) {
                        1 => 'HR Check',
                        2 => 'Finance Approval',
                        3 => 'Director Approval',
                        default => (string) $state,
                    })
                    ->color(fn ($state): string => match((int) $state) {
                        1 => 'info',
                        2 => 'warning',
                        3 => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('remarks')
                    ->label('Catatan')
                    ->limit(50)
                    ->placeholder('-'),

                TextColumn::make('approved_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->actions([
                Action::make('approveLevel')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->size('sm')
                    ->visible(function ($record) {
                        if ($record->status !== 'pending') return false;
                        /** @var User|null $user */
                        $user = Auth::user();
                        if (!$user) return false;
                        // Level 1 (HR Check): only hr_manager can check
                        if ($record->approval_level == 1) {
                            return $user->hasAnyRole(['super_admin', 'hr_manager']);
                        }
                        // Level 2 (Finance Approval): only finance_manager/director can approve
                        if ($record->approval_level == 2) {
                            return $user->hasAnyRole(['super_admin', 'finance_manager', 'director']);
                        }
                        return false;
                    })
                    ->form([
                        Textarea::make('remarks')->label('Catatan')->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $payroll = $record->payroll;
                        /** @var User|null $user */
                        $user = Auth::user();
                        $service = app(PayrollApprovalService::class);

                        try {
                            if ($record->approval_level == 1) {
                                $service->checkPayroll($payroll, $user, $data['remarks'] ?? '');
                                Notification::make()->success()->title('Payroll diperiksa (Level 1).')->send();
                            } elseif ($record->approval_level == 2) {
                                $service->approvePayroll($payroll, $user, $data['remarks'] ?? '');
                                Notification::make()->success()->title('Payroll disetujui (Level 2).')->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                        }
                    }),

                Action::make('rejectLevel')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->size('sm')
                    ->visible(function ($record) {
                        if ($record->status !== 'pending') return false;
                        /** @var User|null $user */
                        $user = Auth::user();
                        if (!$user) return false;
                        // Anyone who can check or approve can also reject
                        return $user->hasAnyRole(['super_admin', 'hr_manager', 'finance_manager', 'director']);
                    })
                    ->form([
                        Textarea::make('remarks')->label('Alasan Penolakan')->rows(2)->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $payroll = $record->payroll;
                        /** @var User|null $user */
                        $user = Auth::user();
                        $service = app(PayrollApprovalService::class);

                        try {
                            $service->rejectPayroll($payroll, $user, $data['remarks']);
                            Notification::make()->success()->title('Payroll ditolak.')->send();
                        } catch (\Exception $e) {
                            Notification::make()->danger()->title('Gagal menolak')->body($e->getMessage())->send();
                        }
                    }),
            ]);
    }
}
