<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Impressoras.
 *
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Printer extends Model
{
    use HasFactory;

    protected $table = 'printers';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name'];

    /**
     * Impressões feitas por uma determinada impressora.
     *
     * Relacionamento impressora (1:N) impressões.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prints()
    {
        return $this->hasMany(Printing::class, 'printer_id', 'id');
    }

    /**
     * Gera o relatório de volume de impressão por impressora de acordo com o
     * período e a(s) impressora(s) informadas.
     *
     * O relatório traz as seguintes informações de acordo com os parâmetros
     * informados:
     * - total_print: volume de impressão
     * - printer: nome da impressora
     * - last_print_date: data da última impressão da impressora
     *
     * O retorno é ordenado pelo:
     * - total_print desc
     * - printer asc
     *
     * Regra de negócio: calcula o volume de impressão de todas as impressoras
     * e/ou apenas das informadas, bem como a data de sua última impressão de
     * acordo com o range de datas informados.
     * A data da última impressão também é limitada pelo range.
     * Somente são exibidas as impressoras que realizaram alguma impressão no
     * período informado.
     *
     * @param \Carbon\Carbon                 $initial_date
     * @param \Carbon\Carbon                 $final_date
     * @param int                            $per_page
     * @param string $term
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function report(Carbon $initial_date, Carbon $final_date, int $per_page, string $printer = null)
    {
        return
            DB::table('prints', 'i')
                ->selectRaw('SUM(i.copies * i.pages) AS total_print, p.name AS printer, DATE_FORMAT(MAX(i.date), "%d-%m-%Y") AS last_print_date')
                ->join('printers AS p', 'p.id', '=', 'i.printer_id')
                ->whereBetween('i.date', [$initial_date->startOfDay(), $final_date->endOfDay()])
                ->when($printer, function ($query) use ($printer) {
                    return $query->whereRaw('p.name REGEXP ?', [$printer]);
                })
                ->groupBy('i.printer_id')
                ->orderBy('total_print', 'desc')
                ->orderBy('printer', 'asc')
                ->paginate($per_page);
    }
}
