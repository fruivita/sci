{{--
    View for HTTP 4xx error.

    Note: This view is used as the default view for errors in the range 400 ~
    499, that is, when there is no specific view to display the error.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://laravel.com/docs/8.x/errors#custom-http-error-pages
    @see https://codepen.io/fixcl/pen/eYpmYj
--}}


@extends('layouts.error')


@section('title', __('error.4xx.title'))
@section('code', $exception->getStatusCode())
@section('message', $exception->getMessage() ?: __('error.4xx.message'))
