<?php

namespace App\Providers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /*
         * Paginate a standard Laravel Collection.
         *
         * @param int $total
         * @param int $per_page
         * @param int $page
         * @param string $page_name default value 'page'
         * @return \Illuminate\Pagination\LengthAwarePaginator
         *
         * @see https://gist.github.com/simonhamp/549e8821946e2c40a617c85d2cf5af5e
         */
        Collection::macro('customPaginate', function (int $total, int $per_page, int $page, string $page_name = 'page') {
            return new LengthAwarePaginator(
                $this,
                $total,
                $per_page,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $page_name,
                    'query' => LengthAwarePaginator::resolveQueryString(),
                ]
            );
        });
    }
}
