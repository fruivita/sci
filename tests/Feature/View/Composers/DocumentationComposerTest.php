<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Documentation;
use App\View\Composers\DocumentationComposer;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

 // Invalid
 test('retorna o link padrão se o nome da rota não for encontrada no banco de dados', function () {
     $composer = new DocumentationComposer();

     $view = $this->spy(View::class);

     $composer->compose($view);

     $view
        ->shouldHaveReceived('with')
        ->with(['doc_link' => config('app.doc_link_default')])
        ->once();
 });

 test('retorna o link padrão se a rota estiver cadastrada, porém, sem link definido para documentação', function () {
     Documentation::factory()->create([
        'app_route_name' => 'report.printing.create',
        'doc_link' => null,
    ]);

     Route::shouldReceive('currentRouteName')
    ->once()
    ->andReturn('report.printing.create');

     $composer = new DocumentationComposer();

     $view = $this->spy(View::class);

     $composer->compose($view);

     $view
        ->shouldHaveReceived('with')
        ->with(['doc_link' => config('app.doc_link_default')])
        ->once();
 });

// Happy path
 test('retorna o link para  se a rota estiver cadastrada, porém, sem link definido para documentação', function () {
     Documentation::factory()->create([
        'app_route_name' => 'report.printing.create',
        'doc_link' => 'http://exemplo.com',
    ]);

     Route::shouldReceive('currentRouteName')
    ->once()
    ->andReturn('report.printing.create');

     $composer = new DocumentationComposer();

     $view = $this->spy(View::class);

     $composer->compose($view);

     $view
        ->shouldHaveReceived('with')
        ->with(['doc_link' => 'http://exemplo.com'])
        ->once();
 });
