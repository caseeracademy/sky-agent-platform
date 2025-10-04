<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Programs Report</title>
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
            max-width: 1200px;
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
        .programs-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
        }
        .programs-table th,
        .programs-table td {
            padding: 15px 12px;
            text-align: left;
            border: 1px solid #e5e5e5;
            background: white;
        }
        .programs-table th {
            background: white;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e5e5e5;
        }
        .programs-table tr:nth-child(even) {
            background: white;
        }
        .programs-table tr:hover {
            background: white;
        }
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
            color: #666;
            font-style: italic;
            padding: 20px;
            border: 1px solid #ddd;
        }
        @media print {
            .container {
                box-shadow: none;
                padding: 0;
            }
            .university-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Programs</h1>
            <p>{{ now()->format('F j, Y') }}</p>
        </div>

        <!-- Programs Table -->
        @if($programs->count() > 0)
            <table class="programs-table">
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>University</th>
                        <th>Degree</th>
                        <th>Fee</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($programs as $program)
                        <tr>
                            <td>{{ $program->name }}</td>
                            <td>{{ $program->university->name }}</td>
                            <td>{{ $program->degree->name ?? $program->degree_type }}</td>
                            <td>${{ number_format($program->tuition_fee, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                No programs found in the system.
            </div>
        @endif

    </div>
</body>
</html>
