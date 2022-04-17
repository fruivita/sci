<?php

namespace App\Models;

use App\Enums\MonthlyGroupingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

/**
 * Impressão.
 *
 * @see https://laravel.com/docs/9.x/eloquent
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
     * Cliente que solicitou a impressão.
     *
     * Relacionamento impressão (N:1) cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    /**
     * Lotação do usuário que solicitou a impressão.
     *
     * Relacionamento impressão (N:1) lotação.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    /**
     * Impressora que realizou a impressão.
     *
     * Relacionamento impressão (N:1) impressora.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function printer()
    {
        return $this->belongsTo(Printer::class, 'printer_id', 'id');
    }

    /**
     * Usuário que solicitou a impressão.
     *
     * Relacionamento impressão (N:1) usuário.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Servidor de impressão responsável pela impressão.
     *
     * Relacionamento impressão (N:1) servidor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id', 'id');
    }

    /**
     * Gera o relatório de impressão de acordo com o período e o agrupamento
     * (mensal, bimestral, etc) informados.
     *
     * O relatório traz as seguintes informações de acordo com os parâmetros
     * informados:
     * - year: ano do agrupamento
     * - monthly_grouping: quantidade de meses de agrupamento
     * - total_print: volume de impressão
     * - printer_count: quantidade de impressoras utilizadas
     *
     * O retorno é ordenado pelo:
     * - year asc
     * - monthly_grouping asc
     *
     * Regra de negócio: calcula o volume de impressão e a quantidade de
     * impressoras utilizadas de acordo com o agrupamento informado. A pesquisa
     * é limitada ao range definido pelo initial_year e final_year.
     * Nos anos passados, o agrupamento é calculado sem a necessidade de
     * operações complexas. Contudo, no ano correte, o agrupamento depende do
     * mês atual para que as datas futuras sejam descartadas.
     * Todos os agrupamentos são exibidos mesmo que a impressão daquele período
     * seja zero.
     *
     * @param int                           $initial_year  ano inicial no padrão aaaa
     * @param int                           $final_year    ano final no padrão aaaa
     * @param int                           $per_page
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
     * Prepara a string de agrupamento para ser exibida em tela.
     *
     * @param mixed                         $row           linha do result set
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
     * Calcula a quantidade total de registros do relatório (report).
     *
     * O cálculo leva em consideração o ano e o mês atual para o descarte de datas futuras.
     * O total de registros é calculado para viabilizar a paginação dos resultados.
     *
     * @param int                           $initial_year
     * @param int                           $final_year
     * @param \App\Enums\MonthlyGroupingType $grouping_type
     *
     * @return int total de registros
     */
    private static function total(int $initial_year, int $final_year, MonthlyGroupingType $grouping_type)
    {
        $current_year = now()->year;
        $current_month = now()->month;

        $total = 0;

        // regra própria para o ano corrente
        if ($final_year == $current_year) {
            $total += ceil($current_month / $grouping_type->value);
            $final_year = $current_year - 1;
        }

        // regra própria para os anos passados
        if ($initial_year < $current_year) {
            $total += ($final_year - $initial_year + 1) * 12 / $grouping_type->value;
        }

        return (int) $total;
    }
}
