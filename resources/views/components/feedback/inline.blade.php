{{--
    User feedback to preferably be added next to the action button to display
    feedback to the user.

    The component waits for the 'feedback' event to be issued, accompanied by
    the type (error or success) and the message that should be displayed.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<span
    x-data="{ showInlineFeedback : false , type : '', message : '' }"
    x-init="
        @this.on('feedback', ( t, m ) => {
            setTimeout(() => {
                showInlineFeedback = false;
            }, 2500);
            showInlineFeedback = true;
            type = t;
            message = m;
        })
    "
    x-show="showInlineFeedback"
    x-text="message"
    x-transition.duration.500ms
    x-bind:class="(type == 'success') ? 'text-green-500' : 'text-red-500'"
    class="cursor-default font-bold text-center"
></span>
