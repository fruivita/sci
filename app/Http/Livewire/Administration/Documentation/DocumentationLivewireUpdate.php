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
class DocumentationLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithFeedbackEvents;

    /**
     * Editing resource.
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
                "unique:docs,app_route_name,{$this->doc->id}",
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
        $this->authorize(Policy::Update->value, Documentation::class);
    }

    /**
     * Renders the component.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administration.documentation.edit')->layout('layouts.app');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $saved = $this->doc->save();

        $this->flashSelf($saved);
    }
}
