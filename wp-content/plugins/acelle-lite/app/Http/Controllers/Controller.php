<?php

namespace Acelle\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        // Generate info
        try {
            $user = \Auth::user();
            if (is_object($user->language)) {
                \App::setLocale($user->language->code);
                if(file_exists(base_path("vendor/nesbot/carbon/src/Carbon/Lang/" . $user->language->code . ".php"))) {
                    \Carbon\Carbon::setLocale($user->language->code);
                }
            }
        } catch (\Exception $e) {
        }
    }
    
    /**
     * Check if the site is in demo mode.
     *
     * @return \Illuminate\Http\Response
     */
    function isDemoMode()
    {
        return config('app.demo');
    }

    /**
     * Check if the user is not authorized.
     *
     * @return \Illuminate\Http\Response
     */
    public function notAuthorized()
    {
        return view('notAuthorized');
    }

    /**
     * Check if the user cannot create more item.
     *
     * @return \Illuminate\Http\Response
     */
    public function noMoreItem()
    {
        return view('noMoreItem');
    }

    /**
     * When site status is offline.
     *
     * @return \Illuminate\Http\Response
     */
    public function offline()
    {
        return view('offline');
    }
    
    /**
     * Show demo home page.
     *
     * @return \Illuminate\Http\Response
     */
    public function demo()
    {
        return view('demo');
    }
    
    /**
     * Show demo home page.
     *
     * @return \Illuminate\Http\Response
     */
    public function reloadCache()
    {
        $next = action('HomeController@index');
        \Artisan::call('config:cache');
        sleep(5);
        return redirect()->away($next);
    }
    
    /**
     * Docs for api v1.
     *
     * @return \Illuminate\Http\Response
     */
    public function docsApiV1()
    {
        return view('docs.api.v1');
    }
    
    /**
     * Login from outsite.
     *
     * @return \Illuminate\Http\Response
     */
    public function autoLogin($api_token)
    {
        $user = \Acelle\Model\User::where('api_token', $api_token)->first();

        \Auth::login($user);
        
        return redirect()->action('HomeController@index');
    }

    /**
     * Translate datatable.
     *
     * @return text
     */
    public function datatable_locale()
    {
        echo '{
            "sEmptyTable":     "'.trans('messages.datatable_sEmptyTable').'",
            "sProcessing":   "'.trans('messages.datatable_sProcessing').'",
            "sLengthMenu":   "'.trans('messages.datatable_sLengthMenu').'",
            "sZeroRecords":  "'.trans('messages.datatable_sZeroRecords').'",
            "sInfo":         "'.trans('messages.datatable_sInfo').'",
            "sInfoEmpty":    "'.trans('messages.datatable_sInfoEmpty').'",
            "sInfoFiltered": "'.trans('messages.datatable_sInfoFiltered').'",
            "sInfoPostFix":  "'.trans('messages.datatable_sInfoPostFix').'",
            "sSearch":       "'.trans('messages.datatable_sSearch').'",
            "sUrl":          "'.trans('messages.datatable_sUrl').'",
            "oPaginate": {
                "sFirst":    "'.trans('messages.datatable_sFirst').'",
                "sPrevious": "'.trans('messages.datatable_sPrevious').'",
                "sNext":     "'.trans('messages.datatable_sNext').'",
                "sLast":     "'.trans('messages.datatable_sLast').'"
            },            
            "oAria": {
                "sSortAscending":  "'.trans('messages.datatable_sSortAscending').'",
                "sSortDescending": "'.trans('messages.datatable_sSortDescending').'"
            }
        }';
    }

    /**
     * Translate jqery validate.
     *
     * @return text
     */
    public function jquery_validate_locale()
    {
        echo 'jQuery.extend(jQuery.validator.messages, {
                required: "'.trans('messages.jvalidate_required').'",
                remote: "'.trans('messages.jvalidate_remote').'",
                email: "'.trans('messages.jvalidate_email').'",
                url: "'.trans('messages.jvalidate_url').'",
                date: "'.trans('messages.jvalidate_date').'",
                dateISO: "'.trans('messages.jvalidate_dateISO').'",
                number: "'.trans('messages.jvalidate_number').'",
                digits: "'.trans('messages.jvalidate_digits').'",
                creditcard: "'.trans('messages.jvalidate_creditcard').'",
                equalTo: "'.trans('messages.jvalidate_equalTo').'",
                accept: "'.trans('messages.jvalidate_accept').'",
                maxlength: jQuery.validator.format("'.trans('messages.jvalidate_maxlength').'"),
                minlength: jQuery.validator.format("'.trans('messages.jvalidate_minlength').'"),
                rangelength: jQuery.validator.format("'.trans('messages.jvalidate_rangelength').'"),
                range: jQuery.validator.format("'.trans('messages.jvalidate_range').'"),
                max: jQuery.validator.format("'.trans('messages.jvalidate_max').'"),
                min: jQuery.validator.format("'.trans('messages.jvalidate_min').'")
            });';
    }
    
    /**
     * When site cannot connect to the database. WordPress database configuration change.
     *
     * @return \Illuminate\Http\Response
     */
    public function databaseConnectionError()
    {
        return view('database_connection_error');
    }
}
