<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Services\CurrencyApi;
use App\Models\Report;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the currency converter dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $currencies = CurrencyApi::list();
        $ranges = config('currency.ranges');
        $reports = Report::orderBy('created_at', 'desc')->get();

        // Pull the default currency out so users cannot select it
        Arr::pull($currencies, config('currency.default'));

        return view('home', [
            'currencies' => $currencies,
            'ranges' => $ranges,
            'reports' => $reports,
        ]);
    }

    /**
     * Saves the report request into the database for scheduled processing
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit()
    {
        $this->validate(request(), [
            'name' => 'bail|required',
            'currencies' => 'bail|required',
            'range' => 'bail|integer|required',
        ]);

        $report = new Report;
       
        $report->name = request('name');
        $report->range = request('range');
        $report->interval = Arr::get(config('currency.ranges'), request('range').'.interval');;
        $report->currencies = json_encode(request('currencies'));
        $report->status = 'pending';

        $report->save();

        session()->flash('success', 'Report successfully scheduled for processing!');

        return redirect()->route('home');
    }

    /**
     * Display an individual report in tabular and linechart form
     *
     * @return \Illuminate\View\View
     */
    public function report($id)
    {
        $report = Report::where('id', $id)
            ->where('status', 'complete')
            ->firstOrFail();

        return view('report', [
            'report' => $report,
            'currencies' => json_decode($report->currencies),
            'data' => json_decode($report->data),
        ]);       
    }

    /**
     * Conversion rates for a comma separated list of currencies via frontend request
     *
     * @return string|json
     */
    public function convert()
    {
        $currency_conversions = Str::of(request('currencies'))->explode(',');
        $currency_list = CurrencyApi::list();

        $rates = [];

        foreach ($currency_conversions as $currency)
        {
            // Validation - check the currency requested is in the full list
            if (Arr::exists($currency_list, $currency))
            {
                $rates[$currency] = CurrencyApi::convert($currency);
            }
        }

        return json_encode($rates);
    }
}
