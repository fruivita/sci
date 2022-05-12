<?php

namespace App\Models;

use App\Enums\DepartmentReportType;
use Carbon\Carbon;
use FruiVita\Corporate\Models\Department as CorporateDepartment;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

/**
 * Department for a given user.
 *
 * @see https://laravel.com/docs/eloquent
 */
class Department extends CorporateDepartment
{
    /**
     * Department ID of users with no department. As a rule, users that exist
     * only on the LDAP server.
     *
     * @var int
     */
    public const DEPARTMENTLESS = 0;

    /**
     * Prints from a particular Department.
     *
     * Relationship department (1:N) prints.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prints()
    {
        return $this->hasMany(Printing::class, 'department_id', 'id');
    }

    /**
     * Generates the printing report by department according to the informed
     * period.
     *
     * The report brings the following information according to the parameters
     * informed:
     * - department: department name
     * - acronym: department acronym
     * - total_print: print volume
     * - printer_count: number of printers used
     * - parent_acronym: parent department acronym
     * - parent_department: parent department name
     *
     * The report is ordered by:
     * - total_print desc
     * - department asc
     *
     * Business Rule: There are 3 types of print reports per department
     * - institutional: report of all registered departments
     * - managerial: authenticated user department report and child departments
     * - departamento: report only from the authenticated user's department
     *
     * The report includes departments with zero prints.
     *
     * @param \Carbon\Carbon                  $initial_date
     * @param \Carbon\Carbon                  $final_date
     * @param int                             $per_page
     * @param \App\Enums\DepartmentReportType $type
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function report(Carbon $initial_date, Carbon $final_date, int $per_page, DepartmentReportType $type)
    {
        return
        self::{$type->value}(
            $initial_date->startOfDay(),
            $final_date->endOfDay(),
            $per_page
        );
    }

    /**
     * Generates the institutional print report by department according to the
     * period.
     *
     * The report brings the following information according to the parameters
     * informed:
     * - department: department name
     * - acronym: department acronym
     * - total_print: print volume
     * - printer_count: number of printers used
     * - parent_acronym: parent department acronym
     * - parent_department: parent department name
     *
     * The report is ordered by:
     * - total_print desc
     * - department asc
     *
     * Business rule: the 'institutional' type report brings information about
     * all existing departments, including those with zero printing.
     *
     * @param \Carbon\Carbon $initial_date
     * @param \Carbon\Carbon $final_date
     * @param int            $per_page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private static function institutional(Carbon $initial_date, Carbon $final_date, int $per_page)
    {
        // All prints and quantities of printers used in the informed period
        // are prepared.
        $prints = DB::table('prints')
            ->selectRaw(
                'department_id,
                SUM(copies * pages) AS total_print,
                COUNT(DISTINCT printer_id) AS printer_count'
            )
            ->whereBetween('date', [$initial_date, $final_date])
            ->groupBy('department_id');

        // prepare all locations with the parent department acronym
        $department = DB::table('departments', 'd1')
            ->select(
                'd1.acronym',
                'd1.name AS department',
                'tmp.total_print',
                'tmp.printer_count',
                'd2.acronym AS parent_acronym',
                'd2.name AS parent_department'
            )
            ->leftJoin('departments AS d2', 'd2.id', '=', 'd1.parent_department')
            ->rightJoinSub($prints, 'tmp', 'tmp.department_id', '=', 'd1.id');

        // join the queries and prepares it for execution.
        return
        DB::table('departments', 'd3')
            ->select(
                'd3.acronym',
                'd3.name AS department',
                'tmp.total_print',
                'tmp.printer_count',
                'd4.acronym AS parent_acronym',
                'd4.name AS parent_department'
            )
            ->leftJoin('departments AS d4', 'd4.id', '=', 'd3.parent_department')
            ->leftJoinSub($prints, 'tmp', 'tmp.department_id', '=', 'd3.id')
            ->union($department)
            ->orderBy('total_print', 'desc')
            ->orderBy('department', 'asc')
            ->paginate($per_page);
    }

    /**
     * Generates the print management report by department according to the
     * period.
     *
     * The report brings the following information according to the parameters
     * informed:
     * - department: department name
     * - acronym: department acronym
     * - total_print: print volume
     * - printer_count: number of printers used
     * - parent_acronym: parent department acronym
     * - parent_department: parent department name
     *
     * The report is ordered by:
     * - total_print desc
     * - department asc
     *
     * Business rule: the 'managerial' type report brings information from the
     * authenticated person's current department as well as their children.
     *
     * @param \Carbon\Carbon $initial_date
     * @param \Carbon\Carbon $final_date
     * @param int            $per_page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
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
     * Generates the authenticated user's department print report.
     *
     * The report brings the following information according to the parameters
     * informed:
     * - department: department name
     * - acronym: department acronym
     * - total_print: print volume
     * - printer_count: number of printers used
     * - parent_acronym: parent department acronym
     * - parent_department: parent department name
     *
     * Business rule: 'department' type report only brings information from the
     * authenticated person's department, not from child departments.
     *
     * @param \Carbon\Carbon $initial_date
     * @param \Carbon\Carbon $final_date
     * @param int            $per_page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private static function department(Carbon $initial_date, Carbon $final_date, int $per_page)
    {
        return
        DB::table('departments', 'd')
            ->selectRaw(
                'SUM(i.copies * i.pages) AS total_print,
                d.acronym,
                d.name AS department,
                parent.acronym AS parent_acronym,
                parent.name AS parent_department,
                COUNT(DISTINCT printer_id) AS printer_count'
            )
            ->join('users AS u', 'u.department_id', '=', 'd.id')
            ->leftJoin('departments AS parent', 'parent.id', '=', 'd.parent_department')
            ->leftJoin('prints AS i', function ($join) use ($initial_date, $final_date) {
                $join->on('d.id', '=', 'i.department_id')
                ->whereBetween('date', [$initial_date, $final_date]);
            })
            ->where('u.username', '=', auth()->user()->username)
            ->groupBy('u.department_id')
            ->paginate($per_page);
    }

    /**
     * Calculates the number of records returned by the management report.
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
