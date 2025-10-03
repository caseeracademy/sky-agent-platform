@php
    $scholarship = $scholarship;
    $university = \App\Models\University::find($scholarship->university_id);
    $degree = \App\Models\Degree::find($scholarship->degree_id);
    $isProgress = $scholarship->type === 'progress';
@endphp

<style>
.scholarship-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 2rem;
    color: white;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    position: relative;
    overflow: hidden;
}

.scholarship-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: translate(50%, -50%);
}

.scholarship-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    letter-spacing: -0.025em;
}

.scholarship-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
    margin-bottom: 2rem;
    font-weight: 300;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 1.5rem 1rem;
    text-align: center;
    transition: all 0.3s ease;
}

.stat-card:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.8;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-badge {
    position: absolute;
    top: 2rem;
    right: 2rem;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    z-index: 10;
}

.status-progress {
    background: rgba(59, 130, 246, 0.9);
    border: 2px solid rgba(59, 130, 246, 0.3);
}

.status-earned {
    background: rgba(16, 185, 129, 0.9);
    border: 2px solid rgba(16, 185, 129, 0.3);
}

@media (max-width: 768px) {
    .scholarship-header {
        padding: 1.5rem;
    }
    
    .scholarship-title {
        font-size: 2rem;
    }
    
    .scholarship-subtitle {
        font-size: 1.125rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .status-badge {
        position: static;
        margin-top: 1rem;
        display: inline-block;
    }
}
</style>

<div class="scholarship-header">
    <div class="status-badge {{ $isProgress ? 'status-progress' : 'status-earned' }}">
        {{ $isProgress ? 'In Progress' : 'Earned' }}
    </div>
    
    <h1 class="scholarship-title">
        {{ $scholarship->commission_number ?: ($isProgress ? 'Scholarship in Progress' : 'Earned Scholarship') }}
    </h1>
    
    <p class="scholarship-subtitle">
        {{ $university->name ?? 'Unknown University' }} • {{ $degree->name ?? 'Unknown Degree' }}
    </p>
    
    <div class="stats-grid">
        @if($isProgress)
            <div class="stat-card">
                <div class="stat-value">{{ $scholarship->current_points ?? 0 }}</div>
                <div class="stat-label">Current Points</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $scholarship->threshold ?? 5 }}</div>
                <div class="stat-label">Required</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $scholarship->progress_percentage ?? 0 }}%</div>
                <div class="stat-label">Complete</div>
            </div>
        @else
            <div class="stat-card">
                <div class="stat-value">{{ $scholarship->qualifying_points_count ?? 'N/A' }}</div>
                <div class="stat-label">Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $scholarship->earned_at ? $scholarship->earned_at->format('M j') : 'N/A' }}</div>
                <div class="stat-label">Earned Date</div>
            </div>
        @endif
    </div>
</div>
