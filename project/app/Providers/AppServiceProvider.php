<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {


        view()->composer('*', function ($settings) {
            $admin_lang = DB::table('admin_languages')->where('is_default', '=', 1)->first();
            App::setlocale($admin_lang->name);
            $settings->with('gs', DB::table('generalsettings')->find(1));
            $settings->with('seo', DB::table('seotools')->find(1));
            $settings->with('categories', Category::where('status', '=', 1)->get());
            if (Session::has('language')) {
                $data = DB::table('languages')->find(Session::get('language'));
                $data_results = file_get_contents('assets/languages/' . $data->file);
                $lang = json_decode($data_results);
                $settings->with('langg', $lang);
            } else {
                $data = DB::table('languages')->where('is_default', '=', 1)->first();

                $data_results = file_get_contents('assets/languages/' . $data->file);
                $lang = json_decode($data_results);
                $settings->with('langg', $lang);
            }
            if (!Session::has('popup')) {
                $settings->with('visited', 1);
            }
            Session::put('popup', 1);
        });
    }


    public function register()
    {
        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
    }
}
