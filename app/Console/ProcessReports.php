<?php

namespace App\Console;

use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Services\CurrencyApi;
use App\Models\Report;

class ProcessReports
{
    /**
     * Process all of the pending reports
     *
     * @return void
     */
    public function __invoke()
    {
        // Update all the pending reports to processing status
        $pending_count = Report::where('status', 'pending')
            ->update(['status' => 'processing']);

        // There's no reports to process, exit gracefully
        if ($pending_count == 0)
            return;
        
        // Find all the due reports and start processing them
        $reports = Report::where('status', 'processing')->get();

        foreach ($reports as $report)
        {
            // Find the conversion rates for the date range for the selected currencies
            $start_date = Carbon::createFromDate($report->created_at)->subMonths($report->range)->format('Y-m-d');
            $end_date = Carbon::createFromDate($report->created_at)->format('Y-m-d');
            $currencies = json_decode($report->currencies);            
            $quotes = CurrencyApi::timeframe($start_date, $end_date, $currencies);
            $interval = Arr::get(config('currency.ranges'), $report->range.'.interval');
            $data = []; // Report data

            // Next date that we need to add to the reporting data based on the interval
            $next_date = $start_date;

            foreach ($quotes as $date => $rates)
            {
                // If its not the next date to include skip over it
                if ($next_date != $date)
                    continue;

                // Currencies in the form 'USDAUD'. For clarity, strip the USD off the front
                foreach ($rates as $currency => $rate)
                {
                    $currency = Str::replaceFirst('USD', '', $currency);

                    $data[$date][$currency] = $rate;
                }

                // Assign the next date we should add to the data
                $next_date = date('Y-m-d', strtotime('+1 '.$interval, strtotime($date)));
            }

            $report->data = json_encode($data);
            $report->status = 'complete';
            $report->processed_at = now();

            // Save the generated report and set its status to 'complete'
            $report->save();
        }
    }
}