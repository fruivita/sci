{{--
    Subview to display the print per server report in pdf.

    @see https://laravel.com/docs/blade
    @see https://github.com/barryvdh/laravel-dompdf
    @see https://github.com/dompdf/dompdf
--}}

@extends('layouts.pdf')


@section('content')


<div class="content">

    {{-- report display table --}}
    <table style="table-layout:fixed;">

        <thead>

            <tr>

                <th style="width:20%;overflow-wrap:break-word;">{{ __('Sites') }}</th>


                <th style="width:20%;overflow-wrap:break-word;">{{ __('Servers') }}</th>


                <th style="width:20%;overflow-wrap:break-word;">{{ __('Print volume') }}</th>


                <th style="width:20%;overflow-wrap:break-word;">{{ __('Printers used') }}</th>


                <th style="width:20%;overflow-wrap:break-word;">{{ __('Percentage') }}</th>

            </tr>

        </thead>


        <tbody>

            @forelse ($result ?? [] as $row)

                <tr>

                    <td style="width:20%;overflow-wrap:break-word;">{{ $row->site }}</td>


                    <td style="width:20%;overflow-wrap:break-word;">{{ $row->server }}</td>


                    <td style="width:20%;overflow-wrap:break-word;">{{ $row->total_print ?? 0 }}</td>


                    <td style="width:20%;overflow-wrap:break-word;">{{ $row->printer_count ?? 0 }}</td>


                    <td style="width:20%;overflow-wrap:break-word;">{{ $row->percentage ?? 0 }}</td>

                </tr>

            @empty

                <tr><td colspan="5">{{ __('No record found') }}</td></tr>

            @endforelse

        </tbody>

    </table>

</div>


@endsection
