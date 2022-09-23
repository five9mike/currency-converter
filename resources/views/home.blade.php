@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{!! $error !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="post">
                        @csrf
                        <h4 class="mb-0">Todays Exchange Rates</h4>
                        <small>Click and select up to 5 currencies to get todays exchange rates.</small>
                        <div class="form-group mb-2">
                            <select class="js-multi-select-currency form-control w-100" id="report-currencies" name="currencies[]" aria-describedby="currency-help" multiple="multiple" required>
                            @foreach ($currencies as $currency_code => $currency_name)
                                <option value="{{ $currency_code }}">{{ $currency_code }} - {{ $currency_name }}</option>
                            @endforeach
                            </select>
                        </div>

                        <h5 id="rates" style="height: 28px;"></h5>

                        <h4 class="mb-0">Historial Exchange Rates</h4>
                        <small>To obtain an historial report for the above rates, enter a name for the report and select the time range.</small>
                        <div class="form-group mb-2">
                            <input type="text" class="form-control" id="report-name" name="name" aria-describedby="name-help" placeholder="Enter report name" autocomplete="off" required>
                        </div>

                        <div class="form-group mb-2">
                            <select class="js-multi-select-range form-control w-100" id="report-range" name="range" aria-describedby="range-help" required>
                            @foreach ($ranges as $months => $range_details)
                                <option value="{{ $months }}">{{ $range_details['label'] }}</option>
                            @endforeach
                            </select>
                        </div>     
                        <button type="submit" class="btn btn-secondary mb-4">Request Report</button>
                    </form>
                    @if (count($reports))
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th scope="col">Id</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Requested</th>
                                    <th scope="col">Status</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>                    
                            <tbody>
                                @foreach ($reports as $report)
                                    <tr>
                                        <td class="col-1">{{ $report->id }}</td>
                                        <td class="col-5">{{ $report->name }}</td>
                                        <td class="col-4">{{ $report->created_at->format('F j, Y, g:i a', time()) }}</td>
                                        <td class="col-2">{{ ucfirst($report->status) }}</td>
                                        <td>
                                            @if ($report->status == 'complete')
                                                <a href="{{ url('/report/'.$report->id) }}" class="btn btn-primary btn-sm float-end px-1 py-0">View</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$('.js-multi-select-currency').select2({
    placeholder: 'Select Currencies',
    maximumSelectionLength: 5,
    allowClear: true
});

$('#report-currencies').on('change', function(e) { 
    $('#rates').html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');

    currencies = $('#report-currencies :selected').map((_, e) => e.value).get();
    response = fetch('{{ url("/convert?currencies=") }}' + currencies)
        .then(response => response.json())
        .then(data => {
            $('#rates').empty();
            Object.entries(data).forEach(([key, value]) => {
                $('#rates').append(`<span class="badge bg-primary me-2">${key} : ${value}</span>`);
            });
        });
});
</script>

@endsection
