<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'usuario';

    protected static ?string $pluralModelLabel = 'usuarios';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('rol')
                    ->label('Rol')
                    ->options([
                        'admin' => 'Administrador',
                        'coordinador' => 'Coordinador',
                        'asistente' => 'Asistente',
                    ])
                    ->default('asistente')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable(),

                TextColumn::make('rol')
                    ->label('Rol')
                    ->badge()
                    ->placeholder('Sin rol'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Cambiar rol'),
            ]);
    }

    public static function getViewAnyAuthorizationResponse(): Response
    {
        return Filament::auth()->user()?->rol === 'admin'
            ? Response::allow()
            : Response::deny(
                'Solo los administradores pueden gestionar usuarios.'
            );
    }

    public static function getEditAuthorizationResponse(
        Model $record
    ): Response {
        return static::getViewAnyAuthorizationResponse();
    }

    public static function getCreateAuthorizationResponse(): Response
    {
        return Response::deny();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}