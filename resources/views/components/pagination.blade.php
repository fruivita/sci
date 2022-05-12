{{--
    Paging links.

    It is a customization of the view provided by the laravel framework
    compatible with livewire.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@if ($paginator->hasPages())

  @php

    isset($this->numberOfPaginatorsRendered[$paginator->getPageName()])
      ? $this->numberOfPaginatorsRendered[$paginator->getPageName()]++
      : $this->numberOfPaginatorsRendered[$paginator->getPageName()] = 1

  @endphp


  <nav class="flex items-center justify-between">

    <div class="flex justify-between flex-1 lg:hidden mx-3">

      @if ($paginator->onFirstPage())

        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-primary-300 bg-primary-100 border border-primary-300 cursor-default leading-5 rounded-md select-none">

          {{ __('pagination.previous') }}

        </span>

      @else

        <button wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-primary-700 bg-primary-200 border border-primary-300 leading-5 rounded-md hover:text-primary-500 hover:scale-110 hover:-translate-y-1 focus:outline-none focus:ring ring-primary-300 focus:border-blue-300 active:bg-primary-100 active:text-primary-700 transition">

          {{ __('pagination.previous') }}

        </button>

      @endif


      @if ($paginator->hasMorePages())

        <button wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-primary-700 bg-primary-200 border border-primary-300 leading-5 rounded-md hover:text-primary-500 hover:scale-110 hover:-translate-y-1 focus:outline-none focus:ring ring-primary-300 focus:border-blue-300 active:bg-primary-100 active:text-primary-700 transition">

          {{ __('pagination.next') }}

        </button>

      @else

        <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-primary-300 bg-primary-100 border border-primary-300 cursor-default leading-5 rounded-md">

          {{ __('pagination.next') }}

        </span>

      @endif

    </div>


    <div class="hidden lg:flex-1 lg:flex lg:items-center lg:justify-between">

      <div>

        <p class="text-sm leading-5">

          <span>{{ __('Records') }}</span>


          <span class="font-medium">{{ $paginator->firstItem() }}</span>


          <span>{{ __('to') }}</span>


          <span class="font-medium">{{ $paginator->lastItem() }}</span>


          <span>{{ __('of') }}</span>


          <span class="font-medium">{{ $paginator->total() }}</span>

        </p>

      </div>


      <div>

        <span class="relative z-0 inline-flex shadow-sm rounded-md">

          {{-- Previous Page Link --}}
          @if ($paginator->onFirstPage())

            <span>

              <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-primary-300 bg-primary-100 border border-primary-300 cursor-default rounded-l-md leading-5">

                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">

                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />

                </svg>

              </span>

            </span>

          @else

            <button wire:click="previousPage('{{ $paginator->getPageName() }}')" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-primary-500 bg-primary-200 border border-primary-300 rounded-l-md leading-5 hover:text-primary-400 hover:scale-110 hover:-translate-y-1 hover:z-10 focus:z-10 focus:outline-none focus:ring ring-primary-300 focus:border-blue-300 active:bg-primary-100 active:text-primary-500 transition">

              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">

                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />

              </svg>

            </button>

          @endif


          {{-- Pagination Elements --}}
          @foreach ($elements as $element)

            {{-- "Three Dots" Separator --}}
            @if (is_string($element))

              <span>

                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-primary-700 bg-primary-100 border border-primary-300 cursor-default leading-5">{{ $element }}</span>

              </span>

            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))

              @foreach ($element as $page => $url)
                <span wire:key="paginator-{{ $paginator->getPageName() }}-{{ $this->numberOfPaginatorsRendered[$paginator->getPageName()] }}-page{{ $page }}">

                  @if ($page == $paginator->currentPage())

                    <span>

                      <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-primary-700 bg-primary-400 border border-primary-300 cursor-default leading-5">{{ $page }}</span>

                    </span>

                  @else

                    <button wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-primary-700 bg-primary-200 border border-primary-300 leading-5 hover:text-primary-500 hover:scale-110 hover:-translate-y-1 hover:z-10 focus:z-10 focus:outline-none focus:ring ring-primary-300 focus:border-blue-300 active:bg-primary-100 active:text-primary-700 transition">

                      {{ $page }}

                    </button>

                  @endif

                </span>

              @endforeach

            @endif

          @endforeach


          <span>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())

              <button wire:click="nextPage('{{ $paginator->getPageName() }}')" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-primary-500 bg-primary-200 border border-primary-300 rounded-r-md leading-5 hover:text-primary-400 hover:scale-110 hover:-translate-y-1 hover:z-10 focus:z-10 focus:outline-none focus:ring ring-primary-300 focus:border-blue-300 active:bg-primary-100 active:text-primary-500 transition">

                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">

                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />

                </svg>

              </button>

            @else

              <span>

                <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-primary-300 bg-primary-100 border border-primary-300 cursor-default rounded-r-md leading-5">

                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">

                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />

                  </svg>

                </span>

              </span>

            @endif

          <span>

        </span>

      </div>

    </div>

  </nav>

@endif
