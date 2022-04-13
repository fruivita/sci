<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
