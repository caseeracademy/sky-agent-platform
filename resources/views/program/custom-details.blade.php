<div class="program-details-wrapper">
    <style>
        .program-details-wrapper {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: transparent;
            min-height: auto;
            color: #333;
            padding: 2rem;
            margin: -2rem;
            position: relative;
            z-index: 1;
        }

        .program-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
        }

        /* Header Section */
        .program-header {
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

        .program-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: white;
            margin: 0 auto 1.5rem;
        }

        .program-name {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .program-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f8fafc;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-size: 0.9rem;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .meta-emoji {
            font-size: 1.1rem;
        }

        /* Statistics Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
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
            color: #1f2937;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-emoji {
            font-size: 1.5rem;
        }

        /* Applications Section */
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .application-info {
            flex: 1;
        }

        .student-name {
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .application-id {
            color: #6b7280;
            font-size: 0.8rem;
            margin: 0;
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

        .application-details {
            display: grid;
            gap: 0.5rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
        }

        .detail-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .detail-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Program Information Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .program-details-wrapper {
                padding: 1rem;
                margin: -1rem;
                min-height: auto;
            }

            .program-header {
                padding: 2rem 1.5rem;
            }

            .program-name {
                font-size: 2rem;
            }

            .program-meta {
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

            .applications-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .program-details-wrapper {
                background: transparent;
                color: #e2e8f0;
            }

            .program-header,
            .stat-card,
            .content-section {
                background: #1f2937;
                color: #e2e8f0;
            }

            .program-name {
                color: #e2e8f0;
            }

            .section-title {
                color: #e2e8f0;
            }

            .application-card {
                background: #374151;
                border-color: rgba(102, 126, 234, 0.2);
            }

            .student-name {
                color: #e2e8f0;
            }

            .info-item {
                background: #374151;
            }

            .detail-value,
            .info-value {
                color: #e2e8f0;
            }
        }
    </style>
    
    <div class="program-container">
        <!-- Program Header -->
        <div class="program-header">
            <div class="program-logo">
                {{ substr($program->name, 0, 1) }}
            </div>
            <h1 class="program-name">{{ $program->name }}</h1>
            <div class="program-meta">
                <span class="meta-item">
                    <span class="meta-emoji">🏫</span> {{ $program->university->name }}
                </span>
                <span class="meta-item">
                    <span class="meta-emoji">🎓</span> {{ $program->degree->name ?? $program->degree_type }}
                </span>
                <span class="meta-item">
                    <span class="meta-emoji">💰</span> ${{ number_format($program->tuition_fee, 2) }}
                </span>
                <span class="meta-item status-{{ $program->is_active ? 'active' : 'inactive' }}">
                    <span class="meta-emoji">🟢</span> {{ $program->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-value">{{ $program->applications->count() }}</div>
                <div class="stat-label">Total Applications</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value">{{ $program->applications->where('status', 'approved')->count() }}</div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-value">{{ $program->applications->whereIn('status', ['pending', 'submitted', 'under_review'])->count() }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value">
                    @if($program->applications->count() > 0)
                        {{ round(($program->applications->where('status', 'approved')->count() / $program->applications->count()) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
                <div class="stat-label">Success Rate</div>
            </div>
        </div>

        <!-- Applications Section -->
        @if($program->applications->count() > 0)
        <div class="content-section">
            <h2 class="section-title">
                <span class="section-emoji">📝</span>
                Applications ({{ $program->applications->count() }})
            </h2>
            <div class="applications-grid">
                @foreach($program->applications as $application)
                    <div class="application-card">
                        <div class="application-header">
                            <div class="student-avatar">{{ substr($application->student->name ?? 'N/A', 0, 1) }}</div>
                            <div class="application-info">
                                <h3 class="student-name">{{ $application->student->name ?? 'Unknown Student' }}</h3>
                                <p class="application-id">#{{ $application->application_number }}</p>
                            </div>
                            <span class="application-status status-{{ $application->status }}">
                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                            </span>
                        </div>
                        <div class="application-details">
                            <div class="detail-row">
                                <span class="detail-label">Agent:</span>
                                <span class="detail-value">{{ $application->agent->name ?? 'N/A' }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Language:</span>
                                <span class="detail-value">{{ ucfirst($application->language ?? 'English') }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Commission:</span>
                                <span class="detail-value">${{ number_format($application->commission_amount, 2) }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Applied:</span>
                                <span class="detail-value">{{ $application->created_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Program Information -->
        <div class="content-section">
            <h2 class="section-title">
                <span class="section-emoji">ℹ️</span>
                Program Information
            </h2>
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
                    <div class="info-value">{{ $program->is_active ? 'Active' : 'Inactive' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Added to System</div>
                    <div class="info-value">{{ $program->created_at->format('M j, Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Last Updated</div>
                    <div class="info-value">{{ $program->updated_at->format('M j, Y') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
