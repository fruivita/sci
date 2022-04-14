<?php

namespace App\Importer\Contracts;

interface IImportablePrintLog
{
    /**
     * Importa os logs de impressão que estão no File System.
     *
     * @return void
     */
    public function import();
}
