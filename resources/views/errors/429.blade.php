{{--
    View para erro HTTP 429.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://laravel.com/docs/8.x/errors#custom-http-error-pages
    @see https://codepen.io/fixcl/pen/eYpmYj
--}}


@extends('layouts.error')


@section('title', __('error.429.title'))
@section('code', '429')
@section('message', __('error.429.message'))
