<?php

use App\Models\Categoria;
use App\Models\Idea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
uses(RefreshDatabase::class);


test('utilizar base de datos de prueba', function () {
    expect(config('database.default'))->toBe('sqlite');
    expect(config('database.connections.sqlite.database'))->toBe(':memory:');
});

//dd(Categoria::all());

test('registrar una categoria', function () {
    Categoria::create([
        'nombre' => 'Ciencia',
        'descripcion' => 'Idea de ciencias',
    ]);

    $this->assertDatabaseHas('categorias', [
        'nombre' => 'Ciencia',
        'descripcion' => 'Idea de ciencias',
    ]);
});


test('consultar una categoria', function () {
    $categoria = Categoria::create([
        'nombre' => 'Ciencia',
        'descripcion' => 'Idea de ciencias',
    ]);

    $encontrada = Categoria::find($categoria->id);

    expect($encontrada->nombre)->toBe('Ciencia');
});



test('modificar una categoria', function () {
    $categoria = Categoria::create([
        'nombre' => 'Ciencia',
        'descripcion' => 'Idea de ciencias',
    ]);

    $categoria->update([
        'nombre' => 'Innovación',
    ]);

    $this->assertDatabaseHas('categorias', [
        'id' => $categoria->id,
        'nombre' => 'Innovación',
    ]);

     $this->assertDatabaseMissing('categorias', [
        'id' => $categoria->id,
        'nombre' => 'Ciencia',
    ]);
});
  


test('eliminar una categoria', function () {
    $categoria = Categoria::create([
        'nombre' => 'Ciencia',
        'descripcion' => 'Idea de ciencias',
    ]);

    $categoria->delete();



     $this->assertDatabaseMissing('categorias', [
        'id' => $categoria->id,
    ]);
});
  

test('una categoría puede tener varias ideas', function () {
  $categoria = Categoria::create([
    'nombre' => 'Tecnología',
    'descripcion' => 'Ideas tecnológicas'
  ]);

  Idea::create([
    'titulo' => 'Aplicación de transporte',
    'descripcion' => 'Aplicación para compartir viajes',
    'autor' => 'Ana',
    'estado' => 'registrada',
    'categoria_id' => $categoria->id
  ]);

  Idea::create([
    'titulo' => 'Sistema de inventario',
    'descripcion' => 'Control de productos',
    'autor' => 'Luis',
    'estado' => 'registrada',
    'categoria_id' => $categoria->id
  ]);

 	expect($categoria->ideas)->toHaveCount(2);
});



test('no se puede borrar una categoria con ideas asociadas', function () {
  $categoria = Categoria::create([
    'nombre' => 'Tecnología',
    'descripcion' => 'Ideas tecnológicas'
  ]);

  Idea::create([
    'titulo' => 'Aplicación de transporte',
    'descripcion' => 'Aplicación para compartir viajes',
    'autor' => 'Ana',
    'estado' => 'registrada',
    'categoria_id' => $categoria->id
  ]);

  Idea::create([
    'titulo' => 'Sistema de inventario',
    'descripcion' => 'Control de productos',
    'autor' => 'Luis',
    'estado' => 'registrada',
    'categoria_id' => $categoria->id
  ]);

    $seRechazo = False;
  try{
    $categoria->delete();
  }catch(QueryException $e){
    // la base de datos impidio la eliminación
     $seRechazo = True;
  }

  expect($seRechazo)->toBeTrue();

    $this->assertDatabaseHas('categorias', [
        'id' => $categoria->id
    ]);
});