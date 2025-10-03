<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Applications Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        h1 { text-align: center; color: #0ea5e9; margin-bottom: 20px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #0ea5e9; color: white; padding: 6px; text-align: left; font-size: 8px; }
        td { padding: 5px; border-bottom: 1px solid #ddd; font-size: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .header { margin-bottom: 20px; }
        .date { text-align: right; color: #666; font-size: 8px; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 7px; font-weight: bold; }
        .badge-success { background-color: #22c55e; color: white; }
        .badge-warning { background-color: #f59e0b; color: white; }
        .badge-danger { background-color: #ef4444; color: white; }
        .badge-info { background-color: #0ea5e9; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sky Blue Consulting - Applications Export</h1>
        <p class="date">Generated: {{ date('F j, Y g:i A') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>App #</th>
                <th>Student</th>
                <th>Program</th>
                <th>University</th>
                <th>Agent</th>
                <th>Status</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applications as $app)
            <tr>
                <td>{{ $app->application_number }}</td>
                <td>{{ $app->student->name ?? 'N/A' }}</td>
                <td>{{ $app->program->name ?? 'N/A' }}</td>
                <td>{{ $app->program->university->name ?? 'N/A' }}</td>
                <td>{{ $app->agent->name ?? 'N/A' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $app->status)) }}</td>
                <td>{{ $app->commission_type ? ucfirst($app->commission_type) : 'Not Set' }}</td>
                <td>${{ number_format($app->commission_amount ?? 0, 2) }}</td>
                <td>{{ $app->submitted_at?->format('Y-m-d') ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <p style="margin-top: 30px; text-align: center; color: #666; font-size: 8px;">
        Total Applications: {{ $applications->count() }}
    </p>
</body>
</html>

