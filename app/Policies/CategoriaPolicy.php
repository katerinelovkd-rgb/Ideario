<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\User;

class CategoriaPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol, ['admin', 'coordinador', 'asistente'], true);
    }

    public function view(User $user, Categoria $categoria): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->rol === 'admin';
    }

    public function update(User $user, Categoria $categoria): bool
    {
        return in_array($user->rol, ['admin', 'asistente'], true);
    }

    public function delete(User $user, Categoria $categoria): bool
    {
        return $this->deleteAny($user);
    }

    public function deleteAny(User $user): bool
    {
        return $user->rol === 'admin';
    }
}
