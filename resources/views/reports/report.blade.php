<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap');

        body {
            margin-bottom: 1rem;
            font-family: 'Roboto', sans-serif !important;
        }

        h1 {
            font-size: 1.125rem;
            font-weight: 700;
        }

        p {
            font-size: 0.75rem;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .table-report {
            min-width: 100%;
            border-collapse: collapse;
        }

        .table-report thead {
            background-color: #f3f4f6;
        }

        .table-report th {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            color: #000;
            border: 1px solid #000;
        }

        .table-report tbody {
            background-color: #fff;
        }

        .table-report td {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
            font-size: 0.75rem;
            border: 1px solid #000;
            white-space: nowrap;
        }

        .table-report tfoot {
            background-color: #f3f4f6;
        }

        .table-report tfoot td {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: right;
            color: #000;
            border: 1px solid #000;
            white-space: nowrap;
        }

        .table-report tfoot td:first-child {
            text-align: center !important;
        }

        .\!text-center {
            text-align: center !important;
        }

        .\!text-right {
            text-align: right !important;
        }

        .\!whitespace-normal {
            white-space: normal !important;
        }

        .break-all {
            word-break: break-all;
        }

        .w-6 {
            width: 1.5rem
                /* 24px */
            ;
        }
    </style>
</head>

<body>
    <div class="mb-4 text-center">
        <h1 class="text-lg font-bold">{{ $title }}</h1>
        @if ($report_type == 'stock')
            <p class="text-xs">{{ $brand ? "Brand $brand" : 'Semua Brand' }}</p>
        @else
            <p class="text-xs">Periode {{ $start_date ?? 'awal' }} sampai {{ $end_date ?? 'akhir' }}</p>
        @endif
    </div>

    <div class="mb-2">
        <p class="text-xs">Dicetak pada: {{ $printed_at }}</p>
    </div>

    @if (count($report) === 0)
        <p class="text-center text-red-500">Tidak ada data penjualan untuk periode ini.</p>
    @else
        @include("reports.table.{$report_type}_table", ['report' => $report, 'totals' => $totals])
    @endif
</body>

</html>
