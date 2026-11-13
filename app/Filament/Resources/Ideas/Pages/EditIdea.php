<?php

namespace App\Filament\Resources\Ideas\Pages;

use App\Filament\Resources\Ideas\IdeaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class EditIdea extends EditRecord
{
    protected static string $resource = IdeaResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        Gate::authorize('update', $record);

        foreach (['estado', 'observaciones'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== $record->getAttribute($field)) {
                Gate::authorize('cambiarEstado', $record);
            }
        }

        return parent::handleRecordUpdate($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Idea creada exitosamente';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
