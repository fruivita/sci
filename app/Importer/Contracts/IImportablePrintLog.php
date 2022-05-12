<?php

namespace App\Importer\Contracts;

interface IImportablePrintLog
{
    /**
     * Import the print logs that are in the File System.
     *
     * @return void
     */
    public function import();
}
