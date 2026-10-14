@props(['name' => 'info'])
<svg {{ $attributes->class(['ui-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('grid') <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/> @break
        @case('users') <circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M21 21v-3a6 6 0 0 0-4-5.66"/> @break
        @case('user') <circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/> @break
        @case('box') <path d="m12 3 9 5v8l-9 5-9-5V8l9-5Zm0 10 9-5M12 13 3 8m9 5v8M7.5 5.5l9 5"/> @break
        @case('receipt') <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3ZM9 7h6M9 11h6M9 15h3"/> @break
        @case('logout') <path d="M9 3H4v18h5m5-5 4-4-4-4m-6 4h13"/> @break
        @case('menu') <path d="M3 6h18M3 12h18M3 18h18"/> @break
        @case('x') <path d="m6 6 12 12M6 18 18 6"/> @break
        @case('search') <circle cx="10.5" cy="10.5" r="7.5"/><path d="m16 16 5 5"/> @break
        @case('check') <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/> @break
        @case('warning') <path d="m12 3 10 18H2L12 3Zm0 6v5m0 3h.01"/> @break
        @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
        @case('money') <rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 12h.01M18 12h.01"/> @break
        @case('arrow-right') <path d="M4 12h16m-6-6 6 6-6 6"/> @break
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @default <circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10h.01"/>
    @endswitch
</svg>
