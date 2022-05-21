<?php

namespace App\Http\Livewire\Administration\Documentation;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Models\Documentation;
use App\Rules\RouteExists;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class DocumentationLivewireCreate extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;

    /**
     * Resource that will be created.
     *
     * @var \App\Models\Documentation
     */
    public Documentation $doc;

    /**
     * Rules for validation of inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'doc.app_route_name' => [
                'bail',
                'required',
                'string',
                'max:255',
                new RouteExists(),
                'unique:docs,app_route_name',
            ],

            'doc.doc_link' => [
                'bail',
                'nullable',
                'string',
                'max:255',
                'url',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, mixed>
     */
    protected function validationAttributes()
    {
        return [
            'doc.app_route_name' => __('Route name'),
            'doc.doc_link' => __('Documentation link'),
        ];
    }

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::Create->value, Documentation::class);
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
        $this->doc = $this->blankModel();
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
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administration.documentation.create')->layout('layouts.app');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $saved = $this->doc->save();

        $this->doc = $this->blankModel();

        $this->flashSelf($saved);
    }
}
