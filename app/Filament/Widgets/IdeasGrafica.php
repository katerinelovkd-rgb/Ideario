<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Idea;
use App\Models\Categoria;

class IdeasGrafica extends ChartWidget
{
    protected ?string $heading = 'Ideas Grafica';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
         $registradas = Idea::where('estado', 'registrada')->count();
        $revision = Idea::where('estado', 'en revision')->count();
        $aprobadas = Idea::where('estado', 'aprobada')->count();
        $rechazadas = Idea::where('estado', 'rechazado')->count();
       return [
            'datasets' => [
                [
                    'label' => 'Estados',
                    'data' => [$registradas, $revision, $aprobadas, $rechazadas],
                    'backgroundColor' => ('#3B82F6')
                    ],
            ],
            'labels' => ['Registrada', 'En revision', 'Aprobada', 'Rechazada'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
