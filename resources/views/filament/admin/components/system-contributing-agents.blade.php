@php
    $agents = $agents ?? [];
    $scholarship = $scholarship ?? null;
@endphp

<style>
.agents-container {
    display: grid;
    gap: 1rem;
}

.agent-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.agent-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #7c3aed 0%, #5b21b6 100%);
    border-radius: 0 2px 2px 0;
}

.agent-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    border-color: #cbd5e1;
}

.agent-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
}

.agent-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.agent-status-completed {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    color: #166534;
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 1px solid #a7f3d0;
}

.agent-status-progress {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 1px solid #f59e0b;
}

.agent-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.agent-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.agent-detail-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.agent-detail-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: #334155;
}

.agent-progress-section {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 1rem;
}

.agent-progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.agent-progress-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: #0c4a6e;
}

.agent-progress-percentage {
    font-size: 0.75rem;
    font-weight: 700;
    color: #0369a1;
    background: #ffffff;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    border: 1px solid #7dd3fc;
}

.agent-progress-bar-container {
    background: #e0f2fe;
    border-radius: 50px;
    height: 6px;
    overflow: hidden;
    position: relative;
}

.agent-progress-bar {
    background: linear-gradient(90deg, #0ea5e9 0%, #0284c7 100%);
    height: 100%;
    border-radius: 50px;
    transition: width 0.5s ease;
    position: relative;
}

.agent-progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.4) 50%, transparent 100%);
    animation: agent-shimmer 2s infinite;
}

@keyframes agent-shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.empty-agents-state {
    text-align: center;
    padding: 3rem 1rem;
    background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 100%);
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
}

.empty-agents-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.empty-agents-description {
    font-size: 0.875rem;
    color: #94a3b8;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .agent-details {
        grid-template-columns: 1fr;
    }
    
    .agent-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
}

.dark .agent-card {
    background: linear-gradient(145deg, #1e293b 0%, #334155 100%);
    border-color: #475569;
}

.dark .agent-name {
    color: #f1f5f9;
}

.dark .agent-detail-value {
    color: #e2e8f0;
}

.dark .agent-progress-section {
    background: linear-gradient(145deg, #1e293b 0%, #334155 100%);
    border-color: #475569;
}

.dark .agent-progress-text {
    color: #7dd3fc;
}

.dark .agent-progress-percentage {
    background: #334155;
    color: #7dd3fc;
    border-color: #475569;
}

.dark .empty-agents-state {
    background: linear-gradient(145deg, #1e293b 0%, #334155 100%);
    border-color: #475569;
}

.dark .empty-agents-title {
    color: #94a3b8;
}

.dark .empty-agents-description {
    color: #64748b;
}
</style>

@if(count($agents) > 0)
    <div class="agents-container">
        @foreach($agents as $agentData)
            <div class="agent-card">
                <div class="agent-header">
                    <h4 class="agent-name">
                        {{ $agentData['agent']['name'] ?? 'Unknown Agent' }}
                        @if(isset($agentData['cycle_number']))
                            <span style="font-size: 0.875rem; font-weight: 500; color: #64748b;">
                                - Scholarship #{{ $agentData['cycle_number'] }}
                            </span>
                        @endif
                    </h4>
                    <span class="agent-status-{{ $agentData['has_completed'] ? 'completed' : 'progress' }}">
                        {{ $agentData['has_completed'] ? 'Completed' : 'In Progress' }}
                    </span>
                </div>
                
                <div class="agent-details">
                    <div class="agent-detail-item">
                        <span class="agent-detail-label">Email</span>
                        <span class="agent-detail-value">{{ $agentData['agent']['email'] ?? 'N/A' }}</span>
                    </div>
                    <div class="agent-detail-item">
                        <span class="agent-detail-label">Cycle Progress</span>
                        <span class="agent-detail-value">{{ $agentData['progress_text'] ?? 'N/A' }}</span>
                    </div>
                    <div class="agent-detail-item">
                        <span class="agent-detail-label">Cycle Status</span>
                        <span class="agent-detail-value">{{ $agentData['has_completed'] ? 'Completed ✅' : 'In Progress' }}</span>
                    </div>
                    @if(isset($agentData['total_points']))
                        <div class="agent-detail-item">
                            <span class="agent-detail-label">Agent Total</span>
                            <span class="agent-detail-value">{{ $agentData['total_points'] }} students ({{ $agentData['scholarships_earned'] ?? 0 }} earned)</span>
                        </div>
                    @endif
                </div>
                
                @if(($agentData['progress_percentage'] ?? 0) > 0 || $agentData['has_completed'])
                    <div class="agent-progress-section">
                        <div class="agent-progress-header">
                            <span class="agent-progress-text">{{ $agentData['progress_text'] ?? 'N/A' }}</span>
                            <span class="agent-progress-percentage">{{ $agentData['progress_percentage'] ?? 0 }}%</span>
                        </div>
                        <div class="agent-progress-bar-container">
                            <div class="agent-progress-bar" style="width: {{ min(100, $agentData['progress_percentage'] ?? 0) }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="empty-agents-state">
        <div class="empty-agents-title">No Contributing Agents</div>
        <div class="empty-agents-description">
            No agents have submitted scholarship applications for this university and degree combination yet.
            <br>Agents will appear here once they start accumulating scholarship points.
        </div>
    </div>
@endif
