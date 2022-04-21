{{--
    View de login.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
    @see https://www.chromium.org/developers/design-documents/create-amazing-password-forms
--}}


<x-layouts.app>

    <article class="flex items-center justify-center min-h-screen">

        <x-container class="flex flex-col items-center justify-center rounded space-y-12">

            <h1 class="bg-primary-500 flex font-extrabold items-center h-24 justify-center rounded-full text-primary-50 w-24">{{ config('company.acronym') }}</h1>


            <form>

                @csrf


                <div class="flex flex-col p-3 space-y-6">

                    <x-form.input
                        autocomplete="username"
                        :error="$errors->first('username')"
                        icon="person"
                        id="username"
                        placeholder="{{ __('Ldap user') }}"
                        required
                        text="{{ __('Username') }}"
                        title="{{ __('Inform your network user') }}"
                        type="text"
                        :value="old('username')"/>


                    <x-form.input
                        autocomplete="current-password"
                        :error="$errors->first('password')"
                        icon="key"
                        id="password"
                        placeholder="{{ __('Ldap password') }}"
                        required
                        text="{{ __('Password') }}"
                        title="{{ __('Inform your network password') }}"
                        type="password"
                        value=""/>


                    <x-button
                        class="btn-default"
                        formaction="{{ route('login') }}"
                        formmethod="POST"
                        icon="box-arrow-in-right"
                        text="{{ __('Login') }}"
                        title="{{ __('Login in application') }}"
                        type="submit"/>

                </div>

            </form>

        </x-container>

    </article>

</x-layouts.app>
