<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

class CurrencyApi
{
    /**
     * Wrapper function to handle the API requests
     *
     * @param  string    $endpoint    API endpoint we're requesting
     * @param  string    $index       The index of the JSON response we should extract
     * @param  array     $args        The HTTP args we should send along with the request
     * @param  bool|int  $cache_secs  Should we cache? If so pass in how many seconds
     * 
     * @return string|null
     */
    private static function request($endpoint, $index, $args = array(), $cache_secs = false)
    {
    	$cache_key = 'currency:'.$endpoint.':'.md5(serialize($args));

    	// We arent caching or the cache is invalid, load the API
    	if ( ! $cache_secs || ( ! $result = cache($cache_key)))
    	{
	    	$url = config('currency.api.url').'/'.$endpoint;
	    	$key = config('currency.api.key');

			$response = Http::withHeaders(['apikey' => $key])
				->get($url, $args)
				->json();

			$result = Arr::get($response, $index);

			// Assign the cache if there's a time value set
	    	if ($cache_secs)
	    	{
	    		cache([$cache_key => $result], now()->addSeconds($cache_secs));
	    	}
	    }

		return $result;
    }

    /**
     * Obtain the full list of available currencies
     *
     * @return array
     */
	public static function list()
	{
		$cache_secs = 86400; // 1 day in seconds

		return self::request('list', 'currencies', [], $cache_secs);
	}

    /**
     * Get the full list of all conversion rates between two dates for a group of currencies
     * 
     * @param  string  $start_date  Date we start the data from. Format YYYY-MM-DD
     * @param  string  $end_date    Date we end the data at. Format YYYY-MM-DD
     * @param  array   $currencies  An array of currency codes
     *
     * @return array
     */
	public static function timeframe($start_date, $end_date, $currencies = array())
	{
		$args = [
			'start_date' => $start_date,
			'end_date' => $end_date,
			'currencies' => implode(',', $currencies),
		];

		$cache_secs = 86400; // 1 day in seconds

		return self::request('timeframe', 'quotes', $args, $cache_secs);
	}

    /**
     * Find the conversion rate of a currency compared to the app default
     * 
     * @param  string  $currency  Currency code to convert
     * @param  int     $amount    How much of the default we want to convert
     * @param  string  $date      Optional parameter to check exchange rate on specific day, YYYY-MM-DD
     *
     * @return array
     */
	public static function convert($currency, $amount = 1, $date = null)
	{
		$args = [
			'from' => config('currency.default'),
			'to' => $currency,
			'amount' => $amount,
			'date' => $date,
		];

		$cache_secs = 600; // 10 minutes, keep rates relatively fresh

		return self::request('convert', 'result', $args, $cache_secs);
	}
}