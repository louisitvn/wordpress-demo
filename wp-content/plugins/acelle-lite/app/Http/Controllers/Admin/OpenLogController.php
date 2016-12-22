<?php

namespace Acelle\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;

class OpenLogController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->getOption('backend', 'report_open_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = \Acelle\Model\OpenLog::getAll();

        return view('admin.open_logs.index', [
            'items' => $items,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        if ($request->user()->getOption('backend', 'report_open_log') == 'no') {
            return $this->notAuthorized();
        }

        $items = \Acelle\Model\OpenLog::search($request)->paginate($request->per_page);

        return view('admin.open_logs._list', [
            'items' => $items,
        ]);
    }
}