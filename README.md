# Currency Converter

This application allows you to select up to 5 currencies and find their current exchange rates compared to a base currency (defaults to USD). You can also select from 3 different date ranges and generate historical exchange rate reports.

Here's a video walkthrough (you may need to unmute):

https://user-images.githubusercontent.com/114114611/191901284-018e62f0-9b23-42ab-810e-eb9faea78357.mp4

## Installation

- Clone the repo: `git clone https://github.com/five9mike/currency-converter.git`
- Move to the application folder: `cd currency-converter`
- Copy the example env file: `cp .env.example .env`
- Update the necessary `.env` values, specifically the [`DB_*` settings](https://github.com/five9mike/currency-converter/blob/main/.env.example#L11-L16) and the [`CURRENCY_*` settings](https://github.com/five9mike/currency-converter/blob/main/.env.example#L60-L62)
    - Note: You should fill out all the `CURRENCY_*` settings and can [config defaults](https://github.com/five9mike/currency-converter/blob/main/config/currency.php) for the URL/currency default but will need your own API key.
- Install composer packages: `composer install`
- Install npm packages: `npm install`
- Run database migrations: `php artisan migrate`
- Generate unique app key: `php artisan key:generate`

## Running

- From within the application folder execute: `npm run dev`
- In a new terminal serve the application: `php artisan serve`
    - If you are using [Laravel Valet](https://laravel.com/docs/9.x/valet) to serve your applications locally you can safely skip the `serve` step.
- To test the scheduled job: `php artisan schedule:run`
    - You'll need to wait for a 15 minute interval for execution, alternatively change `everyFifteenMinutes` with `everyMinute` [here](https://github.com/five9mike/currency-converter/blob/main/app/Console/Kernel.php#L21).

## Architecture

Below are the key architecutral components of the application and brief explanations of each.

**The HomeController**

Most of the magic happens in the [HomeController](https://github.com/five9mike/currency-converter/blob/main/app/Http/Controllers/HomeController.php). It's responsible for handling the `auth` middleware, displaying the home dashboard, individual reports, handling a report submission and generating the real time conversion rates via API.

**CurrencyApi Service**

To handle interaction with the currency API I've implemented the [CurrencyApi service](https://github.com/five9mike/currency-converter/blob/main/app/Services/CurrencyApi.php). There's functions to find all the currencies, an individual conversion rate and well as historical rates. These all get routed through the services `request` method which handles the json decoding and optional caching.

**ProcessReports Job**

The [ProcessReports](https://github.com/five9mike/currency-converter/blob/main/app/Console/ProcessReports.php) job handles the generation of the requested reports. It first marks any `pending` reports as `processing`. Afterwards, it queries the currency API for the requested currencies in the desired range. It uses the [configurable interval values](https://github.com/five9mike/currency-converter/blob/main/config/currency.php#L42-L55) to identify which dates throughout the range to include in the report. The job is [scheduled to run](https://github.com/five9mike/currency-converter/blob/main/app/Console/Kernel.php#L18-L21) every 15 minutes and is done so `withoutOverlapping`.

**Database Design**

The migration for the [reports table](https://github.com/five9mike/currency-converter/blob/main/database/migrations/2022_09_20_064604_create_reports_table.php) will give you a good idea of its architecture. In short, after a report request is made it stored the range in months and the interval as a human readable time period; ie. month, week, day.

The currencies requested are stored as json in the `currencies` field and once the reporting data is generated its stored as a json object in the `data` field. The `status` field throughout the lifetime if a report can be `pending`, `processing` or `complete` and only `complete` reports can be viewed.

**Google Charts**

Google Charts open source solution has been implemented for the line chart. The implementation can be viewed in the [report view](https://github.com/five9mike/currency-converter/blob/main/resources/views/report.blade.php#L18-L39).

## Improvements

Given more time I believe the following could be implemented to improve the application and make it more secure, efficient and scalable.

**Validation**

There's basic frontend and [backend validation](https://github.com/five9mike/currency-converter/blob/main/app/Http/Controllers/HomeController.php#L52-L56) but this could be made a lot more robust. Specifically, ensure that currencies passed through forms appear in the list and that any range selected is part of the possible configuration options.

**Queue**

I believe its better to dispatch the report processing to a queue rather than rely on the schedule. That way each report gets processed as it comes in rather than waiting for the 15 minute window. A user could wait up to 14:59 for their report to start processing.

**Datawarehouse**

Given the static nature of currency exchange rates all this could be stored in a datawarehouse rather than relying on an API. This would minimize API requests, speed up processing and allow for caching on a more granualar level.

**Vuejs**

For simple applications I think the simpler the better, but of course a separate backend/frontend repo would be more ideal for a client/consumer facing application.

**More Testing**

I've included a [simple unit test](https://github.com/five9mike/currency-converter/tree/main/tests/Feature) to show I know how it works (this can be executed with `php artisan test`) but in a real production application more unit testing is needed to have a higher amount of coverage. 

