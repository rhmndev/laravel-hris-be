<?php

namespace App\Filament\Resources\LeaveRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\ImageColumn;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Str;
use Filament\Tables\Enums\FiltersLayout;


class LeaveRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->searchable(),
                TextColumn::make('type_label')
                    ->badge()
                    ->color(fn (LeaveRequest $record): string => match ($record->type) {
                        LeaveRequest::TYPE_ANNUAL => 'primary',
                        LeaveRequest::TYPE_SICK => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_days')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('attachment')
                    ->label('Lampiran')
                    ->disk('public')
                    ->visibility('public')
                    ->getStateUsing(fn (LeaveRequest $record): ?string => static::resolveImageAttachment($record)),
                TextColumn::make('status')
                    ->color(fn (LeaveRequest $record): string => match ($record->status) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->badge(),
                TextColumn::make('reviewed_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // select filter approved, rejected, pending
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->label('Status'),

                // select filter cuti tahunan, cuti sakit
                SelectFilter::make('type')
                    ->options([
                        'cuti_tahunan' => 'Cuti tahunan',
                        'cuti_sakit' => 'Cuti sakit',
                    ])
                    ->label('Jenis Cuti'),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (LeaveRequest $record) => static::canReview($record) && $record->status !== 'approved')
                    ->form([
                        Textarea::make('review_notes')
                        ->label('Catatan Review')
                        ->rows(3)
                    ])
                    ->action(function (LeaveRequest $record, array $data): void {
                        if (!static::canReview($record)) {
                            Notification::make()
                                ->title('Anda tidak memiliki akses untuk melakukan review.')
                                ->danger()
                                ->send();
                        }

                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => Auth::user()->id,
                            'reviewed_at' => now(),
                            'review_notes' => $data['review_notes'],
                        ]);
                    }),
                    Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (LeaveRequest $record) => static::canReview($record) && $record->status !== 'rejected')
                    ->form([
                        Textarea::make('review_notes')
                        ->label('Alasan Penolakan')
                        ->rows(4)
                    ])
                    ->action(function (LeaveRequest $record, array $data): void {
                        if (!static::canReview($record)) {
                            Notification::make()
                                ->title('Anda tidak memiliki akses untuk melakukan review.')
                                ->danger()
                                ->send();
                        }

                        $record->update([
                            'status' => 'rejected',
                            'reviewed_by' => Auth::user()->id,
                            'reviewed_at' => now(),
                            'review_notes' => $data['review_notes'],
                        ]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Determine if the current user can review (approve/reject) this leave request.
     * Aligns with LeaveRequestPolicy::approve() logic:
     * - Super Admin: always
     * - HR Manager: for operator/general_staff/supervisor level employees
     * - Director: all levels (final authorization)
     */
    private static function canReview(LeaveRequest $record): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (static::hasRoleInsensitive($user, ['super_admin'])) {
            return true;
        }

        if (static::hasRoleInsensitive($user, ['hr_manager'])) {
            $requester = $record->user;
            if ($requester && $requester->hasAnyRole(['hrd_it', 'employee'])) {
                return true;
            }
        }

        if (static::hasRoleInsensitive($user, ['director'])) {
            return true;
        }

        return false;
    }

    private static function hasRoleInsensitive(User $user, array $roleNames): bool
    {
        $ownedRoles = $user->roles()
            ->pluck('name')
            ->map(static fn(string $name):string => strtolower($name))
            ->all();
        $expectedRoles = array_map(static fn(string $name):string => strtolower($name), $roleNames);
        return count(array_intersect($ownedRoles, $expectedRoles)) > 0;
    }

    private static function resolveImageAttachment(LeaveRequest $record): ?string
    {
        if (blank($record->attachment)) {
            return null;
        }

        return static::isImagePath($record->attachment) ? $record->attachment : null;
    }

    private static function isImagePath(string $path): bool
    {
        return in_array(Str::lower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }
}
