<?php

namespace App\View\Components\Form;

use Illuminate\Support\Facades\Validator;
use Illuminate\View\Component;

use function App\reportMaxDate;
use function App\reportMinDate;

/**
 * @see https://laravel.com/docs/9.x/blade#components
 * @see https://flatpickr.js.org/
 * @see https://www.youtube.com/watch?v=lKg7AMeRtJY
 */
class DatePicker extends Component
{
    /**
     * Data mínima do datepicker.
     *
     * @var string
     */
    private $min_date;

    /**
     * Data máxima do datepicker.
     *
     * @var string
     */
    private $max_date;

    /**
     * Create a new component instance.
     *
     * @param string|null $min_date formato d-m-Y
     * @param string|null $max_date formato d-m-Y
     *
     * @return void
     */
    public function __construct(string $min_date = null, string $max_date = null)
    {
        $validator = $this->validator($min_date, $max_date);

        $this->min_date = $validator->errors()->has('min_date')
        ? reportMinDate()->format('d-m-Y')
        : $min_date;

        $this->max_date = $validator->errors()->has('max_date')
        ? reportMaxDate()->format('d-m-Y')
        : $max_date;
    }

    /**
     * Create a new component instance.
     *
     * @return array<string, mixed>
     */
    public function getFlatpickrConfiguration()
    {
        return [
            'allowInput' => true,
            'dateFormat' => 'd-m-Y',
            'disableMobile' => true,
            'locale' => 'pt',
            'minDate' => $this->min_date,
            'maxDate' => $this->max_date,
        ];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.form.date-picker');
    }

    /**
     * Valida os parâmetros informados.
     *
     * @param string|null $min_date formato d-m-Y
     * @param string|null $max_date formato d-m-Y
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validator(string $min_date = null, string $max_date = null)
    {
        return Validator::make(
            [
                'min_date' => $min_date,
                'max_date' => $max_date,
            ],
            [
                'min_date' => [
                    'bail',
                    'required',
                    'date_format:d-m-Y',
                ],

                'max_date' => [
                    'bail',
                    'required',
                    'date_format:d-m-Y',
                ],
            ]
        );
    }
}
