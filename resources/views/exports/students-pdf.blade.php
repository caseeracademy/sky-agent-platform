<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Students Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h1 { text-align: center; color: #0ea5e9; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #0ea5e9; color: white; padding: 8px; text-align: left; font-size: 9px; }
        td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 9px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .header { margin-bottom: 20px; }
        .date { text-align: right; color: #666; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sky Blue Consulting - Students Export</h1>
        <p class="date">Generated: {{ date('F j, Y g:i A') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Passport</th>
                <th>Nationality</th>
                <th>Country</th>
                <th>Agent</th>
                <th>Apps</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                <td>{{ $student->id }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->email }}</td>
                <td>{{ $student->phone_number ?? $student->phone ?? 'N/A' }}</td>
                <td>{{ $student->passport_number ?? 'N/A' }}</td>
                <td>{{ $student->nationality ?? 'N/A' }}</td>
                <td>{{ $student->country_of_residence ?? 'N/A' }}</td>
                <td>{{ $student->agent->name ?? 'N/A' }}</td>
                <td>{{ $student->applications()->count() }}</td>
                <td>{{ $student->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <p style="margin-top: 30px; text-align: center; color: #666; font-size: 9px;">
        Total Students: {{ $students->count() }}
    </p>
</body>
</html>

