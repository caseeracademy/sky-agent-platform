@php
    $student = $record;
    $profileImageUrl = $student->profile_image_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($student->name) . '&color=7c3aed&background=e0e7ff&bold=true&size=200';
@endphp

<div class="fi-section-content p-6 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl text-white">
    <div class="flex items-center gap-6">
        {{-- Profile Image --}}
        <div class="flex-shrink-0">
            <img src="{{ $profileImageUrl }}" alt="{{ $student->name }}" 
                 class="w-24 h-24 rounded-full border-4 border-white/20 object-cover">
        </div>
        
        {{-- Student Info --}}
        <div class="flex-1">
            <h1 class="text-3xl font-bold mb-2">{{ $student->name }}</h1>
            <p class="text-lg opacity-90 mb-4">{{ $student->email }}</p>
            
            {{-- Stats --}}
            <div class="flex gap-4">
                <div class="bg-white/20 rounded-lg px-4 py-2 text-center">
                    <div class="text-2xl font-bold">{{ $student->applications()->count() }}</div>
                    <div class="text-sm opacity-80">Applications</div>
                </div>
                <div class="bg-white/20 rounded-lg px-4 py-2 text-center">
                    <div class="text-2xl font-bold">{{ $student->documents()->count() }}</div>
                    <div class="text-sm opacity-80">Documents</div>
                </div>
                <div class="bg-white/20 rounded-lg px-4 py-2 text-center">
                    <div class="text-2xl font-bold">{{ $student->age ?: '—' }}</div>
                    <div class="text-sm opacity-80">Age</div>
                </div>
            </div>
        </div>
        
        {{-- Status Badge --}}
        <div class="flex-shrink-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-500 text-white text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                Active Student
            </span>
        </div>
    </div>
</div>











