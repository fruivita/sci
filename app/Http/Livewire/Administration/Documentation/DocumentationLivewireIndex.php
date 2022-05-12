<?php

namespace App\Http\Livewire\Administration\Documentation;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithLimit;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Models\Documentation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class DocumentationLivewireIndex extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;
    use WithLimit;
    use WithPerPagePagination;

    /**
     * Should the modal for deleting the documentation be displayed?
     *
     * @var bool
     */
    public $show_delete_modal = false;

    /**
     * Resource that will be deleted.
     *
     * @var \App\Models\Documentation|null
     */
    public $deleting = null;

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::ViewAny->value, Documentation::class);
    }

    /**
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @return void
     */
    public function mount()
    {
        $this->deleting = $this->blankModel();
    }

    /**
     * Blank model.
     *
     * @return \App\Models\Documentation
     */
    private function blankModel()
    {
        return new Documentation();
    }

    /**
     * Computed property to list paginated documentation.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getDocsProperty()
    {
        return $this->applyPagination(
            Documentation::defaultOrder()
        );
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administration.documentation.index', [
            'docs' => $this->docs,
        ])->layout('layouts.app');
    }

    /**
     * Displays the modal and defines the resource to be deleted.
     *
     * @param \App\Models\Documentation $doc
     *
     * @return void
     */
    public function setDeleteDocumentation(Documentation $doc)
    {
        $this->authorize(Policy::Delete->value, Documentation::class);

        $this->deleting = $doc;

        $this->show_delete_modal = true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return void
     */
    public function destroy()
    {
        $this->authorize(Policy::Delete->value, Documentation::class);

        $deleted = $this->deleting->delete();

        $this->fill([
            'show_delete_modal' => false,
            'deleting' => $this->blankModel(),
        ]);

        $this->notify($deleted);
    }
}
