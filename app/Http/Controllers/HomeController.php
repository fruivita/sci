<?php

namespace App\Http\Controllers;

/**
 * @see https://laravel.com/docs/controllers
 */
class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->view('home');
    }
}
