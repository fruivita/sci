<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Printers.
 *
 * @see https://laravel.com/docs/eloquent
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
     * Prints from a particular printer.
     *
     * Relationship printer (1:N) prints.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prints()
    {
        return $this->hasMany(Printing::class, 'printer_id', 'id');
    }

    /**
     * Generates the print volume report by printer according to the period and
     * printer(s) informed.
     *
     * The report brings the following information according to the parameters
     * informed:
     * - total_print: print volume
     * - printer:printer name
     * - last_print_date: printer's last print date
     *
     * The return is ordered by:
     * - total_print desc
     * - printer asc
     *
     * Business rule: calculates the printing volume of all printers and/or
     * only the ones informed, as well as the date of its last printing
     * according to the range of dates informed.
     * The last print date is also limited by the range.
     * Only printers that performed some printing in the informed period are
     * displayed.
     *
     * @param \Carbon\Carbon $initial_date
     * @param \Carbon\Carbon $final_date
     * @param int            $per_page
     * @param string         $printer
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
