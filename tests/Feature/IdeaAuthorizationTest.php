<?php

use App\Filament\Resources\Ideas\IdeaResource;
use App\Filament\Resources\Ideas\Pages\CreateIdea;
use App\Filament\Resources\Ideas\Pages\EditIdea;
use App\Filament\Resources\Ideas\Pages\ListIdeas;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\Categoria;
use App\Models\Idea;
use App\Models\User;
use App\Policies\IdeaPolicy;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('x', 32))]);
    Filament::setCurrentPanel('admin');
});

function idearioIdea(array $attributes = []): Idea
{
    $category = Categoria::create(['nombre' => 'Tecnología']);

    return Idea::create([
        'titulo' => 'Biblioteca digital',
        'descripcion' => 'Consultar materiales de estudio.',
        'autor' => 'Equipo Ideario',
        'categoria_id' => $category->getKey(),
        'estado' => 'registrada',
        ...$attributes,
    ]);
}

test('registers the idea policy', function (): void {
    expect(Gate::getPolicyFor(Idea::class))->toBeInstanceOf(IdeaPolicy::class);
});

test('enforces each permission for each role', function (?string $role, string $ability, bool $allowed): void {
    $user = User::factory()->make(['rol' => $role]);
    $idea = new Idea;
    $arguments = in_array($ability, ['viewAny', 'create', 'deleteAny', 'restoreAny', 'forceDeleteAny'], true)
        ? Idea::class
        : $idea;

    expect(Gate::forUser($user)->allows($ability, $arguments))->toBe($allowed);
})->with(function (): array {
    $abilities = ['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny', 'restore', 'restoreAny', 'forceDelete', 'forceDeleteAny', 'cambiarEstado'];
    $grants = [
        'admin' => $abilities,
        'coordinador' => ['viewAny', 'view', 'cambiarEstado'],
        'asistente' => ['viewAny', 'view', 'update', 'delete', 'deleteAny', 'restore', 'restoreAny'],
        'desconocido' => [],
        'sin rol' => [],
    ];
    $cases = [];

    foreach ($grants as $role => $permissions) {
        foreach ($abilities as $ability) {
            $cases[$role.' / '.$ability] = [$role === 'sin rol' ? null : $role, $ability, in_array($ability, $permissions, true)];
        }
    }

    return $cases;
});

test('redirects guests to login', function (): void {
    $this->get(IdeaResource::getUrl('index'))->assertRedirect(route('filament.admin.auth.login'));
});

test('denies the ideas page to a user without a recognized role with 403', function (?string $role): void {
    $user = User::factory()->create(['rol' => $role]);

    $this->actingAs($user)->get(IdeaResource::getUrl('index'))->assertForbidden();
})->with(['sin rol' => [null], 'rol desconocido' => ['invitado']]);

test('allows each recognized role to enter the ideas page', function (string $role): void {
    $user = User::factory()->create(['rol' => $role]);

    $this->actingAs($user)->get(IdeaResource::getUrl('index'))->assertOk();
})->with(['admin', 'coordinador', 'asistente']);

test('denies direct creation to non administrators with 403', function (string $role): void {
    $user = User::factory()->create(['rol' => $role]);

    $this->actingAs($user)->get(IdeaResource::getUrl('create'))->assertForbidden();
})->with(['coordinador', 'asistente']);

test('denies direct editing to the coordinator with 403', function (): void {
    $user = User::factory()->create(['rol' => 'coordinador']);
    $idea = idearioIdea();

    $this->actingAs($user)->get(IdeaResource::getUrl('edit', ['record' => $idea]))->assertForbidden();
});

test('allows the administrator to create an idea in registered state', function (): void {
    $user = User::factory()->create(['rol' => 'admin']);
    $category = Categoria::create(['nombre' => 'Tecnología']);
    $this->actingAs($user);

    Livewire::test(CreateIdea::class)
        ->fillForm(['titulo' => 'Huerto escolar', 'descripcion' => 'Cultivar plantas.', 'autor' => 'Equipo Ideario', 'categoria_id' => $category->getKey()])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('ideas', ['titulo' => 'Huerto escolar', 'estado' => 'registrada', 'observaciones' => null]);
});

test('shows review actions and hides editing and deletion for the coordinator', function (): void {
    $user = User::factory()->create(['rol' => 'coordinador']);
    $idea = idearioIdea();
    $this->actingAs($user);

    Livewire::test(ListIdeas::class)
        ->assertTableActionVisible('aprobar', $idea)
        ->assertTableActionVisible('rechazar', $idea)
        ->assertTableActionHidden('edit', $idea)
        ->assertTableActionHidden('delete', $idea)
        ->assertTableActionHidden('forceDelete', $idea);
});

test('hides approval and rejection from the assistant', function (): void {
    $user = User::factory()->create(['rol' => 'asistente']);
    $idea = idearioIdea();
    $this->actingAs($user);

    Livewire::test(ListIdeas::class)
        ->assertTableActionHidden('aprobar', $idea)
        ->assertTableActionHidden('rechazar', $idea)
        ->assertTableActionVisible('edit', $idea)
        ->assertTableActionVisible('delete', $idea);
});

test('allows the coordinator to approve an idea and clears old observations', function (): void {
    $user = User::factory()->create(['rol' => 'coordinador']);
    $idea = idearioIdea(['estado' => 'rechazado', 'observaciones' => 'Revisar propuesta.']);
    $this->actingAs($user);

    Livewire::test(ListIdeas::class)->callTableAction('aprobar', $idea)->assertNotified();

    $this->assertDatabaseHas('ideas', ['id' => $idea->getKey(), 'estado' => 'aprobado', 'observaciones' => null]);
});

test('allows the coordinator to reject an idea with a saved reason', function (): void {
    $user = User::factory()->create(['rol' => 'coordinador']);
    $idea = idearioIdea();
    $this->actingAs($user);

    Livewire::test(ListIdeas::class)
        ->callTableAction('rechazar', $idea, data: ['observaciones' => 'Falta detallar el presupuesto.'])
        ->assertNotified();

    $this->assertDatabaseHas('ideas', ['id' => $idea->getKey(), 'estado' => 'rechazado', 'observaciones' => 'Falta detallar el presupuesto.']);
});

test('requires a rejection reason and leaves the idea unchanged on failure', function (): void {
    $user = User::factory()->create(['rol' => 'coordinador']);
    $idea = idearioIdea();
    $this->actingAs($user);

    Livewire::test(ListIdeas::class)
        ->callTableAction('rechazar', $idea, data: ['observaciones' => ''])
        ->assertHasActionErrors(['observaciones' => 'required']);

    $this->assertDatabaseHas('ideas', ['id' => $idea->getKey(), 'estado' => 'registrada', 'observaciones' => null]);
});

test('lets the assistant edit content while preserving the state and rejection reason', function (): void {
    $user = User::factory()->create(['rol' => 'asistente']);
    $idea = idearioIdea(['estado' => 'rechazado', 'observaciones' => 'Revisar alcance.']);
    $this->actingAs($user);

    Livewire::test(EditIdea::class, ['record' => $idea->getKey()])
        ->assertFormFieldIsDisabled('estado')
        ->fillForm(['descripcion' => 'Descripción corregida.', 'estado' => 'aprobado', 'observaciones' => 'Intento no autorizado.'])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('ideas', ['id' => $idea->getKey(), 'descripcion' => 'Descripción corregida.', 'estado' => 'rechazado', 'observaciones' => 'Revisar alcance.']);
});

test('denies a forged state change from the assistant with 403', function (): void {
    $user = User::factory()->create(['rol' => 'asistente']);
    $idea = idearioIdea();
    $this->actingAs($user);

    Livewire::test(ForgedStateEditIdea::class, ['record' => $idea->getKey()])
        ->call('save')
        ->assertForbidden();

    $this->assertDatabaseHas('ideas', ['id' => $idea->getKey(), 'estado' => 'registrada']);
});

test('allows the administrator to set the state through the edit form', function (): void {
    $user = User::factory()->create(['rol' => 'admin']);
    $idea = idearioIdea();
    $this->actingAs($user);

    Livewire::test(EditIdea::class, ['record' => $idea->getKey()])
        ->assertFormFieldIsEnabled('estado')
        ->fillForm(['estado' => 'aprobado'])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('ideas', ['id' => $idea->getKey(), 'estado' => 'aprobado']);
});

test('allows the assistant to soft delete an idea', function (): void {
    $user = User::factory()->create(['rol' => 'asistente']);
    $idea = idearioIdea();
    $this->actingAs($user);

    Livewire::test(EditIdea::class, ['record' => $idea->getKey()])->callAction(DeleteAction::class)->assertRedirect();

    $this->assertSoftDeleted($idea);
});

test('allows the assistant to restore and hides permanent deletion', function (): void {
    $user = User::factory()->create(['rol' => 'asistente']);
    $idea = idearioIdea();
    $idea->delete();
    $this->actingAs($user);

    Livewire::test(ListIdeas::class)
        ->filterTable('trashed', true)
        ->assertTableActionVisible(RestoreAction::class, $idea)
        ->assertTableActionHidden(ForceDeleteAction::class, $idea)
        ->callTableAction(RestoreAction::class, $idea);

    expect($idea->fresh()->trashed())->toBeFalse();
});

test('allows permanent deletion only from the administrator table', function (): void {
    $user = User::factory()->create(['rol' => 'admin']);
    $idea = idearioIdea();
    $idea->delete();
    $this->actingAs($user);

    Livewire::test(ListIdeas::class)
        ->filterTable('trashed', true)
        ->callTableAction(ForceDeleteAction::class, $idea);

    $this->assertModelMissing($idea);
});

test('denies user management to non administrators with 403', function (string $role): void {
    $user = User::factory()->create(['rol' => $role]);

    $this->actingAs($user)->get(UserResource::getUrl('index'))->assertForbidden();
})->with(['coordinador', 'asistente']);

test('does not allow changing the state of a deleted idea', function (): void {
    $user = User::factory()->create(['rol' => 'coordinador']);
    $idea = idearioIdea();
    $idea->delete();

    expect(Gate::forUser($user)->allows('cambiarEstado', $idea))->toBeFalse();
});

test('allows the administrator to assign a role from user management', function (): void {
    $administrator = User::factory()->create(['rol' => 'admin']);
    $target = User::factory()->create(['rol' => 'asistente']);
    $this->actingAs($administrator);

    Livewire::test(ManageUsers::class)
        ->callTableAction('edit', $target, data: ['rol' => 'coordinador'])
        ->assertNotified();

    $this->assertDatabaseHas('users', ['id' => $target->getKey(), 'rol' => 'coordinador']);
});

class ForgedStateEditIdea extends EditIdea
{
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['estado'] = 'aprobado';

        return $data;
    }
}
