<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Server extends Model
{
    use HasEagerLimit;
    use HasFactory;

    protected $table = 'servers';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name'];

    /**
     * Impressões gerenciadas por um determinado servidor de impressão.
     *
     * Relacionamento servidor de impressão (1:N) impressões.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prints()
    {
        return $this->hasMany(Printing::class, 'server_id', 'id');
    }

    /**
     * Relacionamento server (N:M) site.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sites()
    {
        return $this->belongsToMany(Site::class, 'server_site', 'server_id', 'site_id')->withTimestamps();
    }

    /**
     * Ordenação padrão do modelo.
     *
     * Ordem: name asc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Registro anterior.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function previous()
    {
        return Server::select('id')
        ->whereRaw('name < (select name from servers where id = ?)', [$this->id])
        ->orderBy('name', 'desc')
        ->take(1);
    }

    /**
     * Registro posterior.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function next()
    {
        return Server::select('id')
        ->whereRaw('name > (select name from servers where id = ?)', [$this->id])
        ->orderBy('name', 'asc')
        ->take(1);
    }

    /**
     * Gera o relatório de volume de impressão por servidor de acordo com o
     * período informado.
     *
     * Inclui informações sobre as localidades permitindo, com ressalvas, usar
     * esse relatório para verificar o volume de impressão por localidade.
     *
     * O relatório traz as seguintes informações de acordo com os parâmetros
     * informados:
     * - total_print: volume de impressão
     * - site: localidade geográfica controlada pelo servidor de impressão
     * - server: servidor de impressão
     * - printer_count: quantidade de impressoras utilizadas
     * - percentage: percentual que o volume de impressão representa do total
     *
     * O retorno é ordenado pelo:
     * - total_print desc
     * - site asc
     *
     * Regra de negócio: calcula o volume de impressão de todas os servidores,
     * a quantidade de impressoras utilizadas, o percentual que a impressão
     * represent, bem como as localidades (sites) que são controladas pelo
     * servidor de impressão.
     * Todos os servidores de impressão são trazidos no resultado, mesmo que
     * seu volume de impressão venha zerado.
     *
     * @param \Carbon\Carbon $initial_date
     * @param \Carbon\Carbon $final_date
     * @param int            $per_page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @see https://laravel.com/docs/9.x/pagination#appending-query-string-values
     */
    public static function report(Carbon $initial_date, Carbon $final_date, int $per_page)
    {
        $from = $initial_date->startOfDay();
        $until = $final_date->endOfDay();

        // Prepara-se a query do total de impressão no período.
        $sum =
            DB::table('prints')
                ->selectRaw('SUM(copies * pages) AS total')
                ->whereBetween('date', [$from, $until]);

        // Prepara-se a query das impressões agrupadas por servidor.
        $printing =
            DB::table('prints')
                ->selectRaw('server_id AS s_id, SUM(copies * pages) AS sum, COUNT(DISTINCT printer_id) AS printer')
                ->whereBetween('date', [$from, $until])
                ->groupBy('s_id');

        // Executa a query juntando as query preparadas
        return
            DB::table('sites', 'l')
                ->selectRaw('
                GROUP_CONCAT(l.name SEPARATOR ",") AS site,
                ss.name AS server,
                tmp1.printer AS printer_count,
                tmp1.sum AS total_print,
                ROUND((tmp1.sum/tmp2.total) * 100, 2) AS percentage')
                ->join('server_site AS ls', 'ls.site_id', '=', 'l.id')
                ->rightJoin('servers AS ss', 'ss.id', '=', 'ls.server_id')
                ->leftJoinSub($printing, 'tmp1', 'tmp1.s_id', '=', 'ss.id')
                ->crossJoinSub($sum, 'tmp2')
                ->groupBy(['ss.id', 'tmp2.total'])
                ->orderBy('total_print', 'desc')
                ->orderBy('site', 'asc')
                ->paginate($per_page);
    }
}
