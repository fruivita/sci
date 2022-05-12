<?php

namespace App\View\Components\Form;

use function App\reportMaxDate;
use function App\reportMinDate;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\Component;

/**
 * @see https://laravel.com/docs/blade#components
 * @see https://flatpickr.js.org/
 * @see https://www.youtube.com/watch?v=lKg7AMeRtJY
 */
class DatePicker extends Component
{
    /**
     * Minimum date of the datepicker.
     *
     * @var string
     */
    private $min_date;

    /**
     * Datepicker maximum date.
     *
     * @var string
     */
    private $max_date;

    /**
     * Create a new component instance.
     *
     * @param string|null $min_date d-m-Y format
     * @param string|null $max_date d-m-Y format
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
     * Validates the informed parameters.
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
