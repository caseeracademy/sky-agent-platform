@php
    $scholarship = $scholarship;
    $university = $scholarship->university;
    $degree = $scholarship->degree;
    $isProgress = $scholarship->status === 'in_progress';
@endphp

<style>
.system-header {
    background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
    border-radius: 16px;
    padding: 2rem;
    color: white;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    position: relative;
    overflow: hidden;
}

.system-header::before {
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

.system-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    letter-spacing: -0.025em;
}

.system-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
    margin-bottom: 2rem;
    font-weight: 300;
}

.system-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}

.system-stat-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 1.5rem 1rem;
    text-align: center;
    transition: all 0.3s ease;
}

.system-stat-card:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.system-stat-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.system-stat-label {
    font-size: 0.875rem;
    opacity: 0.8;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.system-status-badge {
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

.system-status-progress {
    background: rgba(251, 191, 36, 0.9);
    border: 2px solid rgba(251, 191, 36, 0.3);
}

.system-status-earned {
    background: rgba(16, 185, 129, 0.9);
    border: 2px solid rgba(16, 185, 129, 0.3);
}

@media (max-width: 768px) {
    .system-header {
        padding: 1.5rem;
    }
    
    .system-title {
        font-size: 2rem;
    }
    
    .system-subtitle {
        font-size: 1.125rem;
    }
    
    .system-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .system-status-badge {
        position: static;
        margin-top: 1rem;
        display: inline-block;
    }
}
</style>

<div class="system-header">
    <div class="system-status-badge {{ $isProgress ? 'system-status-progress' : 'system-status-earned' }}">
        {{ $isProgress ? 'In Progress' : 'Earned' }}
    </div>
    
    <h1 class="system-title">
        System Scholarship Progress
    </h1>
    
    <p class="system-subtitle">
        {{ $university->name ?? 'Unknown University' }} • {{ $degree->name ?? 'Unknown Degree' }}
    </p>
    
    <div class="system-stats-grid">
        <div class="system-stat-card">
            <div class="system-stat-value">{{ $scholarship->total_students ?? 0 }}</div>
            <div class="system-stat-label">Total Students</div>
        </div>
        
        @if($isProgress)
            <div class="system-stat-card">
                <div class="system-stat-value">{{ $scholarship->current_cycle_progress ?? 0 }}</div>
                <div class="system-stat-label">Current Cycle</div>
            </div>
            <div class="system-stat-card">
                <div class="system-stat-value">{{ $scholarship->students_per_system_scholarship ?? 20 }}</div>
                <div class="system-stat-label">Per Award</div>
            </div>
            <div class="system-stat-card">
                <div class="system-stat-value">{{ $scholarship->progress_percentage ?? 0 }}%</div>
                <div class="system-stat-label">Complete</div>
            </div>
        @else
            <div class="system-stat-card">
                <div class="system-stat-value">{{ $scholarship->system_scholarships_earned ?? 0 }}</div>
                <div class="system-stat-label">Scholarships</div>
            </div>
            <div class="system-stat-card">
                <div class="system-stat-value">{{ count($scholarship->contributing_agents ?? []) }}</div>
                <div class="system-stat-label">Agents</div>
            </div>
        @endif
    </div>
</div>
