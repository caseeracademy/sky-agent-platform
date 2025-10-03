@props(['student'])

<style>
    .profile-header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 1rem;
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 2rem;
        color: white;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
    }
    
    .profile-avatar-container {
        position: relative;
        flex-shrink: 0;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, 0.3);
        object-fit: cover;
        background: white;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    
    .profile-status-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 24px;
        height: 24px;
        background: #10b981;
        border: 3px solid white;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .profile-content {
        flex: 1;
        min-width: 0;
    }
    
    .profile-name {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        line-height: 1.2;
    }
    
    .profile-email {
        font-size: 1.125rem;
        opacity: 0.95;
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .profile-meta {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
    }
    
    .profile-meta-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .profile-meta-label {
        font-size: 0.75rem;
        opacity: 0.8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }
    
    .profile-meta-value {
        font-size: 1rem;
        font-weight: 600;
    }
    
    .profile-stats {
        display: flex;
        gap: 1.5rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .profile-stat {
        text-align: center;
    }
    
    .profile-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        display: block;
        margin-bottom: 0.25rem;
    }
    
    .profile-stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    
    @media (max-width: 768px) {
        .profile-header-card {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-meta {
            justify-content: center;
        }
        
        .profile-stats {
            justify-content: center;
        }
    }
</style>

<div class="profile-header-card">
    <div class="profile-avatar-container">
        <img src="{{ $student->avatar_url }}" alt="{{ $student->name }}" class="profile-avatar">
        <div class="profile-status-badge" title="Active Student"></div>
    </div>
    
    <div class="profile-content">
        <h1 class="profile-name">{{ $student->name }}</h1>
        
        <p class="profile-email">
            <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            {{ $student->email }}
        </p>
        
        <div class="profile-meta">
            @if($student->nationality)
                <div class="profile-meta-item">
                    <span class="profile-meta-label">Nationality</span>
                    <span class="profile-meta-value">{{ $student->nationality }}</span>
                </div>
            @endif
            
            @if($student->country_of_residence)
                <div class="profile-meta-item">
                    <span class="profile-meta-label">Country of Residence</span>
                    <span class="profile-meta-value">{{ $student->country_of_residence }}</span>
                </div>
            @endif
            
            @if($student->gender)
                <div class="profile-meta-item">
                    <span class="profile-meta-label">Gender</span>
                    <span class="profile-meta-value">{{ ucfirst(str_replace('_', ' ', $student->gender)) }}</span>
                </div>
            @endif
            
            @if($student->date_of_birth)
                <div class="profile-meta-item">
                    <span class="profile-meta-label">Age</span>
                    <span class="profile-meta-value">{{ $student->age }} years</span>
                </div>
            @endif
        </div>
        
        <div class="profile-stats">
            <div class="profile-stat">
                <span class="profile-stat-value">{{ $student->applications()->count() }}</span>
                <span class="profile-stat-label">{{ $student->applications()->count() === 1 ? 'Application' : 'Applications' }}</span>
            </div>
            <div class="profile-stat">
                <span class="profile-stat-value">{{ $student->applications()->where('status', 'approved')->count() }}</span>
                <span class="profile-stat-label">Approved</span>
            </div>
            <div class="profile-stat">
                <span class="profile-stat-value">{{ $student->documents()->count() }}</span>
                <span class="profile-stat-label">{{ $student->documents()->count() === 1 ? 'Document' : 'Documents' }}</span>
            </div>
        </div>
    </div>
</div>




