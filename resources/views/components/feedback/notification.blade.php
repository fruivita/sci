{{--
    Feedback in the form of a popup notification to respond to the user's
    request.
    Suitable for when 'inline' feedback is not indicated.

    After a certain time, the message will be automatically extinguished.

    The component waits for the 'notify' event to be emitted accompanied by:
    - message type (error or success)
    - representative image icon
    - header
    - message
    - message timeout.

    Note: This is an intrusive display component, as it overlays the page
    content.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<div
    x-data="{ notify : false, type : '', icon : '', header : '', message : '', timeout : '' , timeout_id : '' }"
    x-on:notify.window="
        notify = true;
        type = $event.detail.type;
        icon = $event.detail.icon;
        header = $event.detail.header;
        message = $event.detail.message;
        timeout = $event.detail.timeout ?? 2500;
        timeout_id = setTimeout(() => {
            notify = false;
        }, timeout);
    "
    x-show="notify"
    x-on:mouseover.once="clearTimeout(timeout_id)"
    x-transition.duration.500ms
    x-bind:class="type"
    class="border-l-8 border-r-8 bottom-3 fixed ml-3 p-3 right-3 z-30"
>

    <div class="flex items-center space-x-3">

        {{-- message context icon --}}
        <div x-html="icon"></div>


        <div x-bind:class="(header && message) ? 'space-y-3' : ''">

            {{-- message header --}}
            <h3 x-text="header" class="font-bold text-lg"></h3>


            {{-- message itself --}}
            <p x-text="message"></p>

        </div>

            {{-- button to close the dialog box --}}
            <button x-on:click="notify = false;" class="animate-none lg:animate-ping" id="btn-flash" type="button">

                <x-icon name="x-circle"/>

            </button>

        </div>

    </div>

</div>
