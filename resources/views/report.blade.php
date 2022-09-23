@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ __('Report') }} : {{ $report->name; }}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end">< Back</a>
                </div>

                <div class="card-body">

                    <div id="google-line-chart" class="w-100" style="height: 400px;"></div>

                    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
                    <script type="text/javascript">
                        google.charts.load('current', {'packages':['corechart']});
                        google.charts.setOnLoadCallback(drawChart);

                        function drawChart() {
                            var data = google.visualization.arrayToDataTable([
                                ['Date', {!! "'" . implode("','", $currencies) . "'" !!}],
                                @foreach ($data as $date => $rates)
                                ['{{ $date }}', {{ implode(',', (array) $rates) }}],
                                @endforeach
                            ]);

                            var options = {
                                curveType: 'function',
                                legend: { position: 'bottom' }
                            };

                            var chart = new google.visualization.LineChart(document.getElementById('google-line-chart'));

                            chart.draw(data, options);
                        }
                    </script>

                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                @foreach ($currencies as $currency)
                                    <th scope="col">{{ $currency }}</th>
                                @endforeach
                            </tr>
                        </thead>                    
                        <tbody>
                            @foreach ($data as $date => $rates)
                                <tr>
                                    <th scope="col">{{ $date }}</th>
                                    @foreach ($rates as $rate)
                                        <th scope="col">{{ $rate }}</th>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection