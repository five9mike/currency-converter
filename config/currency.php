<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency we are comparing all rates against in the reports 
    |
    */

    'default' => env('CURRENCY_DEFAULT', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    |
    | The URL and key we need to access the currency API 
    |
    */
    
    'api' => [
    	'url' => env('CURRENCY_API_URL', 'https://api.apilayer.com/currency_data'),
    	'key' => env('CURRENCY_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Reporting Ranges
    |--------------------------------------------------------------------------
    |
    | The range/interval options we can compile reports for. Selectable in the
    | report generation page and also accessed in \App\Console\ProcessReports.
    | The array key is the number of months, label what should be show in the
    | drop list and the interval how we should itemize the reporting data.
    |
    */

    'ranges' => [
    	12 => [
    		'label' => 'Range: One Year, Interval: Monthly',
    		'interval' => 'month',
    	],
    	6 => [
    		'label' => 'Range: Six Months, Interval: Weekly',
    		'interval' => 'week',
    	],
    	1 => [
    		'label' => 'Range: One Month, Interval: Daily',
    		'interval' => 'day',
    	],
    ],

];