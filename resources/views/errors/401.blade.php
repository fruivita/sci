{{--
    View para erro HTTP 401.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://laravel.com/docs/8.x/errors#custom-http-error-pages
    @see https://codepen.io/fixcl/pen/eYpmYj
--}}


@extends('layouts.error')


@section('title', __('error.401.title'))
@section('code', '401')
@section('message', __('error.401.message'))
