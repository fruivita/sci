{{--
    View livewire para administração dos logs da aplicação.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page header="{{ __('Application health logs') }}">

    <x-container>

        <form>

            <div class="flex flex-col space-x-0 space-y-6 lg:flex-row lg:space-x-3 lg:space-y-0">

                <x-form.select
                    wire:key="log-filename"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.lazy="filename"
                    :error="$errors->first('filename')"
                    icon="file-earmark-text"
                    required
                    text="{{ __('Log file') }}"
                    title="{{ __('Choose log file') }}"
                >

                    @forelse ($log_files ?? [] as $file)

                        <option value="{{ $file->getFilename() }}">

                            {{ $file->getFilename() }}

                        </option>

                    @empty

                        <option>{{ __('No record found') }}</option>

                    @endforelse

                </x-form.select>

            </div>


            @if (
                auth()->user()->can(\App\Enums\Policy::LogDelete->value)
                || auth()->user()->can(\App\Enums\Policy::LogDownload->value)
            )

                <div class="flex flex-col mt-3 space-x-0 space-y-3 lg:flex-row lg:justify-end lg:space-x-3 lg:space-y-0">

                    <x-feedback.inline/>


                    @can(\App\Enums\Policy::LogDownload->value)

                        <x-button
                            wire:click="download"
                            wire:key="btn-download"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:target="delete,download,filename"
                            class="btn-do"
                            icon="download"
                            text="{{ __('Download') }}"
                            title="{{ __('Download the log file') }}"
                            type="button"/>

                    @endcan


                    @can(\App\Enums\Policy::LogDelete->value)

                        <x-button
                            wire:click="delete"
                            wire:key="btn-delete"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:target="delete,download,filename"
                            class="btn-danger"
                            icon="trash"
                            text="{{ __('Delete') }}"
                            title="{{ __('Delete the log file') }}"
                            type="button"/>

                    @endcan

                </div>

            @endif

        </form>

    </x-container>


    <x-container>

        <x-table.perpage
            wire:key="per-page"
            wire:model="per_page"
            :error="$errors->first('per_page')"/>


        @forelse ($file_content ?? [] as $row_number => $row)

            @if (preg_match('/^\[\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}\]/', $row))

                <p class="border-t-4 font-bold text-primary-500"><span>{{ $row_number }}</span>: {{ $row }}</p>

            @else

                <p>{{ $row }}</p>

            @endif

        @empty

            <p class="text-center p-3">{{ __('No content') }}</p>

        @endforelse

    </x-container>


    {{ optional($file_content)->links() }}

</x-page>
