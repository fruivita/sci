<?php

namespace App\View\Composers;

use App\Models\Documentation;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/**
 * @see https://laravel.com/docs/views#view-composers
 */
class DocumentationComposer
{
    /**
     * Create a new profile composer.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Bind data to the view.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $view->with(['doc_link' => $this->getDocLink()]);
    }

    /**
     * Gera o link para a documentação de acordo com a rota visitada pelo
     * usuário.
     *
     * @return string url para da documentação
     */
    private function getDocLink()
    {
        $doc_link = optional(
            Documentation::firstWhere('app_route_name', Route::currentRouteName())
        )->doc_link;

        return $doc_link ?? config('app.doc_link_default');
    }
}
