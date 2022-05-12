<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;

/**
 * Checks if the file exists in the specified storage.
 *
 * @see https://laravel.com/docs/validation#custom-validation-rules
 */
class FileExists implements Rule
{
    /**
     * Storage name where the file's existence will be checked.
     *
     * @var string
     */
    public $disk;

    /**
     * @param string $disk storage name
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
