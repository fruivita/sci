{{--
    View para erro HTTP 503.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://laravel.com/docs/8.x/errors#custom-http-error-pages
    @see https://codepen.io/fixcl/pen/eYpmYj
--}}


@extends('layouts.error')


@section('title', __('error.503.title'))
@section('code', '503')
@section('message', __('error.503.message'))
