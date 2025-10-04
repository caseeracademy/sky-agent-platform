<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $university->name }} - Programs Report</title>
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
            max-width: 1000px;
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
            margin-bottom: 30px;
        }
        .section h2 {
            color: #007bff;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
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
        .programs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .programs-table th,
        .programs-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .programs-table th {
            background: #007bff;
            color: white;
            font-weight: bold;
        }
        .programs-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .degree-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .degree-bachelor { background: #d1ecf1; color: #0c5460; }
        .degree-master { background: #fff3cd; color: #856404; }
        .degree-phd { background: #f8d7da; color: #721c24; }
        .degree-diploma { background: #e2e3e5; color: #383d41; }
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
        .university-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .university-info h3 {
            color: #007bff;
            margin: 0 0 10px 0;
        }
        .university-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-value {
            color: #212529;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $university->name }}</h1>
            <p>Programs Report - Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <!-- University Information -->
        <div class="section">
            <div class="university-info">
                <h3>University Details</h3>
                <div class="university-details">
                    <div class="detail-item">
                        <div class="detail-label">Type</div>
                        <div class="detail-value">{{ ucfirst($university->university_type ?? 'Public') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Country</div>
                        <div class="detail-value">{{ $university->country ?? 'Not specified' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">City</div>
                        <div class="detail-value">{{ $university->city ?? 'Not specified' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="section">
            <h2>Program Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['total_programs'] }}</div>
                    <div class="stat-label">Total Programs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['active_programs'] }}</div>
                    <div class="stat-label">Active Programs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['total_applications'] }}</div>
                    <div class="stat-label">Total Applications</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['success_rate'] }}%</div>
                    <div class="stat-label">Success Rate</div>
                </div>
            </div>
        </div>

        <!-- Programs List -->
        <div class="section">
            <h2>Programs ({{ $university->programs->count() }})</h2>
            @if($university->programs->count() > 0)
                <table class="programs-table">
                    <thead>
                        <tr>
                            <th>Program Name</th>
                            <th>Degree Type</th>
                            <th>Tuition Fee</th>
                            <th>Agent Commission</th>
                            <th>Applications</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($university->programs as $program)
                            <tr>
                                <td>{{ $program->name }}</td>
                                <td>
                                    <span class="degree-badge degree-{{ strtolower(str_replace([' ', ' with', ' without'], ['', '', ''], $program->degree->name ?? $program->degree_type)) }}">
                                        {{ $program->degree->name ?? $program->degree_type }}
                                    </span>
                                </td>
                                <td>${{ number_format($program->tuition_fee, 2) }}</td>
                                <td>${{ number_format($program->agent_commission, 2) }}</td>
                                <td>{{ $program->applications->count() }}</td>
                                <td>
                                    <span class="status-badge {{ $program->is_active ? 'status-active' : 'status-inactive' }}">
                                        {{ $program->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $program->created_at->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">No programs found for this university.</div>
            @endif
        </div>

        <div class="footer">
            <p>Report generated by Sky Blue Consulting System</p>
            <p>Page 1 of 1</p>
        </div>
    </div>
</body>
</html>
