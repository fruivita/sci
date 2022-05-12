{{--
    View for HTTP 419 error.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://laravel.com/docs/8.x/errors#custom-http-error-pages
    @see https://codepen.io/fixcl/pen/eYpmYj
--}}


@extends('layouts.error')


@section('title', __('error.419.title'))
@section('code', '419')
@section('message', __('error.419.message'))
