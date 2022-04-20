<?php

namespace App\Models;

use App\Enums\DepartmentReportType;
use Carbon\Carbon;
use FruiVita\Corporate\Models\Department as CorporateDepartment;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

/**
 * Lotação de um determinado usuário.
 *
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Department extends CorporateDepartment
{
    /**
     * Impressões vindas de uma determinada lotação.
     *
     * Relacionamento lotação (1:N) impressões.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prints()
    {
        return $this->hasMany(Printing::class, 'department_id', 'id');
    }

    /**
     * Gera o relatório de impressão por lotação de acordo com o período
     * informado.
     *
     * O relatório traz as seguintes informações de acordo com os parâmetros
     * informados:
     * - department: nome da lotação
     * - acronym: sigla da lotação
     * - total_print: volume de impressão
     * - printer_count: quantidade de impressoras utilizadas
     * - parent_acronym: sigla da lotação pai
     * - parent_department: nome da lotação pai
     *
     * O retorno é ordenado pelo:
     * - total_print desc
     * - department asc
     *
     * Regra de negócio: Existem 3 tipos de relatórios de impressão por lotação
     * - intitucional: relatório de todas as lotações cadastradas.
     * - gerencial: relatório da lotação da pessoa autenticada e lotações filha
     * - lotação: relatório apenas da lotação da pessoa autenticada.
     *
     * O relatório inclui as lotações com impressões zeradas.
     *
     * @param \Carbon\Carbon                 $initial_date
     * @param \Carbon\Carbon                 $final_date
     * @param \App\Enum\DepartmentReportType $type
     * @param int                            $per_page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function report(Carbon $initial_date, Carbon $final_date, DepartmentReportType $type, int $per_page)
    {
        return
        self::{$type->value}(
            $initial_date->startOfDay(),
            $final_date->endOfDay(),
            $per_page
        );
    }

    /**
     * Gera o relatório intitucional de impressão por lotação de acordo com o
     * período.
     *
     * O relatório traz as seguintes informações de acordo com os parâmetros
     * informados:
     * - department: nome da lotação
     * - acronym: sigla da lotação
     * - total_print: volume de impressão
     * - printer_count: quantidade de impressoras utilizadas
     * - parent_acronym: sigla da lotação pai
     * - parent_department: nome da lotação pai
     *
     * O retorno é ordenado pelo:
     * - total_print desc
     * - department asc
     *
     * Regra de negócio: o relatório do tipo 'institucional' traz informações
     * sobre todas as lotações existentes, inclusive, as com impressão zerada.
     *
     * @param \Carbon\Carbon $initial_date
     * @param \Carbon\Carbon $final_date
     * @param int            $per_page
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private static function institutional(Carbon $initial_date, Carbon $final_date, int $per_page)
    {
        //prepara-se todas as impressoes e quantidades de impressoras
        //utilizadas no período informado.
        $prints = DB::table('prints')
            ->selectRaw('department_id, SUM(copies * pages) AS total_print, COUNT(DISTINCT printer_id) AS printer_count')
            ->whereBetween('date', [$initial_date, $final_date])
            ->groupBy('department_id');

        //prepara-se todas as locações com a sigla da lotação pai right
        $department = DB::table('departments', 'd1')
            ->select('d1.acronym', 'd1.name AS department', 'tmp.total_print', 'tmp.printer_count', 'd2.acronym AS parent_acronym', 'd2.name AS parent_department')
            ->leftJoin('departments AS d2', 'd2.id', '=', 'd1.parent_department')
            ->rightJoinSub($prints, 'tmp', 'tmp.department_id', '=', 'd1.id');

        //junta as query e a prepara para a execução.
        return
        DB::table('departments', 'd3')
            ->select('d3.acronym', 'd3.name AS department', 'tmp.total_print', 'tmp.printer_count', 'd4.acronym AS parent_acronym', 'd4.name AS parent_department')
            ->leftJoin('departments AS d4', 'd4.id', '=', 'd3.parent_department')
            ->leftJoinSub($prints, 'tmp', 'tmp.department_id', '=', 'd3.id')
            ->union($department)
            ->orderBy('total_print', 'desc')
            ->orderBy('department', 'asc')
            ->paginate($per_page);
    }

    /**
     * Gera o relatório gerencial de impressão por lotação de acordo com o
     * período.
     *
     * O relatório traz as seguintes informações de acordo com os parâmetros
     * informados:
     * - department: nome da lotação
     * - acronym: sigla da lotação
     * - total_print: volume de impressão
     * - printer_count: quantidade de impressoras utilizadas
     * - parent_acronym: sigla da lotação pai
     * - parent_department: nome da lotação pai
     *
     * O retorno é ordenado pelo:
     * - total_print desc
     * - department asc
     *
     * Regra de negócio: o relatório do tipo 'gerencial' traz informações
     * da lotação atual da pessoa autenticada bem como das suas filhas.
     *
     * @param \Carbon\Carbon $initial_date
     * @param \Carbon\Carbon $final_date
     * @param int            $per_page
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     *
     * @see \App\Providers\AppServiceProvider
     * @see https://gist.github.com/simonhamp/549e8821946e2c40a617c85d2cf5af5e
     */
    private static function managerial(Carbon $initial_date, Carbon $final_date, int $per_page)
    {
        $page = Paginator::resolveCurrentPage() ?: 1;
        $offset = ($page * $per_page) - $per_page;

        $result = DB::select(
            'WITH RECURSIVE CTE AS
            (
                SELECT
                    d1.id, d1.acronym, d1.name, d1.parent_department
                FROM
                    departments d1
                WHERE d1.id=(
                    SELECT
                        d2.id
                    FROM
                        departments d2
                    JOIN users u ON u.department_id = d2.id
                    WHERE u.username = ?)

                UNION ALL

                SELECT
                    d3.id, d3.acronym, d3.name, d3.parent_department
                FROM
                    CTE lCTE, departments d3
                WHERE d3.parent_department = lCTE.id
            )

            SELECT
                lCTE.id,
                lCTE.acronym,
                lCTE.name AS department,
                d4.acronym AS parent_acronym,
                d4.name AS parent_department,
                COALESCE(SUM(i.copies * i.pages), 0) AS total_print,
                COUNT(DISTINCT i.printer_id) AS printer_count
            FROM CTE lCTE
            LEFT JOIN departments d4 ON d4.id = lCTE.parent_department
            LEFT JOIN prints i ON i.department_id = lCTE.id
                AND (i.date IS NULL OR i.date BETWEEN ? AND ?)
            GROUP BY lCTE.id, lCTE.acronym, lCTE.name, d4.acronym, d4.name
            ORDER BY total_print DESC, department ASC
            LIMIT ? OFFSET ?',
            [
                auth()->user()->username,
                $initial_date,
                $final_date,
                $per_page,
                $offset,
            ]
        );

        return
        collect($result)
        ->customPaginate(
            self::managerialTotalRecords($initial_date, $final_date),
            $per_page,
            $page
        );
    }

    /**
     * Gera o relatório de impressão da lotação do usuário autenticado.
     *
     * O relatório traz as seguintes informações de acordo com os parâmetros
     * informados:
     * - department: nome da lotação
     * - acronym: sigla da lotação
     * - total_print: volume de impressão
     * - printer_count: quantidade de impressoras utilizadas
     * - parent_acronym: sigla da lotação pai
     * - parent_department: nome da lotação pai
     *
     * Regra de negócio: o relatório do tipo 'lotação' traz informações apenas
     * da lotação da pessoa autenticada, não traz das lotações filha.
     *
     * @param \Carbon\Carbon $initial_date
     * @param \Carbon\Carbon $final_date
     * @param int            $per_page
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private static function department(Carbon $initial_date, Carbon $final_date, int $per_page)
    {
        return
        DB::table('prints', 'i')
            ->selectRaw('SUM(i.copies * i.pages) AS total_print, d.acronym, d.name AS department, parent.acronym AS parent_acronym, parent.name AS parent_department, COUNT(DISTINCT printer_id) AS printer_count')
            ->rightJoin('departments AS d', 'd.id', '=', 'i.department_id')
            ->join('users AS u', 'u.department_id', '=', 'd.id')
            ->leftJoin('departments AS parent', 'parent.id', '=', 'd.parent_department')
            ->where('u.username', '=', auth()->user()->username)
            ->where(function ($query) use ($initial_date, $final_date) {
                $query->whereBetween('i.date', [$initial_date, $final_date])
                    ->orWhereNull('i.date');
            })
            ->groupBy('u.department_id')
            ->paginate($per_page);
    }

    /**
     * Calcula a quantidade de registros retornada pelo relatório gerencial.
     *
     * @param \Carbon\Carbon $initial_date
     * @param \Carbon\Carbon $final_date
     *
     * @return int
     */
    private static function managerialTotalRecords(Carbon $initial_date, Carbon $final_date)
    {
        $result = DB::select(
            'WITH RECURSIVE CTE AS
            (
                SELECT
                    d1.id, d1.parent_department
                FROM
                    departments d1
                WHERE d1.id=(
                    SELECT
                        d2.id
                    FROM
                        departments d2
                    JOIN users u ON u.department_id = d2.id
                    WHERE u.username = ?)

                UNION ALL

                SELECT
                    d3.id, d3.parent_department
                FROM
                    CTE lCTE, departments d3
                WHERE d3.parent_department = lCTE.id
            )

            SELECT
                count(1) AS total
            FROM CTE lCTE
            LEFT JOIN departments d4 ON d4.id = lCTE.parent_department
            LEFT JOIN prints i ON i.department_id = lCTE.id
                AND (i.date IS NULL OR i.date BETWEEN ? AND ?)',
            [
                auth()->user()->username,
                $initial_date,
                $final_date,
            ]
        );

        return (int) $result[0]->total;
    }
}
