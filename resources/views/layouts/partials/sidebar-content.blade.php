<div class="flex min-h-0 flex-1 flex-col">
    <div class="sidebar-brand-area">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <span class="brand-mark h-12 w-12">
                <img src="{{ asset('images/logos/irdcrp-logo.svg') }}" alt="" class="h-10 w-10 object-contain">
            </span>
            <span>
                <span class="sidebar-brand-title">IRDCRP MIS</span>
                <span class="sidebar-brand-subtitle">Management Information System</span>
            </span>
        </a>
        <div class="sidebar-status">
            <span class="sidebar-status-pulse"></span>
            <div class="min-w-0">
                <p>Live workspace</p>
                <span>Workspace ready</span>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        @foreach (collect($navItems)->groupBy('section') as $section => $items)
            <div class="sidebar-section">
                <p class="sidebar-section-label">{{ $section }}</p>
                <div class="space-y-1">
                    @foreach ($items as $item)
                        @php
                            $activePatterns = (array) $item['active'];
                            $activeParams = $item['active_params'] ?? [];
                            $isActive = request()->routeIs(...$activePatterns)
                                && collect($activeParams)->every(fn ($value, $key) => request($key) === $value);
                            $itemIcon = $item['icon'] ?? 'circle';
                        @endphp

                        <div>
                            <a
                                href="{{ route($item['route'], $item['params'] ?? []) }}"
                                @class([
                                    'nav-link',
                                    'nav-link-active' => $isActive,
                                ])
                                style="--nav-accent: {{ $item['accent'] }}"
                            >
                                <span class="nav-icon" aria-hidden="true">
                                    @switch($itemIcon)
                                        @case('dashboard')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 13h6V4H4v9Zm10 7h6V4h-6v16ZM4 20h6v-3H4v3Z" />
                                            </svg>
                                            @break

                                        @case('staff')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11a4 4 0 1 0-8 0m8 0a4 4 0 1 1-8 0m8 0h1.5a3.5 3.5 0 1 0 0-7M8 11H6.5a3.5 3.5 0 1 1 0-7M5 20a7 7 0 0 1 14 0" />
                                            </svg>
                                            @break

                                        @case('partnership')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8m-9 4 2 2a3 3 0 0 0 4.24 0l.76-.76m-4-10.48.76-.76A3 3 0 0 1 15 6l2 2m-9 4-3-3a3 3 0 0 1 0-4.24l.76-.76a3 3 0 0 1 4.24 0l1 1m5 7 3 3a3 3 0 0 1 0 4.24l-.76.76a3 3 0 0 1-4.24 0l-1-1" />
                                            </svg>
                                            @break

                                        @case('entrepreneurs')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16M7 9h7.5a3.5 3.5 0 0 1 0 7H7m8-12 3 3m0-3-3 3" />
                                            </svg>
                                            @break

                                        @case('records')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5 12 3l8 3.5-8 3.5-8-3.5Zm0 5L12 15l8-3.5M4 16.5 12 20l8-3.5" />
                                            </svg>
                                            @break

                                        @case('reports')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 20V4h14v16H5Zm4-4v-5m3 5V8m3 8v-3" />
                                            </svg>
                                            @break

                                        @case('settings')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm8 4h-2m-12 0H4m13.66-5.66-1.42 1.42M7.76 16.24l-1.42 1.42m11.32 0-1.42-1.42M7.76 7.76 6.34 6.34" />
                                            </svg>
                                            @break

                                        @default
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="4" />
                                            </svg>
                                    @endswitch
                                </span>
                                <span class="min-w-0">
                                    <span class="nav-link-label">{{ $item['label'] }}</span>
                                    @isset($item['caption'])
                                        <span class="nav-link-caption">{{ $item['caption'] }}</span>
                                    @endisset
                                </span>
                                @if (! empty($item['children']))
                                    <span class="nav-chevron" aria-hidden="true">
                                        <svg viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.22 4.72a.75.75 0 0 1 1.06 0l4.75 4.75a.75.75 0 0 1 0 1.06l-4.75 4.75a.75.75 0 0 1-1.06-1.06L11.44 10 7.22 5.78a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @endif
                            </a>

                            @if (! empty($item['children']))
                                <div class="sub-nav-group">
                                    @foreach ($item['children'] as $child)
                                        @php
                                            $childActivePatterns = (array) $child['active'];
                                            $childActiveParams = $child['active_params'] ?? [];
                                            $isChildActive = request()->routeIs(...$childActivePatterns)
                                                && collect($childActiveParams)->every(fn ($value, $key) => request($key) === $value);
                                        @endphp

                                        <a
                                            href="{{ route($child['route'], $child['params'] ?? []) }}"
                                            @class([
                                                'sub-nav-link',
                                                'sub-nav-link-active' => $isChildActive,
                                            ])
                                        >
                                            <span class="sub-nav-marker" aria-hidden="true"></span>
                                            {{ $child['label'] }}
                                        </a>

                                        @if (! empty($child['children']))
                                            <div class="sub-nav-group sub-nav-group-nested">
                                                @foreach ($child['children'] as $grandChild)
                                                    @php
                                                        $grandChildActiveParams = $grandChild['active_params'] ?? [];
                                                        $isGrandChildActive = request()->routeIs($grandChild['active'])
                                                            && collect($grandChildActiveParams)->every(fn ($value, $key) => request($key) === $value);
                                                    @endphp

                                                    <a
                                                        href="{{ route($grandChild['route'], $grandChild['params'] ?? []) }}"
                                                        @class([
                                                            'sub-nav-link sub-nav-link-nested',
                                                            'sub-nav-link-active' => $isGrandChildActive,
                                                        ])
                                                    >
                                                        <span class="sub-nav-marker" aria-hidden="true"></span>
                                                        {{ $grandChild['label'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="p-4">
        @php
            $userName = Auth::user()->name;
            $userInitials = collect(explode(' ', trim($userName)))
                ->filter()
                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                ->take(2)
                ->implode('');
        @endphp

        <div class="profile-panel">
            <div class="profile-user">
                <span class="profile-avatar">{{ $userInitials ?: 'U' }}</span>
                <span class="min-w-0">
                    <span class="profile-name">{{ $userName }}</span>
                    <span class="profile-email">{{ Auth::user()->email }}</span>
                </span>
            </div>
            <div class="profile-actions">
                <a href="{{ route('profile.edit') }}" class="profile-action profile-action-secondary">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="profile-action profile-action-primary">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</div>
