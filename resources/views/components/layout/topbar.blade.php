@props(['user'])
<header class="app-topbar">
    <button type="button" class="icon-button navigation-toggle" data-navigation-open aria-controls="navigation-dialog" aria-expanded="false" aria-haspopup="dialog" aria-label="Buka menu navigasi"><x-ui.icon name="menu" /></button>
    <div class="topbar-leading">{{ $slot }}</div>
    <div class="topbar-profile">
        <span class="profile-avatar"><x-ui.icon name="user" /></span>
        <div class="profile-identity">
            <p class="profile-name">{{ $user->nama_user }}</p>
            <p class="profile-role">{{ ['admin' => 'Admin', 'sales' => 'Sales', 'direktur' => 'Direktur'][$user->role] ?? $user->role }}</p>
        </div>
    </div>
</header>
