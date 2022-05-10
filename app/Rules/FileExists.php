<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;

/**
 * Verifica se o arquivo existe no storage informado.
 *
 * @see https://laravel.com/docs/validation#custom-validation-rules
 */
class FileExists implements Rule
{
    /**
     * Nome do storage onde será verificada a existência do arquivo.
     *
     * @var string
     */
    public $disk;

    /**
     * @param string $disk nome do storage para verficiar a existência do
     *                     arquivo
     *
     * @return void
     */
    public function __construct(string $disk)
    {
        $this->disk = $disk;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Storage::disk($this->disk)->exists($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.not_found.file');
    }
}
