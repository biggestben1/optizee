@extends('layouts.admin')

@section('title', 'Cumulative Income Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active" aria-current="page">Cumulative Report</li>
@endsection

@section('actions')
<button type="button" class="btn btn-outline-primary" onclick="window.print()">
    <i class="fe fe-printer me-2"></i>Print
</button>
@endsection

@section('content')
@php
    $isSingleDay = $startDate->isSameDay($endDate);
    $periodLabel = $isSingleDay ? $startDate->format('l, M d, Y') : $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
    $quickRanges = [
        'Today' => [today(), today()],
        'Yesterday' => [today()->subDay(), today()->subDay()],
        'This Week' => [today()->startOfWeek(), today()],
        'This Month' => [today()->startOfMonth(), today()],
    ];
    $otherRefunds = $areaRefunds['other'] ?? 0;
@endphp

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fe fe-filter me-1"></i>Filter</button>
            </div>
            <div class="col-md-4 d-flex flex-wrap gap-1 justify-content-md-end">
                @foreach($quickRanges as $label => [$from, $to])
                <a href="{{ route('admin.reports.cumulative', ['start_date' => $from->format('Y-m-d'), 'end_date' => $to->format('Y-m-d')]) }}"
                   class="btn btn-sm {{ $startDate->isSameDay($from) && $endDate->isSameDay($to) ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
                @endforeach
            </div>
        </form>
    </div>
</div>

<h4 class="mb-3">All Hotel Income &mdash; {{ $periodLabel }}</h4>

<div class="row mb-2">
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">Gross Income</h6>
                <h4 class="mb-0">₦{{ number_format($totals['gross'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">Refunds</h6>
                <h4 class="mb-0">-₦{{ number_format($totals['refund_total'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-dark text-white h-100">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">Net Income</h6>
                <h4 class="mb-0">₦{{ number_format($totals['net'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">Cash at Hand</h6>
                <h4 class="mb-0">₦{{ number_format($totals['cash_at_hand'], 2) }}</h4>
                @if($totals['refunds']['cash'] > 0)
                <small class="text-white-50">after ₦{{ number_format($totals['refunds']['cash'], 2) }} cash refunds</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">Transfer</h6>
                <h4 class="mb-0">₦{{ number_format($totals['channels']['transfer'] - $totals['refunds']['transfer'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">POS</h6>
                <h4 class="mb-0">₦{{ number_format($totals['channels']['pos'] - $totals['refunds']['pos'], 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h4 class="card-title mb-0">Income by Area of Work</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th class="text-center">Transactions</th>
                        <th class="text-end">Cash</th>
                        <th class="text-end">Transfer</th>
                        <th class="text-end">POS</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Other</th>
                        <th class="text-end">Gross</th>
                        <th class="text-end">Refunds</th>
                        <th class="text-end">Net</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($areas as $key => $label)
                    @php
                        $split = $areaChannels[$key];
                        $refund = $areaRefunds[$key] ?? 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $label }}</strong></td>
                        <td class="text-center">{{ $counts[$key] }}</td>
                        <td class="text-end">₦{{ number_format($split['cash'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($split['transfer'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($split['pos'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($split['credit'], 2) }}</td>
                        <td class="text-end" title="Mixed payments not split + unpaid balances">₦{{ number_format($split['unsplit'] + $split['outstanding'], 2) }}</td>
                        <td class="text-end fw-bold">₦{{ number_format($totals['areas'][$key], 2) }}</td>
                        <td class="text-end text-danger">{{ $refund > 0 ? '-₦' . number_format($refund, 2) : '-' }}</td>
                        <td class="text-end fw-bold">₦{{ number_format($totals['areas'][$key] - $refund, 2) }}</td>
                    </tr>
                    @endforeach
                    @if($otherRefunds > 0)
                    <tr>
                        <td><strong>Other refunds</strong></td>
                        <td colspan="7"></td>
                        <td class="text-end text-danger">-₦{{ number_format($otherRefunds, 2) }}</td>
                        <td class="text-end fw-bold">-₦{{ number_format($otherRefunds, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="bg-light fw-bold">
                        <td>TOTAL</td>
                        <td class="text-center">{{ array_sum($counts) }}</td>
                        <td class="text-end text-success">₦{{ number_format($totals['channels']['cash'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($totals['channels']['transfer'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($totals['channels']['pos'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($totals['channels']['credit'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($totals['channels']['unsplit'] + $totals['channels']['outstanding'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($totals['gross'], 2) }}</td>
                        <td class="text-end text-danger">-₦{{ number_format($totals['refund_total'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($totals['net'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <small class="text-muted d-block mt-2">
            Bar and Kitchen are split by item category. "Other" covers mixed payments recorded before the cash/transfer split existed and any unpaid balance.
        </small>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Income by Area</h4>
            </div>
            <div class="card-body">
                <canvas id="areaChart" height="260"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Payment Methods</h4>
            </div>
            <div class="card-body">
                <canvas id="channelChart" height="260"></canvas>
            </div>
        </div>
    </div>
</div>

@unless($isSingleDay)
<div class="card mb-4">
    <div class="card-header">
        <h4 class="card-title mb-0">Daily Income Trend</h4>
    </div>
    <div class="card-body">
        <canvas id="dailyChart" height="110"></canvas>
    </div>
</div>
@endunless

<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">Daily Breakdown</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        @foreach($areas as $label)
                        <th class="text-end">{{ $label }}</th>
                        @endforeach
                        <th class="text-end">Gross</th>
                        <th class="text-end">Cash</th>
                        <th class="text-end">Transfer</th>
                        <th class="text-end">POS</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Refunds</th>
                        <th class="text-end">Net</th>
                        <th class="text-end">Cash at Hand</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_reverse($days, true) as $date => $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($date)->format('D, M d, Y') }}</td>
                        @foreach($areas as $key => $label)
                        <td class="text-end">₦{{ number_format($row['areas'][$key], 2) }}</td>
                        @endforeach
                        <td class="text-end fw-bold">₦{{ number_format($row['gross'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($row['channels']['cash'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($row['channels']['transfer'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($row['channels']['pos'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($row['channels']['credit'], 2) }}</td>
                        <td class="text-end text-danger">{{ $row['refund_total'] > 0 ? '-₦' . number_format($row['refund_total'], 2) : '-' }}</td>
                        <td class="text-end fw-bold">₦{{ number_format($row['net'], 2) }}</td>
                        <td class="text-end text-success fw-bold">₦{{ number_format($row['cash_at_hand'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                @unless($isSingleDay)
                <tfoot>
                    <tr class="bg-light fw-bold">
                        <td>TOTAL</td>
                        @foreach($areas as $key => $label)
                        <td class="text-end">₦{{ number_format($totals['areas'][$key], 2) }}</td>
                        @endforeach
                        <td class="text-end">₦{{ number_format($totals['gross'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($totals['channels']['cash'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($totals['channels']['transfer'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($totals['channels']['pos'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($totals['channels']['credit'], 2) }}</td>
                        <td class="text-end text-danger">-₦{{ number_format($totals['refund_total'], 2) }}</td>
                        <td class="text-end">₦{{ number_format($totals['net'], 2) }}</td>
                        <td class="text-end text-success">₦{{ number_format($totals['cash_at_hand'], 2) }}</td>
                    </tr>
                </tfoot>
                @endunless
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .no-print, .app-sidebar, .app-header, .pageheader-btn, .breadcrumb { display: none !important; }
    .card { break-inside: avoid; }
}
</style>
@endpush

@push('scripts')
<script src="/sash/assets/plugins/chart/Chart.bundle.js"></script>
<script>
(function () {
    const chart = @json($chart);
    const palette = ['#5e72e4', '#f5365c', '#2dce89', '#fb6340', '#11cdef', '#8965e0', '#adb5bd'];
    const naira = value => '₦' + Number(value).toLocaleString('en-NG', {minimumFractionDigits: 0, maximumFractionDigits: 2});
    const moneyTooltip = {
        callbacks: {
            label: (item, data) => {
                const dataset = data.datasets[item.datasetIndex];
                const label = dataset.label || data.labels[item.index];
                return label + ': ' + naira(dataset.data[item.index]);
            },
        },
    };

    new Chart(document.getElementById('areaChart'), {
        type: 'bar',
        data: {
            labels: chart.areaLabels,
            datasets: [{
                label: 'Income',
                data: chart.areaValues,
                backgroundColor: palette.slice(0, chart.areaLabels.length),
            }],
        },
        options: {
            legend: {display: false},
            tooltips: moneyTooltip,
            scales: {yAxes: [{ticks: {beginAtZero: true, callback: naira}}]},
        },
    });

    new Chart(document.getElementById('channelChart'), {
        type: 'doughnut',
        data: {
            labels: chart.channelLabels,
            datasets: [{
                data: chart.channelValues,
                backgroundColor: ['#2dce89', '#11cdef', '#fb6340', '#f5365c', '#8965e0', '#adb5bd'],
            }],
        },
        options: {
            legend: {position: 'bottom'},
            tooltips: moneyTooltip,
        },
    });

    const dailyCanvas = document.getElementById('dailyChart');
    if (dailyCanvas) {
        const datasets = chart.dayAreas.map((area, i) => ({
            type: 'bar',
            label: area.label,
            data: area.data,
            backgroundColor: palette[i],
            stack: 'income',
        }));
        datasets.push({
            type: 'line',
            label: 'Net Income',
            data: chart.dayNet,
            borderColor: '#212529',
            backgroundColor: 'transparent',
            fill: false,
            lineTension: 0.2,
        });

        new Chart(dailyCanvas, {
            type: 'bar',
            data: {labels: chart.dayLabels, datasets},
            options: {
                legend: {position: 'bottom'},
                tooltips: Object.assign({mode: 'index', intersect: false}, moneyTooltip),
                scales: {
                    xAxes: [{stacked: true}],
                    yAxes: [{stacked: true, ticks: {beginAtZero: true, callback: naira}}],
                },
            },
        });
    }
})();
</script>
@endpush
