<?php

namespace App\Http\Controllers;

class PagesController extends Controller
{
    /**
     * Show the "thank you" page after installing the app.
     *
     * @return \Illuminate\View\View
     */
    public function installed()
    {
        return view('installed');
    }

    /**
     * Show the home page.
     *
     * @return \Illuminate\View\View
     */
    public function home()
    {
        return view('home');
    }

    /**
     * Show the support page.
     *
     * @return \Illuminate\View\View
     */
    public function support()
    {
        return view('support');
    }

    /**
     * Show the privacy page.
     *
     * @return \Illuminate\View\View
     */
    public function privacy()
    {
        return view('privacy');
    }
}
