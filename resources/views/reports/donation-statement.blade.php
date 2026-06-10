<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; line-height: 1.45; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        h2 { font-size: 14px; margin: 0 0 16px; color: #444; font-weight: normal; }
        .meta { margin-bottom: 18px; }
        .meta p { margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border-bottom: 1px solid #ddd; padding: 8px 6px; text-align: left; vertical-align: top; }
        th { background: #f5f5f5; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; }
        .amount { text-align: right; white-space: nowrap; }
        .totals { margin-top: 18px; width: 100%; }
        .totals td { border: none; padding: 4px 0; }
        .totals .label { text-align: right; padding-right: 12px; color: #555; }
        .totals .value { text-align: right; font-weight: bold; width: 120px; }
        .footer { margin-top: 28px; font-size: 10px; color: #666; }
        .empty { padding: 24px 0; color: #666; font-style: italic; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <h2>{{ $subtitle }}</h2>

    <div class="meta">
        <p><strong>Parish:</strong> {{ $parish_name }}@if($charity_number) · Charity No. {{ $charity_number }}@endif</p>
        <p><strong>Report:</strong> {{ $scope_label }}</p>
        <p><strong>Period:</strong> {{ $period_label }}</p>
        <p><strong>Status:</strong> {{ $status_filter }}</p>
        <p><strong>Generated:</strong> {{ $generated_at }}</p>
    </div>

    @if ($donations->isEmpty())
        <p class="empty">No giving records were found for this period and filter.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    @if ($show_donor_column)
                        <th>Donor</th>
                    @endif
                    @if ($show_family_column)
                        <th>Family</th>
                    @endif
                    <th>Method</th>
                    <th>Status</th>
                    <th>Reference</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($donations as $donation)
                    <tr>
                        <td>{{ $donation->donated_on->format('j M Y') }}</td>
                        @if ($show_donor_column)
                            <td>{{ $donation->user?->displayFullName() ?? '—' }}</td>
                        @endif
                        @if ($show_family_column)
                            <td>{{ $donation->family?->name ?? '—' }}</td>
                        @endif
                        <td>{{ $donation->methodEnum()?->label() ?? $donation->method }}</td>
                        <td>{{ $donation->statusEnum()->label() }}</td>
                        <td>{{ $donation->reference ?: '—' }}</td>
                        <td class="amount">{{ $donation->formattedAmount() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="label">Approved total</td>
                <td class="value">£{{ number_format($total_approved, 2) }}</td>
            </tr>
            @if ($total_pending > 0)
                <tr>
                    <td class="label">Pending total</td>
                    <td class="value">£{{ number_format($total_pending, 2) }}</td>
                </tr>
            @endif
            @if ($total_rejected > 0)
                <tr>
                    <td class="label">Rejected total</td>
                    <td class="value">£{{ number_format($total_rejected, 2) }}</td>
                </tr>
            @endif
        </table>
    @endif

    <p class="footer">
        This statement is provided for your records. Approved totals reflect gifts verified by the parish office.
        For questions contact the parish office.
    </p>
</body>
</html>
