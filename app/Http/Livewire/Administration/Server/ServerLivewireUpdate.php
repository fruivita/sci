<?php

namespace App\Http\Livewire\Administration\Server;

use App\Enums\Policy;
use App\Http\Livewire\Traits\WithCheckboxActions;
use App\Http\Livewire\Traits\WithFeedbackEvents;
use App\Http\Livewire\Traits\WithPerPagePagination;
use App\Http\Livewire\Traits\WithPreviousNext;
use App\Models\Server;
use App\Models\Site;
use App\Traits\WithCaching;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ServerLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use WithCaching;
    use WithCheckboxActions;
    use WithFeedbackEvents;
    use WithPerPagePagination;
    use WithPreviousNext;

    /**
     * Servidor que está em edição.
     *
     * @var \App\Models\Server
     */
    public Server $server;

    /**
     * Regras para a validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'selected' => [
                'bail',
                'nullable',
                'array',
                'exists:sites,id',
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
            'selected' => __('Sites'),
        ];
    }

    /**
     * Objeto base que será utilizado definir os ids do registro anterior do
     * próximo.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function workingModel()
    {
        return $this->server;
    }

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::Update->value, Server::class);
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
        $this->server->load(['sites' => function ($query) {
            $query->select('id');
        }]);
    }

    /**
     * Computed property para a listar as localidades paginadas.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSitesProperty()
    {
        return $this->applyPagination(Site::defaultOrder());
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administration.server.edit', [
            'sites' => $this->sites,
        ])->layout('layouts.app');
    }

    /**
     * Atualiza o servidor em edição.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $saved = $this->server->sites()->sync($this->selected);

        $this->flashSelf(is_array($saved));
    }

    /**
     * Reseta a propriedade checkbox_action se houver navegação entre as
     * páginas, isto é, caso o usuário navegue para outra página.
     *
     * Útil para que o usuário tenha que definir o comportamento desejado para
     * os checkboxs na página seguinte.
     *
     * @return void
     */
    public function updatedPaginators()
    {
        $this->reset('checkbox_action');
    }

    /**
     * Todos as linhas (ids dos checkbox) que devem ser selecionados no
     * carregamento inicial (mount) da página.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function rowsToCheck()
    {
        return $this->server->sites;
    }

    /**
     * Todos as linhas (ids dos checkbox) disponíveis para seleção.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function allCheckableRows()
    {
        $this->useCache();

        return $this->cache(
            key: 'all-checkable' . $this->id,
            seconds: 60,
            callback: function () {
                return Site::select('id')->get();
            }
        );
    }

    /**
     * Range das linhas (ids dos checkboxs) disponíveis para seleção. Em regra,
     * as linhas atualmente exibidas na página.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function currentlyCheckableRows()
    {
        return $this->sites;
    }
}
