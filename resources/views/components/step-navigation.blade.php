@props([
    'currentStep' => 1,
    'application' => null,
])

@php
    $steps = [
        [
            'number' => 1,
            'label' => 'Stilling',
            'route' => 'enter-employment-information',
        ],
        [
            'number' => 2,
            'label' => 'Utdanning',
            'route' => 'enter-education-information',
        ],
        [
            'number' => 3,
            'label' => 'Arbeidserfaring',
            'route' => 'enter-experience-information',
        ],
        [
            'number' => 4,
            'label' => 'Kurs og verv',
            'route' => 'enter-courses-and-activity-information',
        ],
        [
            'number' => 5,
            'label' => 'Beregning',
            'route' => 'preview-and-estimated-salary',
        ],
    ];

    $appParam = ($application && $application->id) ? $application : (session('applicationId') ? session('applicationId') : null);
    $canAccessStep5 = $application && method_exists($application, 'areSteps1To4Completed') && $application->areSteps1To4Completed();
@endphp

<nav class="step-navigation-wrapper my-4" aria-label="Steg i lønnsberegningen">
    <ol class="step-navigation-list">
        @foreach ($steps as $step)
            @php
                $stepNumber = $step['number'];
                $isCompleted = $stepNumber < $currentStep;
                $isActive = $stepNumber === (int)$currentStep;
                $isFuture = $stepNumber > $currentStep;
                $isStepDisabled = ($stepNumber === 5 && !$canAccessStep5 && !$isActive);
                $stepUrl = $appParam ? route($step['route'], $appParam) : route($step['route']);
            @endphp
            <li class="step-navigation-item {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }} {{ $isFuture ? 'future' : '' }} {{ $isStepDisabled ? 'disabled' : '' }}">
                @if ($isStepDisabled)
                    <span class="step-navigation-link disabled"
                          aria-disabled="true"
                          title="Fullfør steg 1–4 før du kan se beregningen">
                        <span class="step-navigation-circle">
                            <span class="step-number">{{ $stepNumber }}</span>
                        </span>
                        <span class="step-navigation-label">{{ $step['label'] }}</span>
                    </span>
                @else
                    <a href="{{ $stepUrl }}"
                       class="step-navigation-link"
                       @if($isActive) aria-current="step" @endif
                       title="Gå til steg {{ $stepNumber }}: {{ $step['label'] }}">
                        <span class="step-navigation-circle">
                            @if($isCompleted)
                                <svg class="step-check-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            @else
                                <span class="step-number">{{ $stepNumber }}</span>
                            @endif
                        </span>
                        <span class="step-navigation-label">{{ $step['label'] }}</span>
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
