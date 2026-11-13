<?php

namespace App\Filament\Resources\Ideas\Schemas;

use App\Helpers\EstadoIdea;
use App\Models\Idea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;

class IdeaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('titulo')
                ->label('Título de la idea')
                ->helperText('Escribe un título que sea ilustrativo y breve.')
                ->placeholder('Ej. Aplicación de comida')
                ->unique(ignoreRecord: true)
                ->required()
                ->validationMessages([
                    'unique' => 'Ya existe una idea registrada con ese título.',
                ]),

            Textarea::make('descripcion')
                ->label('Describe tu idea')
                ->helperText('Escribe a detalle en qué consiste la idea del proyecto.')
                ->required()
                ->columnSpanFull(),

            TextInput::make('autor')->label('Autor')->required(),

            Select::make('categoria_id')
                ->label('Categoría')
                ->relationship('categoria', 'nombre')
                ->required(),

            Select::make('estado')
                ->label('Estado')
                ->options(EstadoIdea::opciones())
                ->default('registrada')
                ->hiddenOn('create')
                ->disabled(fn (?Idea $record): bool => Gate::denies('cambiarEstado', $record ?? Idea::class))
                ->dehydrated(fn (?Idea $record): bool => Gate::allows('cambiarEstado', $record ?? Idea::class))
                ->required()
                ->live(),

            Textarea::make('observaciones')
                ->label('Motivo de rechazo')
                ->visible(fn (Get $get): bool => $get('estado') === 'rechazado')
                ->disabled(fn (?Idea $record): bool => Gate::denies('cambiarEstado', $record ?? Idea::class))
                ->dehydrated(fn (?Idea $record): bool => Gate::allows('cambiarEstado', $record ?? Idea::class))
                ->maxLength(200)
                ->required(),
        ]);
    }
}
