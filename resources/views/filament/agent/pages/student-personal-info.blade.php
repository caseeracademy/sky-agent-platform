@php
    $student = $record;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="fi-info-item">
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
            Full Name
        </dt>
        <dd class="text-base font-semibold text-gray-900 dark:text-white mt-1">{{ $student->name }}</dd>
    </div>

    <div class="fi-info-item">
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
            </svg>
            Email Address
        </dt>
        <dd class="text-base text-gray-900 dark:text-white mt-1">
            <a href="mailto:{{ $student->email }}" class="text-primary-600 hover:text-primary-700">{{ $student->email }}</a>
        </dd>
    </div>

    <div class="fi-info-item">
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
            </svg>
            Phone Number
        </dt>
        <dd class="text-base text-gray-900 dark:text-white mt-1">{{ $student->phone ?: 'Not provided' }}</dd>
    </div>

    <div class="fi-info-item">
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.559-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.559.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd" />
            </svg>
            Nationality
        </dt>
        <dd class="text-base text-gray-900 dark:text-white mt-1">{{ $student->nationality ?: 'Not specified' }}</dd>
    </div>

    <div class="fi-info-item">
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
            Date of Birth
        </dt>
        <dd class="text-base text-gray-900 dark:text-white mt-1">{{ $student->date_of_birth ? $student->date_of_birth->format('F j, Y') : 'Not provided' }}</dd>
    </div>

    <div class="fi-info-item">
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
            Added to System
        </dt>
        <dd class="text-base text-gray-900 dark:text-white mt-1">{{ $student->created_at->format('F j, Y') }}</dd>
    </div>
</div>


