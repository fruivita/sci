<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Documentation;
use App\View\Composers\DocumentationComposer;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

 // Invalid
 test('returns the default link if the route name is not found in the database', function () {
     $composer = new DocumentationComposer();

     $view = $this->spy(View::class);

     $composer->compose($view);

     $view
        ->shouldHaveReceived('with')
        ->with(['doc_link' => config('app.doc_link_default')])
        ->once();
 });

 test('returns the default link if the route is created, however, without a defined link for documentation', function () {
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
 test('returns the link for if the route is created, however, without a defined link for documentation', function () {
     Documentation::factory()->create([
        'app_route_name' => 'report.printing.create',
        'doc_link' => 'http://foo.com',
    ]);

     Route::shouldReceive('currentRouteName')
    ->once()
    ->andReturn('report.printing.create');

     $composer = new DocumentationComposer();

     $view = $this->spy(View::class);

     $composer->compose($view);

     $view
        ->shouldHaveReceived('with')
        ->with(['doc_link' => 'http://foo.com'])
        ->once();
 });
