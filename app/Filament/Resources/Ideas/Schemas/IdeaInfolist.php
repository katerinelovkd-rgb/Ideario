<?php

namespace App\Filament\Resources\Ideas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class IdeaInfolist
{
      public static function configure(Schema $schema): Schema
    {
        return $schema 
             ->components([ 
                TextEntry::make('titulo')
                    ->label('Título'),

                TextEntry::make('descripcion')
                    ->label('Descripción')
                    ->columnSpanFull(), 

                TextEntry::make('estado')
                    ->label('Estado'),

                TextEntry::make('autor')
                    ->label('Autor'),

                TextEntry::make('categoria.nombre')
                    ->label('Categoría'),

                TextEntry::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
