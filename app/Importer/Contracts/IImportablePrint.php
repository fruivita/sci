<?php

namespace App\Importer\Contracts;

interface IImportablePrint
{
    /**
     * Executes the import of the informed print.
     *
     * Print string format:
     * - print server that controls the printer
     * - date in dd/mm/yyyy format
     * - time in hh:mm:ss format
     * - printed document name
     * - AD user who performed the print
     * - user occupation (cargo) id
     * - user department (lotação) id
     * - user duly (função comissionada) id
     * - client from which the request came
     * - printer that printed
     * - printed file size
     * - number of pages
     * - number of copies
     *
     * Fields delimited by the **╡** character
     *
     * @param string $print print string
     *
     * @return void
     */
    public function import(string $print);
}
