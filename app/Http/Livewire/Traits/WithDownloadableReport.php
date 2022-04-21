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
     * Nome da view utilizada para a geração do relatório em PDF.
     *
     * @return string
     */
    abstract private function pdfReportViewName();

    /**
     * Relatório paginado, de acordo com as solicitações do usuário.
     *
     * @param int|null $per_page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    abstract private function makeReport(int $per_page = null);

    /**
     * Filtro extra utilizado no relatório.
     *
     * @return string|null
     */
    abstract private function filter();

    /**
     * Autorização para gerar o relatório em formato PDF.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    abstract private function authorizePDF();

    /**
     * Action do usuário para solicitar o Download do relatório em formato PDF.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadPDFReport()
    {
        $this->authorizePDF();
        $this->validate();

        $file_name = str('report-')
        ->append(now()->format('d-m-Y'))
        ->finish('.pdf');

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename={$file_name}",
        ];

        $pdf_content = Pdf::loadView(
            $this->pdfReportViewName(),
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
            'filter' => $this->filter(),
            'result' => $this->makeReport(per_page: PHP_INT_MAX),
        ];
    }
}
