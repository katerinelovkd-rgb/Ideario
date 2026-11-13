<?php

namespace App\Policies;

use App\Models\Idea;
use App\Models\User;

class IdeaPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol, ['admin', 'coordinador', 'asistente'], true);
    }

    public function view(User $user, Idea $idea): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->rol === 'admin';
    }

    public function update(User $user, Idea $idea): bool
    {
        return ! $idea->trashed()
            && in_array($user->rol, ['admin', 'asistente'], true);
    }

    public function delete(User $user, Idea $idea): bool
    {
        return ! $idea->trashed() && $this->deleteAny($user);
    }

    public function deleteAny(User $user): bool
    {
        return in_array($user->rol, ['admin', 'asistente'], true);
    }

    public function restore(User $user, Idea $idea): bool
    {
        return $this->restoreAny($user);
    }

    public function restoreAny(User $user): bool
    {
        return in_array($user->rol, ['admin', 'asistente'], true);
    }

    public function forceDelete(User $user, Idea $idea): bool
    {
        return $this->forceDeleteAny($user);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->rol === 'admin';
    }

    public function cambiarEstado(User $user, ?Idea $idea = null): bool
    {
        return ! $idea?->trashed()
            && in_array($user->rol, ['admin', 'coordinador'], true);
    }
}
