<?php

namespace App\Importer\Contracts;

interface IImportablePrint
{
    /**
     * Executa a importação da impressão informada.
     *
     * Formato da string de impressão:
     * - servidor de impressão que controla a impressora
     * - data no formato dd/mm/yyyy
     * - hora no formato hh:mm:ss
     * - nome do documento impresso
     * - usuário do AD que realizou a impressão
     * - id do cargo do usuário
     * - id da lotação do usuário
     * - id da função do usuário
     * - cliente de onde partiu a solicitação
     * - impressora que imprimiu
     * - tamanho do arquivo impresso
     * - quantidade de páginas
     * - quantidade de cópias
     *
     * Campos delimitados pelo caracter **╡**
     *
     * @param string $print string de impressão
     *
     * @return void
     */
    public function import(string $print);
}
