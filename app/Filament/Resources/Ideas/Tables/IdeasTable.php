<?php

namespace App\Filament\Resources\Ideas\Tables;

use App\Models\Idea;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class IdeasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('titulo')
                    ->label('Idea')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('autor')
                    ->label('Autor')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'registrada' => 'Registrada',
                        'en revision' => 'En revisión',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),

                Action::make('aprobar')
                    ->authorize('cambiarEstado')
                    ->label('Aprobar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar idea')
                    ->modalDescription('¿Deseas aprobar esta idea?')
                    ->modalSubmitActionLabel('Aprobar')
                    ->visible(fn (Idea $record): bool => ! $record->trashed()
                        && $record->estado !== 'aprobado'
                    )
                    ->action(function (Idea $record): void {
                        Gate::authorize('cambiarEstado', $record);

                        $record->estado = 'aprobado';
                        $record->observaciones = null;
                        $record->save();

                        Notification::make()
                            ->title('Idea aprobada correctamente')
                            ->success()
                            ->send();
                    }),

                Action::make('rechazar')
                    ->authorize('cambiarEstado')
                    ->label('Rechazar')
                    ->color('danger')
                    ->modalHeading('Rechazar idea')
                    ->modalDescription('Escribe el motivo del rechazo.')
                    ->modalSubmitActionLabel('Rechazar')
                    ->schema([
                        Textarea::make('observaciones')
                            ->label('Motivo del rechazo')
                            ->required()
                            ->maxLength(200)
                            ->rows(4),
                    ])
                    ->visible(fn (Idea $record): bool => ! $record->trashed()
                        && $record->estado !== 'rechazado'
                    )
                    ->action(function (Idea $record, array $data): void {
                        Gate::authorize('cambiarEstado', $record);

                        $record->estado = 'rechazado';
                        $record->observaciones = $data['observaciones'];
                        $record->save();

                        Notification::make()
                            ->title('Idea rechazada correctamente')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->recordUrl(null)
            ->recordAction(null)

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id')
            ->defaultPaginationPageOption(10);
    }
}
