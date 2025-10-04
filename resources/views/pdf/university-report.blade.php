<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Report - {{ $university->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #6b7280;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section h2 {
            color: #1e40af;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th,
        .info-table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
        }
        .info-table th {
            background-color: #f8fafc;
            font-weight: bold;
            color: #374151;
        }
        .programs-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .programs-table th,
        .programs-table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }
        .programs-table th {
            background-color: #f8fafc;
            font-weight: bold;
            color: #374151;
        }
        .status-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $university->name }}</h1>
        <p>{{ $university->city }}, {{ $university->country }}</p>
        <p>{{ ucfirst($university->university_type) }} University • Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="section">
        <h2>University Overview</h2>
        <table class="info-table">
            <tr>
                <td><strong>University Name</strong></td>
                <td>{{ $university->name }}</td>
            </tr>
            <tr>
                <td><strong>Type</strong></td>
                <td>{{ ucfirst($university->university_type) }}</td>
            </tr>
            <tr>
                <td><strong>Country</strong></td>
                <td>{{ $university->country }}</td>
            </tr>
            <tr>
                <td><strong>City</strong></td>
                <td>{{ $university->city ?: 'Not specified' }}</td>
            </tr>
            <tr>
                <td><strong>Location</strong></td>
                <td>{{ $university->location ?: 'Not specified' }}</td>
            </tr>
            <tr>
                <td><strong>Status</strong></td>
                <td>
                    <span class="status-badge {{ $university->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $university->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Added to System</strong></td>
                <td>{{ $university->created_at->format('F j, Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Key Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_programs'] }}</div>
                <div class="stat-label">Total Programs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['active_programs'] }}</div>
                <div class="stat-label">Active Programs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_applications'] }}</div>
                <div class="stat-label">Total Applications</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['approved_applications'] }}</div>
                <div class="stat-label">Approved Applications</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['success_rate'] }}%</div>
                <div class="stat-label">Success Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">${{ number_format($stats['average_tuition'], 0) }}</div>
                <div class="stat-label">Average Tuition</div>
            </div>
        </div>
    </div>

    @if($university->programs->count() > 0)
        <div class="section page-break">
            <h2>Available Programs</h2>
            <table class="programs-table">
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>Degree Type</th>
                        <th>Tuition Fee</th>
                        <th>Agent Commission</th>
                        <th>Applications</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($university->programs as $program)
                        <tr>
                            <td>{{ $program->name }}</td>
                            <td>{{ $program->degree->name ?? 'N/A' }}</td>
                            <td>${{ number_format($program->tuition_fee ?? 0, 2) }}</td>
                            <td>${{ number_format($program->agent_commission ?? 0, 2) }}</td>
                            <td>{{ $program->applications->count() }}</td>
                            <td>
                                <span class="status-badge {{ $program->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $program->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($university->scholarship_requirements && !empty($university->scholarship_requirements))
        <div class="section">
            <h2>Scholarship Requirements</h2>
            <table class="info-table">
                <thead>
                    <tr>
                        <th>Degree Type</th>
                        <th>System Threshold</th>
                        <th>Agent Threshold</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($university->scholarship_requirements as $requirement)
                        @if(is_array($requirement) && isset($requirement['degree_type']))
                            <tr>
                                <td>{{ $requirement['degree_type'] }}</td>
                                <td>{{ $requirement['system_threshold'] ?? 'N/A' }} students</td>
                                <td>{{ $requirement['agent_threshold'] ?? 'N/A' }} students</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by Sky Blue Consulting System on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>For questions or support, please contact your system administrator.</p>
    </div>
</body>
</html>
