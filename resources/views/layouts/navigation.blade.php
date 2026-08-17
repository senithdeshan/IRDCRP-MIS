@php
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'accent' => 'bg-emerald-500', 'section' => 'Workspace'],
        ['label' => 'Staff', 'route' => 'staff.index', 'active' => 'staff.*', 'accent' => 'bg-sky-500', 'section' => 'Workspace'],
        [
            'label' => 'Component 1.2',
            'caption' => 'Productive Partnership',
            'route' => 'productive-partnership.index',
            'active' => ['productive-partnership.*', 'farmer-organizations.*', 'selected-eois.*', 'reviewed-interviews.*', 'field-visits.*', 'approved-farmer-producer-groups.*', 'full-proposals.*', 'agreement-sign-fop.*'],
            'accent' => 'bg-amber-500',
            'section' => 'IRDCRP modules',
            'children' => [
                [
                    'label' => 'Farmer Organization Information',
                    'route' => 'farmer-organizations.index',
                    'active' => ['farmer-organizations.*', 'selected-eois.*', 'reviewed-interviews.*', 'field-visits.*', 'approved-farmer-producer-groups.*', 'full-proposals.*', 'agreement-sign-fop.*'],
                    'children' => [
                        ['label' => 'Selected All EOI', 'route' => 'selected-eois.index', 'active' => 'selected-eois.*'],
                        ['label' => 'Selected for Reviewed Interview', 'route' => 'reviewed-interviews.index', 'active' => 'reviewed-interviews.*'],
                        ['label' => 'Selected For Verification Field Visit Pass', 'route' => 'field-visits.index', 'active' => 'field-visits.*'],
                        ['label' => 'Approved Farmer Producer Groups', 'route' => 'approved-farmer-producer-groups.index', 'active' => 'approved-farmer-producer-groups.*'],
                        ['label' => 'Selected for Full Proposal Preparation', 'route' => 'full-proposals.index', 'active' => 'full-proposals.*'],
                        ['label' => 'Agreement Sign FOP', 'route' => 'agreement-sign-fop.index', 'active' => 'agreement-sign-fop.*'],
                    ],
                ],
            ],
        ],
        [
            'label' => 'Component 1.3',
            'caption' => 'Youth and Women Entrepreneurs',
            'route' => 'youth-women.index',
            'active' => ['youth-women.*', 'business-information.*'],
            'accent' => 'bg-violet-500',
            'section' => 'IRDCRP modules',
            'children' => [
                [
                    'label' => 'Individual Entrepreneur Information',
                    'route' => 'youth-women.index',
                    'active' => ['youth-women.*', 'business-information.*'],
                    'children' => [
                        ['label' => 'Business Information', 'route' => 'business-information.index', 'active' => 'business-information.*', 'active_params' => ['initial_screening_result' => null]],
                        ['label' => 'Add Applicant', 'route' => 'business-information.create', 'active' => 'business-information.create'],
                        [
                            'label' => 'Selected Individuals',
                            'route' => 'business-information.index',
                            'params' => ['initial_screening_result' => 'Selected'],
                            'active' => 'business-information.index',
                            'active_params' => ['initial_screening_result' => 'Selected'],
                        ],
                        [
                            'label' => 'Rejected Individuals',
                            'route' => 'business-information.index',
                            'params' => ['initial_screening_result' => 'Reject'],
                            'active' => 'business-information.index',
                            'active_params' => ['initial_screening_result' => 'Reject'],
                        ],
                    ],
                ],
            ],
        ],
        [
            'label' => 'Component 2',
            'caption' => 'Cascade and Tank Records',
            'route' => 'component-two.index',
            'active' => ['component-two.*', 'cascade-registration.*', 'tank-registration.*'],
            'accent' => 'bg-cyan-500',
            'section' => 'IRDCRP modules',
            'children' => [
                ['label' => 'Cascade Registration', 'route' => 'cascade-registration.index', 'active' => 'cascade-registration.*'],
                ['label' => 'Tank Registration', 'route' => 'tank-registration.index', 'active' => 'tank-registration.*'],
            ],
        ],
        ['label' => 'Reports', 'route' => 'reports.index', 'active' => 'reports.*', 'accent' => 'bg-teal-500', 'section' => 'Management'],
        ['label' => 'Settings', 'route' => 'settings.index', 'active' => 'settings.*', 'accent' => 'bg-slate-500', 'section' => 'Development'],
    ];
@endphp

<div x-data="{ sidebarOpen: false }">
    <div class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur lg:hidden">
        <div class="flex h-16 items-center justify-between px-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="brand-mark h-10 w-10">IR</span>
                <span>
                    <span class="block text-sm font-semibold text-slate-950">IRDCRP MIS</span>
                    <span class="block text-xs text-slate-500">Operations</span>
                </span>
            </a>
            <button type="button" @click="sidebarOpen = true" class="icon-button">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>

    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
        <div class="absolute inset-0 bg-slate-950/40" @click="sidebarOpen = false"></div>
        <aside class="sidebar-shell relative flex h-full w-72 flex-col shadow-2xl">
            <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4">
                <span class="text-sm font-semibold text-slate-950">IRDCRP MIS</span>
                <button type="button" @click="sidebarOpen = false" class="icon-button h-9 w-9">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>
            @include('layouts.partials.sidebar-content', ['navItems' => $navItems])
        </aside>
    </div>

    <aside class="sidebar-shell fixed inset-y-0 left-0 z-30 hidden w-72 flex-col lg:flex">
        @include('layouts.partials.sidebar-content', ['navItems' => $navItems])
    </aside>
</div>
