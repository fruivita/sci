<?php

namespace App\Http\Livewire\Traits;

use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Trait para as tratativas de download do relatório.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithDownloadableReport
{
    /**
     * Título do relatório que será gerado.
     *
     * @return string
     */
    abstract private function reportHeader();

    /**
     * Nome da view utilizada para a geração do relatório.
     *
     * @return string
     */
    abstract private function reportViewName();

    /**
     * Relatório paginado, de acordo com as solicitações do usuário.
     *
     * @param int|null $per_page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    abstract private function makeReport(int $per_page = null);

    /**
     * Download do relatório em formatro PDF.
     *
     * @return void
     */
    public function downloadPDFReport()
    {
        $this->validate();

        $file_name = str('report-')
        ->append(now()->format('d-m-Y'))
        ->finish('.pdf');

        $headers = array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename={$file_name}",
        );

        $pdf_content = Pdf::loadView(
            $this->reportViewName(),
            $this->data()
        )->output();

        return response()->streamDownload(
            fn () => print($pdf_content),
            $file_name,
            $headers
        );
    }

    /**
     * Dados para serem populados no relatório.
     *
     * @return array<string, mixed>
     */
    private function data()
    {
        return [
            'header' => $this->reportHeader(),
            'initial_date' => $this->initial_date,
            'final_date' => $this->final_date,
            'filter' => $this->term,
            'result' => $this->makeReport(per_page: PHP_INT_MAX),
        ];
    }
}
