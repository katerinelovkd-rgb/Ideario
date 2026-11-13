<?php

namespace App\Helpers;

class EstadoIdea
{
    public static function opciones(): array
    {
        return [
            'registrada' => 'Registrada',
            'en revision' => 'En revisión',
            'aprobado' => 'Aprobada',
            'rechazado' => 'Rechazada',
        ];
    }
}
