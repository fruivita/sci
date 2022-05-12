{{--
    Subview to display the print report by department in pdf.

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

                <th style="width:30%;overflow-wrap:break-word;">{{ __('Department') }}</th>


                <th style="width:15%;overflow-wrap:break-word;">{{ __('Acronym') }}</th>


                <th style="width:20%;overflow-wrap:break-word;">{{ __('Print volume') }}</th>


                <th style="width:20%;overflow-wrap:break-word;">{{ __('Printers used') }}</th>


                <th style="width:15%;overflow-wrap:break-word;">{{ __('Parent department') }}</th>

            </tr>

        </thead>


        <tbody>

            @forelse ($result ?? [] as $row)

                <tr>

                    <td style="width:30%;overflow-wrap:break-word;">{{ $row->department }}</td>


                    <td style="width:15%;overflow-wrap:break-word;">{{ $row->acronym }}</td>


                    <td style="width:20%;overflow-wrap:break-word;">{{ $row->total_print ?? 0 }}</td>


                    <td style="width:20%;overflow-wrap:break-word;">{{ $row->printer_count ?? 0 }}</td>


                    <td style="width:15%;overflow-wrap:break-word;">{{ $row->parent_acronym }}</td>

                </tr>

            @empty

                <tr><td colspan="5">{{ __('No record found') }}</td></tr>

            @endforelse

        </tbody>

    </table>

</div>


@endsection
