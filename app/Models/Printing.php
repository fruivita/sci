<?php

namespace App\Models;

use App\Enums\MonthlyGroupingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

/**
 * Print.
 *
 * @see https://laravel.com/docs/eloquent
 */
class Printing extends Model
{
    use HasFactory;

    protected $table = 'prints';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'date',
        'time',
        'filename',
        'file_size',
        'pages',
        'copies',
    ];

    /**
     * Client who requested the print.
     *
     * Relationship print (N:1) client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    /**
     * Department of the user who requested the print.
     *
     * Relationship print (N:1) department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    /**
     * Printer that performed the printing.
     *
     * Relationship print (N:1) printer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function printer()
    {
        return $this->belongsTo(Printer::class, 'printer_id', 'id');
    }

    /**
     * User who requested the print.
     *
     * Relationship print (N:1) user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Print server responsible for printing.
     *
     * Relationship print (N:1) server.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id', 'id');
    }

    /**
     * Generates the printing report according to the period and grouping
     * (monthly, bimonthly, etc.).
     *
     * The report brings the following information according to the parameters
     * informed:
     * - year: grouping year
     * - monthly_grouping: number of months of grouping
     * - total_print: print volume
     * - printer_count: number of printers used
     *
     * The return is ordered by:
     * - year asc
     * - monthly_grouping asc
     *
     * Business rule: calculates the printing volume and the number of printers
     * used according to the grouping informed. The search is limited to the
     * range defined by initial_year and final_year.
     * In years past, grouping is calculated without the need for complex
     * operations. However, in the correct year, the grouping depends on the
     * current month so that future dates are discarded.
     * All groupings are displayed even if the prints for that period is zero.
     *
     * @param int                            $initial_year  ano inicial no padrão aaaa
     * @param int                            $final_year    ano final no padrão aaaa
     * @param int                            $per_page
     * @param \App\Enums\MonthlyGroupingType $grouping_type
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     *
     * @see \App\Providers\AppServiceProvider
     * @see https://gist.github.com/simonhamp/549e8821946e2c40a617c85d2cf5af5e
     */
    public static function report(int $initial_year, int $final_year, int $per_page, MonthlyGroupingType $grouping_type)
    {
        $grouping = $grouping_type->value;
        $page = Paginator::resolveCurrentPage() ?: 1;
        $offset = ($page * $per_page) - $per_page;

        $result = DB::select(
            'WITH RECURSIVE years AS
            (
                SELECT ? AS year

                UNION ALL

                SELECT year + 1
                FROM years
                WHERE year < ?
            ),

            cycles AS (
                SELECT 1 AS cycle

                UNION ALL

                SELECT cycle + 1
                FROM cycles
                WHERE cycle < 12 / ?
            )

            SELECT
                y.year AS year,
                c.cycle AS monthly_grouping,
                COALESCE(SUM(i.copies * i.pages), 0) AS total_print,
                COUNT(DISTINCT i.printer_id) AS printer_count
            FROM cycles c
            CROSS JOIN years y
            LEFT JOIN prints i ON YEAR(i.date) = y.year AND FLOOR((MONTH(i.date) - 1) / ?) + 1 = c.cycle
            WHERE y.year < ? OR (cycle - 1) * ? < ?
            GROUP BY y.year, c.cycle
            ORDER BY y.year ASC, c.cycle ASC
            LIMIT ? OFFSET ?',
            [
                $initial_year,
                $final_year,
                $grouping,
                $grouping,
                now()->year,
                $grouping,
                now()->month,
                $per_page,
                $offset,
            ]
        );

        return
        collect($result)
        ->map(function ($row) use ($grouping_type) {
            return self::monthlyGroupingForHumans($row, $grouping_type);
        })->customPaginate(
            self::total($initial_year, $final_year, $grouping_type),
            $per_page,
            $page
        );
    }

    /**
     * Prepares the grouping string to be displayed on screen.
     *
     * @param mixed                          $row           result set line
     * @param \App\Enums\MonthlyGroupingType $grouping_type
     *
     * @return mixed
     */
    private static function monthlyGroupingForHumans(mixed $row, MonthlyGroupingType $grouping_type)
    {
        switch ($grouping_type->value) {
            case '12':
                $for_humans = str('')->append($row->year);

                break;

            default:
                $for_humans = str('')->append($row->monthly_grouping)
                ->append('º ')
                ->append(__($grouping_type->label()))
                ->append(' ')
                ->finish($row->year);

                break;
        }

        $row->grouping_for_humans = $for_humans->toString();

        return $row;
    }

    /**
     * Calculates the total number of records in the report (report).
     *
     * The calculation takes into account the current year and month for
     * discarding future dates.
     * The total number of records is calculated to facilitate the pagination
     * of the results.
     *
     * @param int                            $initial_year
     * @param int                            $final_year
     * @param \App\Enums\MonthlyGroupingType $grouping_type
     *
     * @return int total de registros
     */
    private static function total(int $initial_year, int $final_year, MonthlyGroupingType $grouping_type)
    {
        $current_year = now()->year;
        $current_month = now()->month;

        $total = 0;

        // own rule for the current year
        if ($final_year == $current_year) {
            $total += ceil($current_month / $grouping_type->value);
            $final_year = $current_year - 1;
        }

        // own rule for past years
        if ($initial_year < $current_year) {
            $total += ($final_year - $initial_year + 1) * 12 / $grouping_type->value;
        }

        return (int) $total;
    }
}
