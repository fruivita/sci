{{--
  Subview para exibir o relatório geral de impressão em pdf.

  @link https://laravel.com/docs/9.x/blade
  @link https://github.com/barryvdh/laravel-dompdf
  @link https://github.com/dompdf/dompdf
--}}

@extends('layouts.pdf')


@section('content')


<div class="content">

    {{--Tabela para exibição do relatório--}}
    <table style="table-layout:fixed;">

        <thead>

            <tr>

                <th style="width:50%;overflow-wrap:break-word;">{{ __('Grouping') }}</th>


                <th style="width:25%;overflow-wrap:break-word;">{{ __('Print volume') }}</th>


                <th style="width:25%;overflow-wrap:break-word;">{{ __('Printers used') }}</th>

            </tr>

        </thead>


        <tbody>

            @forelse ($result ?? [] as $row)

                <tr>

                    <td style="width:50%;overflow-wrap:break-word;">{{ $row->grouping_for_humans }}</td>


                    <td style="width:25%;overflow-wrap:break-word;">{{ $row->total_print }}</td>


                    <td style="width:25%;overflow-wrap:break-word;">{{ $row->printer_count }}</td>

                </tr>

            @empty

                <tr><td colspan="3">{{ __('No record found') }}</td></tr>

            @endforelse

        </tbody>

    </table>

</div>


@endsection
