@php
    $scholarship = $scholarship ?? null;
    $university = $scholarship->university ?? null;
    $degree = $scholarship->degree ?? null;
@endphp

<style>
.calculation-container {
    background: linear-gradient(135def, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: 16px;
    padding: 2rem;
    border: 1px solid #0ea5e9;
    position: relative;
    overflow: hidden;
}

.calculation-container::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.3) 0%, transparent 70%);
    border-radius: 50%;
}

.calculation-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0c4a6e;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 10;
}

.formula-section {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 2px solid #7dd3fc;
    position: relative;
    z-index: 10;
}

.formula-title {
    font-size: 1rem;
    font-weight: 600;
    color: #0c4a6e;
    margin-bottom: 1rem;
}

.formula-step {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    padding: 0.75rem;
    background: rgba(240, 249, 255, 0.7);
    border-radius: 8px;
    border-left: 4px solid #0ea5e9;
}

.formula-number {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.formula-text {
    color: #0c4a6e;
    font-size: 0.875rem;
    line-height: 1.5;
    font-weight: 500;
}

.example-section {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    padding: 1.5rem;
    border: 2px solid #38bdf8;
    position: relative;
    z-index: 10;
}

.example-title {
    font-size: 1rem;
    font-weight: 600;
    color: #0c4a6e;
    margin-bottom: 1rem;
}

.example-calculation {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 1rem;
    margin: 0.75rem 0;
    font-size: 0.875rem;
    color: #0369a1;
    line-height: 1.6;
}

.highlight-value {
    background: #fbbf24;
    color: #92400e;
    padding: 0.125rem 0.25rem;
    border-radius: 4px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .calculation-container {
        padding: 1.5rem;
    }
    
    .calculation-title {
        font-size: 1.25rem;
    }
    
    .formula-section,
    .example-section {
        padding: 1rem;
    }
}
</style>

<div class="calculation-container">
    <h3 class="calculation-title">System Scholarship Calculation Logic</h3>
    
    <div class="formula-section">
        <h4 class="formula-title">How System Scholarships Work</h4>
        
        <div class="formula-step">
            <div class="formula-number">1</div>
            <div class="formula-text">
                <strong>University Contract:</strong> {{ $university->name ?? 'University' }} gives system 1 scholarship per 
                <span class="highlight-value">{{ $scholarship->university_threshold ?? 4 }}</span> students
            </div>
        </div>
        
        <div class="formula-step">
            <div class="formula-number">2</div>
            <div class="formula-text">
                <strong>Agent Contract:</strong> System gives agents 1 scholarship per 
                <span class="highlight-value">{{ $scholarship->agent_threshold ?? 5 }}</span> students
            </div>
        </div>
        
        <div class="formula-step">
            <div class="formula-number">3</div>
            <div class="formula-text">
                <strong>System Profit:</strong> System earns 1 scholarship every 
                <span class="highlight-value">{{ $scholarship->students_per_system_scholarship ?? 20 }}</span> total students
            </div>
        </div>
        
        <div class="formula-step">
            <div class="formula-number">4</div>
            <div class="formula-text">
                <strong>All Students Count:</strong> Every approved scholarship application contributes to system total, 
                regardless of individual agent completion status
            </div>
        </div>
    </div>
    
    <div class="example-section">
        <h4 class="example-title">Current Calculation for {{ $university->name ?? 'This University' }} {{ $degree->name ?? 'Degree' }}</h4>
        
        <div class="example-calculation">
Total Students Approved: <span class="highlight-value">{{ $scholarship->total_students ?? 0 }}</span><br>
Students Per System Scholarship: <span class="highlight-value">{{ $scholarship->students_per_system_scholarship ?? 20 }}</span><br>
<br>
System Scholarships Earned: {{ $scholarship->total_students ?? 0 }} ÷ {{ $scholarship->students_per_system_scholarship ?? 20 }} = <span class="highlight-value">{{ $scholarship->system_scholarships_earned ?? 0 }}</span><br>
<br>
Current Cycle Progress: {{ $scholarship->current_cycle_progress ?? 0 }} / {{ $scholarship->students_per_system_scholarship ?? 20 }} = <span class="highlight-value">{{ $scholarship->progress_percentage ?? 0 }}%</span><br>
<br>
Students Needed for Next: <span class="highlight-value">{{ $scholarship->students_needed_for_next ?? 0 }}</span> more students
        </div>
        
        <div class="formula-step">
            <div class="formula-number">💡</div>
            <div class="formula-text">
                <strong>Key Insight:</strong> The system benefits from every student approval, even if individual agents 
                don't complete their quotas. This creates consistent revenue regardless of agent performance.
            </div>
        </div>
    </div>
</div>
