<?php

namespace App\Http\Livewire\Traits;

use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Trait for the report download negotiations.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithDownloadableReport
{
    /**
     * Title of the report that will be generated.
     *
     * @return string
     */
    abstract private function reportHeader();

    /**
     * Name of the view used to generate the PDF report.
     *
     * @return string
     */
    abstract private function pdfReportViewName();

    /**
     * Paginated report, as per user requests.
     *
     * @param int|null $per_page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    abstract private function makeReport(int $per_page = null);

    /**
     * Extra filter used in the report.
     *
     * @return string|null
     */
    abstract private function filter();

    /**
     * User Action to request Download the report in PDF format.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadPDFReport()
    {
        $this->validate();

        $filename = str('report-')
        ->append(now()->format('d-m-Y'))
        ->finish('.pdf');

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $pdf_content = Pdf::loadView(
            $this->pdfReportViewName(),
            $this->data()
        )->output();

        return response()->streamDownload(
            fn () => print($pdf_content),
            $filename,
            $headers
        );
    }

    /**
     * Data to be populated in the report.
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
