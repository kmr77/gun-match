@props(['active'])

<nav class="mypage-nav" aria-label="マイページメニュー">
    <h2>マイページメニュー</h2>
    <a @class(['is-active' => $active === 'home']) href="{{ route('mypage') }}" @if ($active === 'home') aria-current="page" @endif><span aria-hidden="true">⌂</span> マイページTOP</a>
    <a @class(['is-active' => $active === 'profile']) href="{{ route('mypage.profile') }}" @if ($active === 'profile') aria-current="page" @endif><span aria-hidden="true">◎</span> プロフィール編集</a>
    <a @class(['is-active' => $active === 'profile-public']) href="{{ route('mypage.profile.public') }}" @if ($active === 'profile-public') aria-current="page" @endif><span aria-hidden="true">◉</span> 公開プロフィール</a>
    <a @class(['is-active' => $active === 'profile-preview']) href="{{ route('mypage.profile.preview') }}" @if ($active === 'profile-preview') aria-current="page" @endif><span aria-hidden="true">▣</span> プロフィールプレビュー</a>
    <a @class(['is-active' => $active === 'settings']) href="{{ route('mypage.settings') }}" @if ($active === 'settings') aria-current="page" @endif><span aria-hidden="true">⚙</span> アカウント設定</a>
    <a @class(['is-active' => $active === 'security']) href="{{ route('mypage.security') }}" @if ($active === 'security') aria-current="page" @endif><span aria-hidden="true">◇</span> セキュリティ設定</a>
    <a @class(['is-active' => $active === 'account']) href="{{ route('mypage.account') }}" @if ($active === 'account') aria-current="page" @endif><span aria-hidden="true">▤</span> アカウント管理のご案内</a>
    <a @class(['is-active' => $active === 'notifications']) href="{{ route('mypage.notifications') }}" @if ($active === 'notifications') aria-current="page" @endif><span aria-hidden="true">♧</span> 通知設定</a>
    <a @class(['is-active' => $active === 'announcements']) href="{{ route('mypage.announcements') }}" @if ($active === 'announcements') aria-current="page" @endif><span aria-hidden="true">▤</span> お知らせ一覧</a>
</nav>
