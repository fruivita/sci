{{--
    Ações sobre os múltiplos checkbox.

    Props:
    - error: mensagem de erro que deverá ser exibida

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<select
    title="{{ __('Checkbox actions') }}"
    {{ $attributes->merge(['class' => "bg-primary-300 rounded w-14 dark:bg-secondary-500"]) }}
    {{ $attributes->except('class') }}
>

    <option value=""></option>


    @foreach (\App\Enums\CheckboxAction::cases() as $action)

        <option value="{{ $action->value }}">

            {{ $action->label() }}

        </option>

    @endforeach

</select>
