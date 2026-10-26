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
                description="マイページの共通UIを確認するための表示サンプルです。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'マイページ', 'route' => 'mypage'], ['label' => $title]]"
            />
            <x-feedback-message
                type="info"
                title="画面表示の確認用です"
                message="会員認証、プロフィール保存、設定変更は未実装です。実際の会員情報や通知設定は表示していません。"
            />
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
                        <x-feedback-message
                            type="error"
                            title="入力エラー表示のサンプル"
                            message="入力検証は未実装です。この表示はUI確認用で、フォームの入力値は検証・保存されません。"
                        />
                        <button class="btn btn--outline" type="button" disabled>保存機能は未実装です</button>
                        <p class="member-help"><a href="{{ route('mypage.profile.public') }}">公開プロフィール表示</a> ・ <a href="{{ route('mypage.profile.preview') }}">プロフィールプレビュー</a></p>
                    @elseif ($pageKey === 'profile-public' || $pageKey === 'profile-preview')
                        <p class="member-help">個人情報を含まないプロフィール画面の表示確認用サンプルです。公開・保存されているプロフィールではありません。</p>
                        @if ($pageKey === 'profile-preview')
                            <x-feedback-message
                                type="success"
                                title="表示状態のサンプル"
                                message="これはプレビュー表示の例です。プロフィールは保存・公開されていません。"
                            />
                        @endif
                        <article class="profile-preview-card" aria-label="公開プロフィール表示サンプル">
                            <span class="profile-preview-card__label">プロフィール表示サンプル</span>
                            <h3>表示名のサンプル</h3>
                            <p>自己紹介文の表示例です。実在の利用者や個人の情報は含まれていません。</p>
                            <dl>
                                <div><dt>活動エリア</dt><dd>表示例</dd></div>
                                <div><dt>プロフィール公開設定</dt><dd>機能未実装</dd></div>
                            </dl>
                        </article>
                        <p class="member-help"><a href="{{ route('mypage.profile') }}">プロフィール編集画面へ</a> ・ <a href="{{ route('mypage.profile.preview') }}">プレビュー表示へ</a></p>
                    @elseif ($pageKey === 'settings')
                        <p class="member-help">アカウント設定の表示例です。実際の登録情報や変更機能はありません。</p>
                        <div class="setting-row"><div><h3>ログイン情報</h3><p>メールアドレス・パスワード</p></div><span class="status-pill">未設定</span></div>
                        <div class="setting-row"><div><h3>アカウント状態</h3><p>会員認証は未実装です</p></div><span class="status-pill">準備中</span></div>
                        <p class="member-help"><a href="{{ route('mypage.security') }}">セキュリティ設定を見る</a> ・ <a href="{{ route('mypage.account') }}">アカウント管理の案内</a></p>
                    @elseif ($pageKey === 'security')
                        <p class="member-help">セキュリティ項目の表示例です。認証情報やログイン履歴は扱っていません。</p>
                        <div class="setting-row"><div><h3>パスワード変更</h3><p>会員認証・変更処理は未実装です</p></div><span class="status-pill">利用不可</span></div>
                        <div class="setting-row"><div><h3>ログイン中の端末</h3><p>セッション管理機能は未実装です</p></div><span class="status-pill">表示なし</span></div>
                        <div class="setting-row"><div><h3>追加認証</h3><p>追加認証機能は未実装です</p></div><span class="status-pill">未設定</span></div>
                        <x-feedback-message
                            type="info"
                            title="セキュリティ機能は未実装です"
                            message="この画面では認証・パスワード変更・端末管理を実行できません。"
                        />
                    @elseif ($pageKey === 'account')
                        <p class="member-help">アカウント機能を利用する際に確認する項目の案内です。現在は会員機能を提供していません。</p>
                        <section class="account-guide">
                            <h3>アカウントを利用する前に</h3>
                            <ol>
                                <li>サービスの提供範囲と利用条件を確認する。</li>
                                <li>個人情報の取扱方針を確認する。</li>
                                <li>パスワード等の認証情報を適切に管理する。</li>
                            </ol>
                        </section>
                        <p class="member-help"><a href="{{ route('terms') }}">利用規約（準備中）</a> ・ <a href="{{ route('privacy') }}">プライバシーポリシー（準備中）</a></p>
                        <x-feedback-message
                            type="info"
                            title="アカウント管理は未実装です"
                            message="登録、退会、情報変更、問い合わせの操作は行えません。"
                        />
                    @elseif ($pageKey === 'announcements')
                        <p class="member-help">以下は一覧レイアウト確認用の仮データです。実際のお知らせや配信履歴ではありません。</p>
                        <ul class="announcement-list">
                            <li>
                                <span class="status-pill">表示サンプル</span>
                                <h3>お知らせタイトルの表示例</h3>
                                <p>サービスのお知らせを表示する行のサンプルです。実際の告知ではありません。</p>
                            </li>
                            <li>
                                <span class="status-pill">表示サンプル</span>
                                <h3>メンテナンス案内の表示例</h3>
                                <p>日付や本文を含むお知らせ一覧の表示確認用です。実際の予定を示すものではありません。</p>
                            </li>
                        </ul>
                        <x-feedback-message
                            type="info"
                            title="お知らせ機能は未実装です"
                            message="通知の配信・既読管理・お知らせの登録機能はありません。"
                        />
                    @else
                        <p class="member-help">通知設定の表示例です。通知の配信・変更機能はありません。</p>
                        <div class="setting-row"><div><h3>お知らせ</h3><p>サイトに関するお知らせ</p></div><span class="status-pill">未設定</span></div>
                        <div class="setting-row"><div><h3>メール通知</h3><p>メール送信機能は未実装です</p></div><span class="status-pill">準備中</span></div>
                        <p class="member-help"><a href="{{ route('mypage.announcements') }}">お知らせ一覧を見る</a></p>
                    @endif
                </section>
            </div>
        </div>
    </main>
    <x-site-footer />
</body>
</html>
