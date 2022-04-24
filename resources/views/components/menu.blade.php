{{--
    Menu principal de navegação.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-menu.group name="{{ __('Functionalities') }}">

    <x-menu.theme-toggler/>


    @auth

        <x-menu.fake-link
            icon="person"
            text="{{ auth()->user()->forHumans() }}"/>


        <x-menu.logout/>

    @else

        <x-menu.link
            class="{{ request()->routeIs('login') ? 'active': '' }}"
            icon="person"
            href="{{ route('login') }}"
            text="{{ __('Login') }}"
            title="{{ __('Go to login page') }}"/>

    @endauth

</x-menu.group>


@auth

    @if (
        auth()->user()->can(\App\Enums\Policy::Report->value, \App\Models\Printer::class)
        || auth()->user()->can(\App\Enums\Policy::Report->value, \App\Models\Printing::class)
        || auth()->user()->can(\App\Enums\Policy::Report->value, \App\Models\Server::class)
    )
        <x-menu.group name="{{ __('Reports') }}">

            @can(\App\Enums\Policy::Report->value, \App\Models\Printing::class)

                <x-menu.link
                    class="{{ request()->routeIs('report.printing.*') ? 'active': '' }}"
                    icon="graph-up"
                    href="{{ route('report.printing.create') }}"
                    text="{{ __('Print') }}"
                    title="{{ __('General print report') }}"/>

            @endcan


            @can(\App\Enums\Policy::Report->value, \App\Models\Printer::class)

                <x-menu.link
                    class="{{ request()->routeIs('report.printer.*') ? 'active': '' }}"
                    icon="printer"
                    href="{{ route('report.printer.create') }}"
                    text="{{ __('Printer') }}"
                    title="{{ __('Report by printer') }}"/>

            @endcan


            @can(\App\Enums\Policy::ReportAny->value, \App\Models\Department::class)

                <x-menu.link
                    class="{{ request()->routeIs('report.department.*') ? 'active': '' }}"
                    icon="diagram-3"
                    href="{{ route('report.department.create') }}"
                    text="{{ __('Department') }}"
                    title="{{ __('Report by department') }}"/>

            @endcan


            @can(\App\Enums\Policy::Report->value, \App\Models\Server::class)

                <x-menu.link
                    class="{{ request()->routeIs('report.server.*') ? 'active': '' }}"
                    icon="server"
                    href="{{ route('report.server.create') }}"
                    text="{{ __('Server') }}"
                    title="{{ __('Report by server') }}"/>

            @endcan

        </x-menu.group>

    @endif


    @if (
            auth()->user()->can(\App\Enums\Policy::View->value, \App\Models\Configuration::class)
            || auth()->user()->can(\App\Enums\Policy::ImportationCreate->value)
            || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Server::class)
            || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Site::class)
        )

        <x-menu.group name="{{ __('Administration') }}">

            @can(\App\Enums\Policy::View->value, \App\Models\Configuration::class)

                <x-menu.link
                    class="{{ request()->routeIs('administration.configuration.*') ? 'active': '' }}"
                    icon="gear"
                    href="{{ route('administration.configuration.show') }}"
                    text="{{ __('Configuration') }}"
                    title="{{ __('Go to application settings page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ImportationCreate->value)

                <x-menu.link
                    class="{{ request()->routeIs('administration.importation.*') ? 'active': '' }}"
                    icon="usb-drive"
                    href="{{ route('administration.importation.create') }}"
                    text="{{ __('Importation') }}"
                    title="{{ __('Go to data importation page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Site::class)

                <x-menu.link
                    class="{{ request()->routeIs('administration.site.*') ? 'active': '' }}"
                    icon="building"
                    href="{{ route('administration.site.index') }}"
                    text="{{ __('Sites') }}"
                    title="{{ __('Go to sites page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Server::class)

                <x-menu.link
                    class="{{ request()->routeIs('administration.server.*') ? 'active': '' }}"
                    icon="server"
                    href="{{ route('administration.server.index') }}"
                    text="{{ __('Servers') }}"
                    title="{{ __('Go to servers page') }}"/>

            @endcan

        </x-menu.group>

    @endif


    @if (
        auth()->user()->can(\App\Enums\Policy::DelegationViewAny->value)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Role::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\Permission::class)
        || auth()->user()->can(\App\Enums\Policy::ViewAny->value, \App\Models\User::class)
    )

        <x-menu.group name="{{ __('Authorizations') }}">

            @can(\App\Enums\Policy::DelegationViewAny->value)

                <x-menu.link
                    class="{{ request()->routeIs('authorization.delegations.*') ? 'active': '' }}"
                    icon="person-lines-fill"
                    href="{{ route('authorization.delegations.index') }}"
                    text="{{ __('Delegation') }}"
                    title="{{ __('Go to delegations page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Role::class)

                <x-menu.link
                    class="{{ request()->routeIs('authorization.role.*') ? 'active': '' }}"
                    icon="award"
                    href="{{ route('authorization.role.index') }}"
                    text="{{ __('Roles') }}"
                    title="{{ __('Go to roles page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Permission::class)

                <x-menu.link
                    class="{{ request()->routeIs('authorization.permission.*') ? 'active': '' }}"
                    icon="vector-pen"
                    href="{{ route('authorization.permission.index') }}"
                    text="{{ __('Permissions') }}"
                    title="{{ __('Go to permissions page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\User::class)

                <x-menu.link
                    class="{{ request()->routeIs('authorization.user.*') ? 'active': '' }}"
                    icon="person-check"
                    href="{{ route('authorization.user.index') }}"
                    text="{{ __('Users') }}"
                    title="{{ __('Go to users page') }}"/>

            @endcan

        </x-menu.group>

    @endif


    @if (
        auth()->user()->can(\App\Enums\Policy::SimulationCreate->value)
    )

        <x-menu.group name="{{ __('Tests') }}">

            @can(\App\Enums\Policy::SimulationCreate->value)

                <x-menu.link
                    class="{{ request()->routeIs('test.simulation.*') ? 'active': '' }}"
                    icon="people"
                    href="{{ route('test.simulation.create') }}"
                    text="{{ __('Simulation') }}"
                    title="{{ __('Go to simulation page') }}"/>

            @endcan

        </x-menu.group>

    @endif

@endauth
