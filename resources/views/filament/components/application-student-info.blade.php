@props(['student', 'studentId'])

<style>
    .student-info-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        transition: box-shadow 0.2s ease;
    }
    
    .student-info-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .student-icon-box {
        width: 4rem;
        height: 4rem;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .student-content {
        flex: 1;
        min-width: 0;
    }
    
    .student-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
        line-height: 1.5;
    }
    
    .student-email {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.75rem;
        word-break: break-all;
    }
    
    .student-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }
    
    .student-meta-item {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.8125rem;
        color: #6b7280;
    }
    
    .student-meta-icon {
        width: 1rem;
        height: 1rem;
        color: #9ca3af;
    }
    
    .student-badges {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .student-badge {
        padding: 0.25rem 0.625rem;
        border-radius: 0.375rem;
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    .badge-nationality {
        background: #e0f2fe;
        color: #0c4a6e;
    }
    
    .badge-mothers-name {
        background: #fce7f3;
        color: #be185d;
    }
    
    .badge-dob {
        background: #fef3c7;
        color: #92400e;
    }
    
    .student-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex-shrink: 0;
    }
    
    .student-btn {
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.8125rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        transition: all 0.2s ease;
        text-decoration: none;
        white-space: nowrap;
        border: none;
        cursor: pointer;
    }
    
    .student-details-btn {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(107, 114, 128, 0.2);
    }
    
    .student-details-btn:hover {
        background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
        box-shadow: 0 4px 8px rgba(107, 114, 128, 0.3);
        transform: translateY(-1px);
    }
    
    .btn-icon {
        width: 1rem;
        height: 1rem;
    }
</style>

<div class="student-info-card">
    <div class="student-icon-box">
        👤
    </div>
    
    <div class="student-content">
        <h3 class="student-title">{{ $student->name }}</h3>
        <p class="student-email">{{ $student->email }}</p>
        
        <div class="student-meta">
            @if($student->phone)
                <div class="student-meta-item">
                    <svg class="student-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <span>{{ $student->phone }}</span>
                </div>
            @endif
            
            @if($student->phone && $student->mothers_name)
                <span style="color: #d1d5db;">•</span>
            @endif
            
            @if($student->mothers_name)
                <div class="student-meta-item">
                    <svg class="student-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>{{ $student->mothers_name }}</span>
                </div>
            @endif
            
            @if(($student->phone || $student->mothers_name) && $student->date_of_birth)
                <span style="color: #d1d5db;">•</span>
            @endif
            
            @if($student->date_of_birth)
                <div class="student-meta-item">
                    <svg class="student-meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>{{ $student->date_of_birth->format('M j, Y') }}</span>
                </div>
            @endif
        </div>
        
        <div class="student-badges">
            @if($student->nationality)
                <span class="student-badge badge-nationality">{{ $student->nationality }}</span>
            @endif
            @if($student->mothers_name)
                <span class="student-badge badge-mothers-name">{{ $student->mothers_name }}</span>
            @endif
            @if($student->date_of_birth)
                <span class="student-badge badge-dob">{{ $student->date_of_birth->format('M j, Y') }}</span>
            @endif
        </div>
    </div>
    
    <div class="student-actions">
        <a href="{{ route('filament.agent.resources.students.view', $studentId) }}" 
           class="student-btn student-details-btn">
            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Student Details
        </a>
    </div>
</div>
