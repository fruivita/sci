{{--
    View para erro HTTP 403.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://laravel.com/docs/8.x/errors#custom-http-error-pages
    @see https://codepen.io/fixcl/pen/eYpmYj
--}}


@extends('layouts.error')


@section('title', __('error.403.title'))
@section('code', '403')
@section('message', __('error.403.message'))
