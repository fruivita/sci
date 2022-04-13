<?php

namespace App\Http\Livewire;

use App\Enums\FeedbackType;
use Livewire\Component;

/**
 * Caixa de mensagem de retorno dada à solicitação do usuário.
 *
 * Idealizada para mensagens mais instrusivas, isto é, mensagens que vao se
 * sobrepor à página exibida, exigindo iteração do usuário para fechá-la.
 * Para feedbacks menos intrusivos, utilizar a trait WithFeedbackEvents inline.
 *
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see \App\Http\Livewire\Traits\WithFeedbackEvents
 */
class Flash extends Component
{
    /**
     * Classe Tailwindcss que será aplicada para controlar a visibilidade do
     * componente.
     *
     * @var string
     */
    public $visible = 'hidden';

    /**
     * Classe Tailwindcss que será aplicadas de acordo com o tipo de retorno
     * que será dado à solicitação do usuário.
     *
     * @var string
     */
    public $css;

    /**
     * Icone SVG que será exibido na caixa de mensagem.
     *
     * @var string
     */
    public $icon;

    /**
     * Título da mensagem.
     *
     * @var string
     */
    public $header;

    /**
     * Mensagem.
     *
     * @var string
     */
    public $message;

    /**
     * @var array
     *
     * @see https://laravel-livewire.com/docs/2.x/events#event-listeners
     */
    protected $listeners = ['showFlash' => 'showFlash'];

    /**
     * Configura o componente e o exibe.
     *
     * @param string $type    tipo de mensagem que será exibida (success e error)
     * @param string $message mensagem que será exibida
     *
     * @return void
     */
    public function showFlash(string $type, string $message)
    {
        $this->{$type}();
        $this->message = $message;
        $this->visible = '';
    }

    /**
     * Esconde o componente voltando suas propriedades ao estado original.
     *
     * @return void
     */
    public function hide()
    {
        $this->reset();
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.flash');
    }

    /**
     * Caixa de mensagem do tipo success.
     *
     * @return void
     */
    private function success()
    {
        $this->css = FeedbackType::Success->value;
        $this->icon = FeedbackType::Success->icon();
        $this->header = FeedbackType::Success->label();
    }

    /**
     * Caixa de mensagem do tipo error.
     *
     * @return void
     */
    private function error()
    {
        $this->css = FeedbackType::Error->value;
        $this->icon = FeedbackType::Error->icon();
        $this->header = FeedbackType::Error->label();
    }
}
