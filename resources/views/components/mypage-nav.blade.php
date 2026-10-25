@props(['active'])

<nav class="mypage-nav" aria-label="マイページメニュー">
    <h2>マイページメニュー</h2>
    <a @class(['is-active' => $active === 'home']) href="{{ route('mypage') }}" @if ($active === 'home') aria-current="page" @endif>マイページTOP</a>
    <a @class(['is-active' => $active === 'profile']) href="{{ route('mypage.profile') }}" @if ($active === 'profile') aria-current="page" @endif>プロフィール編集</a>
    <a @class(['is-active' => $active === 'settings']) href="{{ route('mypage.settings') }}" @if ($active === 'settings') aria-current="page" @endif>アカウント設定</a>
    <a @class(['is-active' => $active === 'notifications']) href="{{ route('mypage.notifications') }}" @if ($active === 'notifications') aria-current="page" @endif>通知設定</a>
</nav>
