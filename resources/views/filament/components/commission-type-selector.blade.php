@php
    $application = $application ?? null;
    
    if (!$application) {
        return;
    }
    
    $commissionAmount = $application->program->agent_commission ?? 0;
@endphp

<style>
.commission-selector-container {
    background: linear-gradient(145deg, #fef3c7 0%, #fde68a 100%);
    border: 3px solid #f59e0b;
    border-radius: 20px;
    padding: 2.5rem;
    margin: 1.5rem 0;
    box-shadow: 0 10px 30px -3px rgba(245, 158, 11, 0.4);
}

.commission-warning-header {
    text-align: center;
    margin-bottom: 2rem;
}

.commission-warning-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.commission-warning-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #92400e;
    margin-bottom: 0.5rem;
}

.commission-warning-subtitle {
    font-size: 1rem;
    color: #a16207;
    font-weight: 500;
}

.commission-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-top: 2rem;
}

.commission-card {
    background: white;
    border: 3px solid transparent;
    border-radius: 16px;
    padding: 2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.commission-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, transparent 0%, rgba(255, 255, 255, 0.5) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.commission-card:hover::before {
    opacity: 1;
}

.commission-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.2);
}

.commission-card-money {
    border-color: #10b981;
    background: linear-gradient(145deg, #ffffff 0%, #ecfdf5 100%);
}

.commission-card-money:hover {
    border-color: #059669;
    background: linear-gradient(145deg, #ecfdf5 0%, #d1fae5 100%);
}

.commission-card-scholarship {
    border-color: #3b82f6;
    background: linear-gradient(145deg, #ffffff 0%, #eff6ff 100%);
}

.commission-card-scholarship:hover {
    border-color: #2563eb;
    background: linear-gradient(145deg, #eff6ff 0%, #dbeafe 100%);
}

.commission-card-icon {
    font-size: 3rem;
    text-align: center;
    margin-bottom: 1rem;
}

.commission-card-title {
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 0.75rem;
}

.commission-card-money .commission-card-title {
    color: #059669;
}

.commission-card-scholarship .commission-card-title {
    color: #2563eb;
}

.commission-card-amount {
    font-size: 2rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 0.75rem;
}

.commission-card-money .commission-card-amount {
    color: #10b981;
}

.commission-card-scholarship .commission-card-amount {
    color: #3b82f6;
}

.commission-card-description {
    text-align: center;
    font-size: 0.875rem;
    color: #64748b;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

.commission-card-button {
    width: 100%;
    padding: 1rem;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.commission-card-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.5s ease;
}

.commission-card-button:hover::before {
    left: 100%;
}

.commission-button-money {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.commission-button-money:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.commission-button-scholarship {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.commission-button-scholarship:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

@media (max-width: 768px) {
    .commission-cards-grid {
        grid-template-columns: 1fr;
    }
    
    .commission-selector-container {
        padding: 1.5rem;
    }
}

.dark .commission-selector-container {
    background: linear-gradient(145deg, #78350f 0%, #92400e 100%);
    border-color: #d97706;
}

.dark .commission-card {
    background: linear-gradient(145deg, #1e293b 0%, #334155 100%);
}

.dark .commission-card-description {
    color: #94a3b8;
}
</style>

<div class="commission-selector-container">
    <div class="commission-warning-header">
        <div class="commission-warning-icon">⚠️</div>
        <h2 class="commission-warning-title">Commission Type Not Set</h2>
        <p class="commission-warning-subtitle">
            Choose how this application will be handled before proceeding
        </p>
    </div>
    
    <div class="commission-cards-grid">
        <!-- Money Commission Card -->
        <div class="commission-card commission-card-money"
             wire:click="selectCommissionType('money')"
             role="button"
             tabindex="0">
            <div class="commission-card-icon">💰</div>
            <h3 class="commission-card-title">Money Commission</h3>
            <div class="commission-card-amount">${{ number_format($commissionAmount, 2) }}</div>
            <p class="commission-card-description">
                Agent will earn a monetary commission when this application is approved.
                Standard commission process applies.
            </p>
            <button class="commission-card-button commission-button-money" type="button">
                Select Money Commission
            </button>
        </div>
        
        <!-- Scholarship Commission Card -->
        <div class="commission-card commission-card-scholarship"
             wire:click="selectCommissionType('scholarship')"
             role="button"
             tabindex="0">
            <div class="commission-card-icon">🎓</div>
            <h3 class="commission-card-title">Scholarship Point</h3>
            <div class="commission-card-amount">FREE</div>
            <p class="commission-card-description">
                Agent will earn a scholarship point when approved.
                No monetary commission - contributes to earning free applications.
            </p>
            <button class="commission-card-button commission-button-scholarship" type="button">
                Select Scholarship
            </button>
        </div>
    </div>
</div>

