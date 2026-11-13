<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CategoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema


            ->components([
                TextInput::make('nombre')
                    ->label('Nmbre de categoría')
                    ->helperText("Ingresa el nombre de la categoria")
                    ->placeholder ('Ej Categoria')
                    ->unique()
                    ->required()
                    ->validationMessages ([
                        'unique' => 'Ya existe una categoría  con ese nombre'
                     ]),
                Textarea::make('descripcion')
                     ->required()
                    ->helperText("Ingresa una descripción completa")
                    ->label('Descripción')
                    ->columnSpanFull(),
            ]);
    }
}
