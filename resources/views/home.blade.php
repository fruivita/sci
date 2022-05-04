{{--
    View default para usuários autenticados.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-layouts.app>

    <x-page header="{{ __('Home') }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            @can(\App\Enums\Policy::Report->value, \App\Models\Printing::class)

                <x-link-card
                    icon="graph-up"
                    href="{{ route('report.printing.create') }}"
                    text="{{ __('General print report') }}"
                    title="{{ __('Generating the general print report') }}"/>

            @endcan


            @can(\App\Enums\Policy::Report->value, \App\Models\Printer::class)

                <x-link-card
                    icon="printer"
                    href="{{ route('report.printer.create') }}"
                    text="{{ __('Report by printer') }}"
                    title="{{ __('Generating the print report by printer') }}"/>

            @endcan


            @can(\App\Enums\Policy::ReportAny->value, \App\Models\Department::class)

                <x-link-card
                    icon="diagram-3"
                    href="{{ route('report.department.create') }}"
                    text="{{ __('Report by department') }}"
                    title="{{ __('Generating the print report by department') }}"/>

            @endcan


            @can(\App\Enums\Policy::Report->value, \App\Models\Server::class)

                <x-link-card
                    icon="server"
                    href="{{ route('report.server.create') }}"
                    text="{{ __('Report by server') }}"
                    title="{{ __('Generating the print report by server') }}"/>

            @endcan


            @can(\App\Enums\Policy::View->value, \App\Models\Configuration::class)

                <x-link-card
                    icon="gear"
                    href="{{ route('administration.configuration.show') }}"
                    text="{{ __('Application settings') }}"
                    title="{{ __('Application working settings management') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Documentation::class)

                <x-link-card
                    icon="book"
                    href="{{ route('administration.doc.index') }}"
                    text="{{ __('Documentation') }}"
                    title="{{ __('Application routes documentation management') }}"/>

            @endcan


            @can(\App\Enums\Policy::ImportationCreate->value)

                <x-link-card
                    icon="usb-drive"
                    href="{{ route('administration.importation.create') }}"
                    text="{{ __('Forced data import') }}"
                    title="{{ __('Execution of forced data import') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Site::class)

                <x-link-card
                    icon="building"
                    href="{{ route('administration.site.index') }}"
                    text="{{ __('Manage sites') }}"
                    title="{{ __('Sites management') }}"/>

            @endcan


            @can(\App\Enums\Policy::LogViewAny->value)

                <x-link-card
                    icon="file-earmark-text"
                    href="{{ route('administration.log.index') }}"
                    text="{{ __('Manage logs') }}"
                    title="{{ __('Application operation logs management') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Server::class)

                <x-link-card
                    icon="server"
                    href="{{ route('administration.server.index') }}"
                    text="{{ __('Manage servers') }}"
                    title="{{ __('Print servers management') }}"/>

            @endcan


            @can(\App\Enums\Policy::DelegationViewAny->value)

                <x-link-card
                    icon="person-lines-fill"
                    href="{{ route('authorization.delegations.index') }}"
                    text="{{ __('Role delegation') }}"
                    title="{{ __('Roles delegation management') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Role::class)

                <x-link-card
                    icon="award"
                    href="{{ route('authorization.role.index') }}"
                    text="{{ __('Manage roles') }}"
                    title="{{ __('Application roles management') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Permission::class)

                <x-link-card
                    icon="vector-pen"
                    href="{{ route('authorization.permission.index') }}"
                    text="{{ __('Manage permissions') }}"
                    title="{{ __('Application permissions management') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\User::class)

                <x-link-card
                    icon="person-check"
                    href="{{ route('authorization.user.index') }}"
                    text="{{ __('Manage users') }}"
                    title="{{ __('Users management') }}"/>

            @endcan


            @can(\App\Enums\Policy::SimulationCreate->value)

                <x-link-card
                    icon="people"
                    href="{{ route('test.simulation.create') }}"
                    text="{{ __('Usage simulation') }}"
                    title="{{ __('Application usage simulation') }}"/>

            @endcan

        </div>

    </x-page>

</x-layouts.app>
