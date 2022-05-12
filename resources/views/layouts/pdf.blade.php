{{--
  Master Page for PDF reports.

  @see https://laravel.com/docs/blade
  @see https://github.com/barryvdh/laravel-dompdf
  @see https://github.com/dompdf/dompdf
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        {{--CSS 2.0--}}
        <link href="{{ public_path(mix('css/pdf.css')) }}" rel="stylesheet">

        <title>{{ config('app.name') }}</title>
    </head>


    <body>

        {{-- report header --}}
        <div class="header">

            <img src="{{ resource_path('svg/colored-republic.svg') }}" alt="Logo">


            <h4>{{ config('company.top_name') }}</h4>


            <h4>{{ config('company.middle_name') }}</h4>


            <h5>{{ config('company.botton_name') }}</h5>


            <hr>


            <h4>{{ $header }}</h4>


            <h5>{{ __('Interval: :attribute1 to :attribute2', ['attribute1' => $initial_date, 'attribute2' => $final_date]) }}</h5>


            <h5>{{ __('Filter: :attribute', ['attribute' => (isset($filter) && str($filter)->length() >= 1) ? $filter : __('None')]) }}</h5>


            @unless (\App::environment('production') && session()->missing('simulator'))

                <div id="water-mark">

                {{ str(__('Example'))->upper() }}

                </div>

            @endunless

        </div>


        {{-- report footer --}}
        <div class="footer">

            <p>{{ __('Page') }} <span class="page"></span></p>


            <p>

                {{
                    __('Report generated at :attribute1 by :attribute2',
                    [
                        'attribute1' => now()->format('d/m/Y H:i:s'),
                        'attribute2' => auth()->user()->name
                                ?? auth()->user()->forHumans()
                    ])
                }}


                @if (session()->has('simulator'))

                    {{ '- ' .  __('Simulation activated by :attribute', ['attribute' => str()->upper(session('simulator')->username)]) }}

                @endif

            </p>


            <p>{{ config('app.full_name') }} - v.{{ config('app.version') }} - {{ __(str()->ucfirst(\App::environment())) }}</p>

        </div>


    @yield('content')


    </body>

</html>
