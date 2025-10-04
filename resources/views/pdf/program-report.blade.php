<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $program->name }} - Program Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h2 {
            color: #007bff;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            color: #212529;
            font-size: 16px;
            margin-top: 5px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .applications-table th,
        .applications-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .applications-table th {
            background: #007bff;
            color: white;
            font-weight: bold;
        }
        .applications-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-approved { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 12px;
        }
        .no-data {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $program->name }}</h1>
            <p>Program Report - Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <!-- Program Information -->
        <div class="section">
            <h2>Program Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">University</div>
                    <div class="info-value">{{ $program->university->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Degree Type</div>
                    <div class="info-value">{{ $program->degree->name ?? $program->degree_type }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tuition Fee</div>
                    <div class="info-value">${{ number_format($program->tuition_fee, 2) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Agent Commission</div>
                    <div class="info-value">${{ number_format($program->agent_commission, 2) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">System Commission</div>
                    <div class="info-value">${{ number_format($program->system_commission, 2) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge {{ $program->is_active ? 'status-approved' : 'status-rejected' }}">
                            {{ $program->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="section">
            <h2>Application Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['total_applications'] }}</div>
                    <div class="stat-label">Total Applications</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['approved_applications'] }}</div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['success_rate'] }}%</div>
                    <div class="stat-label">Success Rate</div>
                </div>
            </div>
        </div>

        <!-- Applications -->
        <div class="section">
            <h2>Applications ({{ $program->applications->count() }})</h2>
            @if($program->applications->count() > 0)
                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>Application #</th>
                            <th>Student</th>
                            <th>Agent</th>
                            <th>Language</th>
                            <th>Status</th>
                            <th>Commission</th>
                            <th>Applied Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($program->applications as $application)
                            <tr>
                                <td>{{ $application->application_number }}</td>
                                <td>{{ $application->student->name ?? 'Unknown' }}</td>
                                <td>{{ $application->agent->name ?? 'Unknown' }}</td>
                                <td>{{ ucfirst($application->language ?? 'English') }}</td>
                                <td>
                                    <span class="status-badge status-{{ $application->status }}">
                                        {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                    </span>
                                </td>
                                <td>${{ number_format($application->commission_amount, 2) }}</td>
                                <td>{{ $application->created_at->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">No applications found for this program.</div>
            @endif
        </div>

        <div class="footer">
            <p>Report generated by Sky Blue Consulting System</p>
            <p>Page 1 of 1</p>
        </div>
    </div>
</body>
</html>
