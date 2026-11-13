<?php

namespace App\Filament\Resources\Categorias\Tables;

use App\Models\Categoria;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('id')
                    ->label('ID'),
                TextColumn::make('nombre')
                    ->label('Categoria')
                    ->searchable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizando en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //En esto a la parte de vistas que tenemos que entregar
                EditAction::make(),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->requiresConfirmation()
                    ->hidden(fn (Categoria $record): bool => $record->ideas()->withTrashed()->exists()),
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->toolbarActions([
                BulkActionGroup::make([
                    //DeleteBulkAction::make(),
                ]),
            ]);
    }
}
