<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

/**
 * @see https://laravel.com/docs/eloquent
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
     * Prints managed by a particular print server.
     *
     * Relationship print server (1:N) prints.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prints()
    {
        return $this->hasMany(Printing::class, 'server_id', 'id');
    }

    /**
     * Relationship print server (N:M) sites.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sites()
    {
        return $this->belongsToMany(Site::class, 'server_site', 'server_id', 'site_id')->withTimestamps();
    }

    /**
     * Default ordering of the model.
     *
     * Order: name asc
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
     * Previous record.
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
     * Next record.
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
     * Generates the print volume report by server according to the informed
     * period.
     *
     * It includes information about the sites allowing, with caveats, to use
     * this report to check the print volume by site.
     *
     * The report brings the following information according to the parameters
     * informed:
     * - total_print: print volume
     * - site: geographic location controlled by the print server
     * - server: print server
     * - printer_count: number of printers used
     * - percentage: percentage that the printing volume represents of the
     * total
     *
     * The return is ordered by:
     * - total_print desc
     * - site asc
     *
     * Business rule: calculates the print volume of all servers, the number of
     * printers used, the percentage that the print represents, as well as the
     * sites that are controlled by the print server.
     * All print servers are brought up in the result, even if their print
     * volume is zero.
     *
     * @param \Carbon\Carbon $initial_date
     * @param \Carbon\Carbon $final_date
     * @param int            $per_page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @see https://laravel.com/docs/pagination#appending-query-string-values
     */
    public static function report(Carbon $initial_date, Carbon $final_date, int $per_page)
    {
        $from = $initial_date->startOfDay();
        $until = $final_date->endOfDay();

        // Prepare the query of the total print in the period.
        $sum =
            DB::table('prints')
                ->selectRaw('SUM(copies * pages) AS total')
                ->whereBetween('date', [$from, $until]);

        // Prepared the query of prints grouped by server.
        $printing =
            DB::table('prints')
                ->selectRaw('server_id AS s_id, SUM(copies * pages) AS sum, COUNT(DISTINCT printer_id) AS printer')
                ->whereBetween('date', [$from, $until])
                ->groupBy('s_id');

        // Execute the query by joining the prepared queries
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
