{{--
    View para erro HTTP 4xx.

    Nota: Esse view é usada como view default para erros do range 400 ~ 499,
    isto é, para quando não houver uma view específica para exibir o erro.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://laravel.com/docs/8.x/errors#custom-http-error-pages
    @see https://codepen.io/fixcl/pen/eYpmYj
--}}


@extends('layouts.error')


@section('title', __('error.4xx.title'))
@section('code', $exception->getStatusCode())
@section('message', $exception->getMessage() ?: __('error.4xx.message'))
