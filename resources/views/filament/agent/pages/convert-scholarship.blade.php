<x-filament-panels::page>
    <style>
    .scholarship-info-banner {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px -3px rgba(16, 185, 129, 0.3);
    }

    .scholarship-info-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .scholarship-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .scholarship-info-item {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 1rem;
    }

    .scholarship-info-label {
        font-size: 0.75rem;
        opacity: 0.8;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .scholarship-info-value {
        font-size: 1.125rem;
        font-weight: 600;
    }
    </style>

    @if($scholarship)
        <div class="scholarship-info-banner">
            <div class="scholarship-info-title">
                🎉 You're Using a FREE Scholarship!
            </div>
            <div class="scholarship-info-grid">
                <div class="scholarship-info-item">
                    <div class="scholarship-info-label">Scholarship Number</div>
                    <div class="scholarship-info-value">{{ $scholarship->commission_number }}</div>
                </div>
                <div class="scholarship-info-item">
                    <div class="scholarship-info-label">University (Locked)</div>
                    <div class="scholarship-info-value">{{ $scholarship->university->name }}</div>
                </div>
                <div class="scholarship-info-item">
                    <div class="scholarship-info-label">Degree Level (Locked)</div>
                    <div class="scholarship-info-value">{{ $scholarship->degree->name }}</div>
                </div>
                <div class="scholarship-info-item">
                    <div class="scholarship-info-label">Application Cost</div>
                    <div class="scholarship-info-value">$0.00 (FREE!)</div>
                </div>
            </div>
        </div>

        <form wire:submit="submit">
            {{ $this->form }}
            
            <div class="mt-6 flex justify-end gap-x-3">
                <x-filament::button
                    type="button"
                    color="gray"
                    tag="a"
                    href="{{ \App\Filament\Agent\Resources\Scholarships\ScholarshipResource::getUrl('index') }}"
                >
                    Cancel
                </x-filament::button>
                
                <x-filament::button
                    type="submit"
                    color="success"
                >
                    Submit FREE Application
                </x-filament::button>
            </div>
        </form>
    @endif
</x-filament-panels::page>
