<div class="flex min-h-0 flex-1 flex-col">
    <div class="px-5 py-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="brand-mark h-11 w-11">IR</span>
            <span>
                <span class="block text-sm font-semibold text-slate-950">IRDCRP MIS</span>
                <span class="block text-xs text-slate-500">Management workspace</span>
            </span>
        </a>
        <div class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-800">Live workspace</p>
            <p class="mt-1 text-xs text-emerald-700">Super admin ready</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 pb-4">
        @foreach (collect($navItems)->groupBy('section') as $section => $items)
            <div class="mt-4 first:mt-1">
                <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ $section }}</p>
                <div class="space-y-1">
                    @foreach ($items as $item)
                        @php
                            $activePatterns = (array) $item['active'];
                            $activeParams = $item['active_params'] ?? [];
                            $isActive = request()->routeIs(...$activePatterns)
                                && collect($activeParams)->every(fn ($value, $key) => request($key) === $value);
                        @endphp

                        <div>
                            <a
                                href="{{ route($item['route'], $item['params'] ?? []) }}"
                                @class([
                                    'nav-link',
                                    'nav-link-active' => $isActive,
                                ])
                            >
                                <span @class(['nav-dot', $item['accent']])></span>
                                <span class="min-w-0">
                                    <span class="block truncate">{{ $item['label'] }}</span>
                                    @isset($item['caption'])
                                        <span @class([
                                            'block truncate text-xs font-medium',
                                            'text-white/70' => $isActive,
                                            'text-slate-400' => ! $isActive,
                                        ])>{{ $item['caption'] }}</span>
                                    @endisset
                                </span>
                            </a>

                            @if (! empty($item['children']))
                                <div class="mt-1 space-y-1 border-l border-slate-200 pl-4 ml-4">
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
                                            {{ $child['label'] }}
                                        </a>

                                        @if (! empty($child['children']))
                                            <div class="ml-3 space-y-1 border-l border-slate-200 pl-3">
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
        <div class="profile-panel p-3">
            <p class="truncate text-sm font-semibold text-slate-950">{{ Auth::user()->name }}</p>
            <p class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
            <div class="mt-3 flex gap-2">
                <a href="{{ route('profile.edit') }}" class="flex-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-center text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full rounded-md bg-slate-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-800">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</div>
