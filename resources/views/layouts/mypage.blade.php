<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GunMatchマイページの画面デザイン確認用ページです。">
    <title>@yield('title', 'マイページ｜GunMatch')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/gunmatch.css') }}">
    <script src="{{ asset('assets/js/gunmatch.js') }}" defer></script>
</head>
<body class="brand-site member-site" id="page-top">
    <a class="skip-link" href="#main-content">本文へ移動</a>
    <x-site-header />
    <main id="main-content" class="member-area" tabindex="-1">
        <div class="container">
            <x-page-heading
                :title="$title"
                description="マイページの共通UIを確認するための画面です。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'マイページ', 'route' => 'mypage'], ['label' => $title]]"
            />
            <p class="page-notice" role="note">会員認証・プロフィール保存・設定変更は未実装です。実際の会員情報や通知設定は表示していません。</p>
            <div class="mypage-layout">
                <x-mypage-nav :active="$pageKey" />
                <section class="mypage-content" aria-labelledby="mypage-content-title">
                    <h2 id="mypage-content-title">{{ $title }}</h2>

                    @if ($pageKey === 'home')
                        <div class="empty-state">
                            <h3>マイページ機能は準備中です</h3>
                            <p>会員認証と保存機能はまだありません。ここに個人情報や実在の活動履歴は表示されません。</p>
                        </div>
                    @elseif ($pageKey === 'profile')
                        <p class="member-help">プロフィール項目の画面イメージです。入力・保存はできません。</p>
                        <x-form-field name="member-name" label="お名前" :required="true" />
                        <x-form-field name="member-email" label="メールアドレス" type="email" />
                        <div class="form-error-slot">保存時のエラー表示領域（現在未実装）</div>
                        <button class="btn btn--outline" type="button" disabled>保存機能は未実装です</button>
                    @elseif ($pageKey === 'settings')
                        <p class="member-help">アカウント設定の表示例です。実際の登録情報や変更機能はありません。</p>
                        <div class="setting-row"><div><h3>ログイン情報</h3><p>メールアドレス・パスワード</p></div><span class="status-pill">未設定</span></div>
                        <div class="setting-row"><div><h3>アカウント状態</h3><p>会員認証は未実装です</p></div><span class="status-pill">準備中</span></div>
                    @else
                        <p class="member-help">通知設定の表示例です。通知の配信・変更機能はありません。</p>
                        <div class="setting-row"><div><h3>お知らせ</h3><p>サイトに関するお知らせ</p></div><span class="status-pill">未設定</span></div>
                        <div class="setting-row"><div><h3>メール通知</h3><p>メール送信機能は未実装です</p></div><span class="status-pill">準備中</span></div>
                    @endif
                </section>
            </div>
        </div>
    </main>
    <x-site-footer />
</body>
</html>
