<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Idea;
use Illuminate\Database\Seeder;

class IdeaSeeder extends Seeder
{
    public function run(): void
    {
        // Categoría, título, descripción, estado, autor y observaciones.
        $ideas = [
            ['Tecnología', 'Aplicación para organizar citas veterinarias', 'Sistema para registrar y organizar citas de mascotas.', 'registrada', 'Jose', null],
            ['Tecnología', 'Sistema de préstamo de equipos de cómputo', 'Registrar préstamos y devoluciones de computadoras.', 'aprobado', 'Pedro', null],
            ['Tecnología', 'Chatbot para resolver dudas del plantel', 'Responder dudas sobre trámites y servicios escolares.', 'en revision', 'Katerine', null],
            ['Educación', 'Tutorías entre estudiantes', 'Ofrecer asesorías de apoyo para distintas materias.', 'aprobado', 'Jose', null],
            ['Educación', 'Plataforma para cursos en línea', 'Sistema para ofrecer cursos y materiales educativos.', 'en revision', 'Pedro', null],
            ['Educación', 'Regularización obligatoria los domingos', 'Ofrecer clases dominicales de regularización.', 'rechazado', 'Katerine', 'El horario dificulta la asistencia; se proponen horarios entre semana.'],
            ['Salud', 'Recordatorios de pausas activas', 'Programar pausas de movilidad y descanso.', 'en revision', 'Jose', null],
            ['Salud', 'Campaña de hidratación en el plantel', 'Difundir los puntos de agua potable del plantel.', 'aprobado', 'Pedro', null],
            ['Salud', 'Jornadas de bienestar durante los exámenes', 'Ofrecer actividades informativas de bienestar.', 'rechazado', 'Katerine', 'No hay personal disponible durante los exámenes; se propone otra fecha.'],
            ['Medio ambiente', 'Estaciones de separación de residuos', 'Instalar contenedores para separar residuos.', 'aprobado', 'Jose', null],
            ['Medio ambiente', 'Captación de agua de lluvia para riego', 'Aprovechar el agua pluvial para las áreas verdes.', 'en revision', 'Pedro', null],
            ['Medio ambiente', 'Recolección de pilas usadas', 'Organizar puntos de acopio y entrega de pilas.', 'registrada', 'Katerine', null],
            ['Deportes', 'Aplicación para organizar torneos deportivos', 'Sistema para registrar equipos y programar partidos.', 'registrada', 'Katerine', null],
            ['Deportes', 'Club de caminata y actividad física', 'Organizar caminatas y actividades recreativas.', 'en revision', 'Pedro', null],
            ['Deportes', 'Construcción de una alberca en el plantel', 'Construir una alberca para clases de natación.', 'rechazado', 'Katerine', 'No hay presupuesto ni espacio disponible; se proponen convenios externos.'],
            ['Alimentación', 'Menú semanal con opciones equilibradas', 'Publicar opciones de alimentos y sus precios.', 'aprobado', 'Jose', null],
            ['Alimentación', 'Información de ingredientes y alérgenos', 'Mostrar los ingredientes de los alimentos de cafetería.', 'en revision', 'Pedro', null],
            ['Alimentación', 'Huerto escolar de hierbas comestibles', 'Cultivar hierbas para actividades educativas.', 'registrada', 'Katerine', null],
        ];

        foreach ($ideas as [$nombre, $titulo, $descripcion, $estado, $autor, $observaciones]) {
            $categoria = Categoria::where('nombre', $nombre)->firstOrFail();

            Idea::firstOrCreate(
                ['categoria_id' => $categoria->id, 'titulo' => $titulo],
                [
                    'descripcion' => $descripcion,
                    'estado' => $estado,
                    'autor' => $autor,
                    'observaciones' => $observaciones,
                ]
            );
        }
    }
}