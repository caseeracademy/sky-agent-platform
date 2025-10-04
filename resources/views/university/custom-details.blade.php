<div class="university-details-wrapper">
    <style>
        .university-details-wrapper {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: transparent;
            min-height: auto;
            color: #333;
            padding: 2rem;
            margin: -2rem;
            position: relative;
            z-index: 1;
        }

        .university-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
        }

        /* Header Section */
        .university-header {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .university-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
        }

        .university-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .university-name {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .university-info {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .info-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 50px;
            color: #667eea;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-active {
            background: rgba(72, 187, 120, 0.1);
            color: #38a169;
        }

        .status-inactive {
            background: rgba(245, 101, 101, 0.1);
            color: #e53e3e;
        }

        /* Statistics Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 1rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.programs { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .stat-icon.degrees { background: rgba(118, 75, 162, 0.1); color: #764ba2; }
        .stat-icon.tuition { background: rgba(240, 147, 251, 0.1); color: #f093fb; }
        .stat-icon.applications { background: rgba(245, 87, 108, 0.1); color: #f5576c; }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #718096;
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Content Sections */
        .content-section {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-icon {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        /* Programs Grid */
        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .program-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .program-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #d1d5db;
        }

        .program-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .program-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .program-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: rgba(72, 187, 120, 0.1);
            color: #38a169;
        }

        .status-inactive {
            background: rgba(245, 101, 101, 0.1);
            color: #e53e3e;
        }

        .program-details {
            display: grid;
            gap: 0.75rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #718096;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .detail-value {
            color: #2d3748;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .detail-value.highlight {
            color: #667eea;
            font-weight: 700;
        }

        /* Applications Grid */
        .applications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .application-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .application-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #d1d5db;
        }

        .application-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .student-info {
            flex: 1;
        }

        .student-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }

        .application-number {
            font-size: 0.8rem;
            color: #718096;
            font-family: monospace;
        }

        .application-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-approved {
            background: rgba(72, 187, 120, 0.1);
            color: #38a169;
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .status-rejected {
            background: rgba(245, 101, 101, 0.1);
            color: #e53e3e;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .info-item {
            padding: 1rem;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }

        .info-label {
            font-size: 0.8rem;
            color: #718096;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-weight: 600;
            color: #2d3748;
        }

        /* Scholarships Section */
        .scholarships-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .scholarship-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .scholarship-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .scholarship-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .scholarship-emoji {
            font-size: 2rem;
        }

        .scholarship-degree {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .scholarship-type {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .thresholds-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .threshold-card {
            background: #f9fafb;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .threshold-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .threshold-emoji {
            font-size: 1.25rem;
        }

        .threshold-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
            margin: 0;
        }

        .threshold-value {
            font-size: 2rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .threshold-label {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .threshold-description {
            font-size: 0.75rem;
            color: #9ca3af;
            line-height: 1.4;
        }

        .agent-threshold {
            border-left: 4px solid #3b82f6;
        }

        .system-threshold {
            border-left: 4px solid #10b981;
        }

        .scholarship-stats {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #e2e8f0;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
        }

        .stat-row:not(:last-child) {
            border-bottom: 1px solid #e2e8f0;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        .stat-value {
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 600;
        }

        .no-scholarships {
            text-align: center;
            padding: 3rem 2rem;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #cbd5e1;
        }

        .no-scholarships-emoji {
            font-size: 3rem;
            display: block;
            margin-bottom: 1rem;
        }

        .no-scholarships-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .no-scholarships-description {
            color: #64748b;
            font-size: 0.9rem;
        }

        .scholarship-summary {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e5e7eb;
        }

        .summary-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .summary-card {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .summary-emoji {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 0.5rem;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .summary-label {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .university-details-wrapper {
                padding: 1rem;
                margin: -1rem;
                min-height: auto;
            }

            .university-header {
                padding: 2rem 1.5rem;
            }

            .university-name {
                font-size: 2rem;
            }

            .university-info {
                flex-direction: column;
                gap: 1rem;
            }

            .stats-section {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .content-section {
                padding: 1.5rem;
            }

            .programs-grid,
            .applications-grid,
            .scholarships-grid {
                grid-template-columns: 1fr;
            }

            .thresholds-grid {
                grid-template-columns: 1fr;
            }

            .summary-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .university-details-wrapper {
                background: transparent;
                color: #e2e8f0;
            }

            .university-header,
            .stat-card,
            .content-section {
                background: #1f2937;
                color: #e2e8f0;
            }

            .university-name {
                color: #e2e8f0;
            }

            .stat-number {
                color: #e2e8f0;
            }

            .section-title {
                color: #e2e8f0;
            }

            .program-card,
            .application-card,
            .scholarship-card {
                background: #374151;
                border-color: rgba(102, 126, 234, 0.2);
            }

            .threshold-card {
                background: #4b5563;
                border-color: rgba(102, 126, 234, 0.2);
            }

            .scholarship-stats,
            .summary-card {
                background: #4b5563;
                border-color: rgba(102, 126, 234, 0.2);
            }

            .no-scholarships {
                background: #374151;
                border-color: rgba(102, 126, 234, 0.3);
            }

            .program-name,
            .student-name {
                color: #e2e8f0;
            }

            .detail-label,
            .info-label {
                color: #a0aec0;
            }

            .detail-value,
            .info-value {
                color: #e2e8f0;
            }
        }
    </style>
    
    <div class="university-container">
        <!-- University Header -->
        <div class="university-header">
            <div class="university-logo">
                {{ substr($university->name, 0, 1) }}
            </div>
            <h1 class="university-name">{{ $university->name }}</h1>
            <div class="university-info">
                <div class="info-badge">
                    <span>🏛️</span>
                    {{ ucfirst($university->university_type) }} University
                </div>
                <div class="info-badge">
                    <span>📍</span>
                    {{ $university->city ? $university->city . ', ' : '' }}{{ $university->country }}
                </div>
                <div class="status-indicator {{ $university->is_active ? 'status-active' : 'status-inactive' }}">
                    <span>{{ $university->is_active ? '✅' : '❌' }}</span>
                    {{ $university->is_active ? 'Active' : 'Inactive' }}
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon programs">📚</div>
                <div class="stat-number">{{ $university->programs()->count() }}</div>
                <div class="stat-label">Total Programs</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon degrees">🎓</div>
                <div class="stat-number">{{ $university->programs()->with('degree')->get()->pluck('degree.name')->unique()->count() }}</div>
                <div class="stat-label">Degree Types</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon tuition">💰</div>
                <div class="stat-number">${{ number_format($university->programs()->where('is_active', true)->avg('tuition_fee') ?? 0, 0) }}</div>
                <div class="stat-label">Avg. Tuition</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon applications">📋</div>
                <div class="stat-number">{{ $university->programs()->withCount('applications')->get()->sum('applications_count') }}</div>
                <div class="stat-label">Applications</div>
            </div>
        </div>

        <!-- Programs Section -->
        @if($university->programs()->count() > 0)
        <div class="content-section">
            <h2 class="section-title">
                <div class="section-icon">📚</div>
                Available Programs ({{ $university->programs()->count() }})
            </h2>
            <div class="programs-grid">
                @foreach($university->programs()->with(['degree'])->orderBy('name')->get() as $program)
                <div class="program-card">
                    <div class="program-header">
                        <div>
                            <div class="program-name">{{ $program->name }}</div>
                            <div class="program-status {{ $program->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $program->is_active ? 'Active' : 'Inactive' }}
                            </div>
                        </div>
                    </div>
                    <div class="program-details">
                        <div class="detail-row">
                            <span class="detail-label">Degree Type:</span>
                            <span class="detail-value">{{ $program->degree->name ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tuition Fee:</span>
                            <span class="detail-value highlight">${{ number_format($program->tuition_fee ?? 0, 2) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Agent Commission:</span>
                            <span class="detail-value highlight">${{ number_format($program->agent_commission ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Applications Section -->
        @if($university->programs()->withCount('applications')->get()->sum('applications_count') > 0)
        <div class="content-section">
            <h2 class="section-title">
                <div class="section-icon">📋</div>
                Recent Applications ({{ $university->programs()->withCount('applications')->get()->sum('applications_count') }})
            </h2>
            <div class="applications-grid">
                @foreach($university->programs()->with(['applications.student', 'applications.agent'])->get()->pluck('applications')->flatten()->take(6) as $application)
                <div class="application-card">
                    <div class="application-header">
                        <div class="student-avatar">
                            {{ substr($application->student->name ?? 'N/A', 0, 1) }}
                        </div>
                        <div class="student-info">
                            <div class="student-name">{{ $application->student->name ?? 'Unknown Student' }}</div>
                            <div class="application-number">#{{ $application->application_number }}</div>
                        </div>
                        <div class="application-status status-{{ $application->status }}">
                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                        </div>
                    </div>
                    <div class="program-details">
                        <div class="detail-row">
                            <span class="detail-label">Program:</span>
                            <span class="detail-value">{{ $application->program->name ?? 'Unknown Program' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Agent:</span>
                            <span class="detail-value">{{ $application->agent->name ?? 'Unknown Agent' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Submitted:</span>
                            <span class="detail-value">{{ $application->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Scholarships Section -->
        <div class="content-section">
            <div class="section-header">
                <h2 class="section-title">🎓 Scholarship Information</h2>
                <span class="section-badge">Scholarship Thresholds & Requirements</span>
            </div>
            
            @if($university->hasAnyScholarshipRequirements())
                <div class="scholarships-grid">
                    @foreach($university->programs->groupBy('degree.name') as $degreeName => $programs)
                        @php
                            $degreeType = $programs->first()->degree_type ?? $degreeName;
                            $requirement = $university->getScholarshipRequirementForDegree($degreeType);
                        @endphp
                        
                        @if($requirement)
                        <div class="scholarship-card">
                            <div class="scholarship-header">
                                <span class="scholarship-emoji">🎓</span>
                                <h3 class="scholarship-degree">{{ $degreeName }}</h3>
                                <span class="scholarship-type">{{ $degreeType }}</span>
                            </div>
                            
                            <div class="thresholds-grid">
                                <div class="threshold-card agent-threshold">
                                    <div class="threshold-header">
                                        <span class="threshold-emoji">👤</span>
                                        <h4 class="threshold-title">Agent Threshold</h4>
                                    </div>
                                    <div class="threshold-details">
                                        <div class="threshold-value">{{ $requirement['agent_threshold'] ?? $requirement['min_students'] ?? 5 }}</div>
                                        <div class="threshold-label">Students Required</div>
                                        <div class="threshold-description">Minimum students needed for agent scholarship eligibility</div>
                                    </div>
                                </div>
                                
                                <div class="threshold-card system-threshold">
                                    <div class="threshold-header">
                                        <span class="threshold-emoji">🏢</span>
                                        <h4 class="threshold-title">System Threshold</h4>
                                    </div>
                                    <div class="threshold-details">
                                        <div class="threshold-value">{{ $requirement['system_threshold'] ?? $requirement['min_agent_scholarships'] ?? 4 }}</div>
                                        <div class="threshold-label">Agent Scholarships</div>
                                        <div class="threshold-description">Minimum agent scholarships for system scholarship</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="scholarship-stats">
                                <div class="stat-row">
                                    <span class="stat-label">Current Agent Scholarships:</span>
                                    <span class="stat-value">{{ $university->scholarshipAwards()->where('degree_type', $degreeType)->count() }}</span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">System Scholarships Earned:</span>
                                    <span class="stat-value">{{ $university->systemScholarshipAwards()->where('degree_type', $degreeType)->sum('system_scholarships_earned') ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="no-scholarships">
                    <span class="no-scholarships-emoji">📝</span>
                    <h3 class="no-scholarships-title">No Scholarship Requirements Set</h3>
                    <p class="no-scholarships-description">This university doesn't have any scholarship thresholds configured yet.</p>
                </div>
            @endif
            
            <!-- Scholarship Awards Summary -->
            <div class="scholarship-summary">
                <h3 class="summary-title">📊 Scholarship Awards Summary</h3>
                <div class="summary-grid">
                    <div class="summary-card">
                        <span class="summary-emoji">👤</span>
                        <div class="summary-value">{{ $university->scholarshipAwards()->count() }}</div>
                        <div class="summary-label">Agent Scholarships</div>
                    </div>
                    <div class="summary-card">
                        <span class="summary-emoji">🏢</span>
                        <div class="summary-value">{{ $university->systemScholarshipAwards()->count() }}</div>
                        <div class="summary-label">System Scholarships</div>
                    </div>
                    <div class="summary-card">
                        <span class="summary-emoji">💰</span>
                        <div class="summary-value">${{ number_format($university->systemScholarshipAwards()->sum('system_scholarships_earned') ?? 0, 2) }}</div>
                        <div class="summary-label">Total System Earnings</div>
                    </div>
                    <div class="summary-card">
                        <span class="summary-emoji">📈</span>
                        <div class="summary-value">{{ $university->scholarshipAwards()->where('status', 'paid')->count() }}</div>
                        <div class="summary-label">Paid Scholarships</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- University Information -->
        <div class="content-section">
            <h2 class="section-title">
                <div class="section-icon">ℹ️</div>
                University Information
            </h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">University Type</div>
                    <div class="info-value">{{ ucfirst($university->university_type) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Country</div>
                    <div class="info-value">{{ $university->country }}</div>
                </div>
                @if($university->city)
                <div class="info-item">
                    <div class="info-label">City</div>
                    <div class="info-value">{{ $university->city }}</div>
                </div>
                @endif
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">{{ $university->is_active ? 'Active' : 'Inactive' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Added to System</div>
                    <div class="info-value">{{ $university->created_at->format('M j, Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Last Updated</div>
                    <div class="info-value">{{ $university->updated_at->format('M j, Y') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
