<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GunMatch会員アカウントとプロフィールを管理します。">
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
                description="ログイン中の会員アカウントと基本プロフィールを管理します。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'マイページ', 'route' => 'mypage'], ['label' => $title]]"
            />
            <div class="member-toolbar">
                @if (session('status'))
                    <x-feedback-message type="success" title="更新しました" :message="session('status')" />
                @endif
                @if ($errors->any())
                    <x-feedback-message type="error" title="入力内容をご確認ください" message="各項目のエラーを修正して、もう一度送信してください。" />
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn--outline" type="submit">ログアウト</button>
                </form>
            </div>
            <div class="mypage-layout">
                <x-mypage-nav :active="$pageKey" />
                <section class="mypage-content" aria-labelledby="mypage-content-title">
                    <h2 id="mypage-content-title">{{ $title }}</h2>

                    @if ($pageKey === 'home')
                        <div class="empty-state">
                            <h3>会員アカウント</h3>
                            <dl class="account-summary">
                                <div><dt>会員名</dt><dd>{{ auth()->user()->name }}</dd></div>
                                <div><dt>メールアドレス</dt><dd>{{ auth()->user()->email }}</dd></div>
                                <div><dt>会員区分</dt><dd>{{ auth()->user()->member_type->label() }}</dd></div>
                                <div>
                                    <dt>メール認証</dt>
                                    <dd>
                                        @if (auth()->user()->hasVerifiedEmail())
                                            認証済み
                                        @else
                                            未認証（<a href="{{ route('verification.notice') }}">確認メールを確認</a>）
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                            <p>プロフィールやパスワードは各メニューから更新できます。掲載・検索・取引に関する機能は提供していません。</p>
                        </div>
                    @elseif ($pageKey === 'profile')
                        <p class="member-help">会員名とメールアドレスを更新できます。メールアドレスを変更すると、再確認が必要です。</p>
                        <form method="POST" action="{{ route('mypage.profile.update') }}">
                            @csrf
                            @method('PUT')
                            <x-form-field
                                name="name"
                                label="会員名"
                                :required="true"
                                :value="auth()->user()->name"
                                :error="$errors->first('name')"
                            />
                            <x-form-field
                                name="email"
                                label="メールアドレス"
                                type="email"
                                autocomplete="email"
                                :required="true"
                                :value="auth()->user()->email"
                                :error="$errors->first('email')"
                            />
                            <button class="btn btn--primary" type="submit">プロフィールを保存</button>
                        </form>
                        <p class="member-help"><a href="{{ route('mypage.profile.public') }}">公開プロフィール表示</a> ・ <a href="{{ route('mypage.profile.preview') }}">プロフィールプレビュー</a></p>
                    @elseif ($pageKey === 'profile-public' || $pageKey === 'profile-preview')
                        <p class="member-help">ご自身の会員名と会員区分を表示しています。公開設定や第三者への公開機能はありません。</p>
                        @if ($pageKey === 'profile-preview')
                            <x-feedback-message
                                type="info"
                                title="プロフィールプレビュー"
                                message="現在のアカウント情報を表示しています。第三者には公開されません。"
                            />
                        @endif
                        <article class="profile-preview-card" aria-label="公開プロフィール表示サンプル">
                            <span class="profile-preview-card__label">{{ auth()->user()->member_type->label() }}</span>
                            <h3>{{ auth()->user()->name }}</h3>
                            <p>プロフィールの公開設定は未実装です。</p>
                            <dl>
                                <div><dt>会員区分</dt><dd>{{ auth()->user()->member_type->label() }}</dd></div>
                                <div><dt>公開設定</dt><dd>非公開</dd></div>
                            </dl>
                        </article>
                        @if ($pageKey === 'profile-preview')
                            <p class="member-help"><a href="{{ route('mypage.profile') }}">プロフィール編集画面へ</a> ・ <a href="{{ route('mypage.profile.public') }}">公開プロフィール表示へ</a></p>
                        @else
                            <p class="member-help"><a href="{{ route('mypage.profile') }}">プロフィール編集画面へ</a> ・ <a href="{{ route('mypage.profile.preview') }}">プレビュー表示へ</a></p>
                        @endif
                    @elseif ($pageKey === 'settings')
                        <p class="member-help">登録した会員区分とメール認証の状態です。</p>
                        <div class="setting-row"><div><h3>会員区分</h3><p>{{ auth()->user()->member_type->label() }}</p></div><span class="status-pill">登録済み</span></div>
                        <div class="setting-row"><div><h3>メール認証</h3><p>{{ auth()->user()->email }}</p></div><span class="status-pill">{{ auth()->user()->hasVerifiedEmail() ? '認証済み' : '未認証' }}</span></div>
                        <p class="member-help"><a href="{{ route('mypage.security') }}">セキュリティ設定を見る</a> ・ <a href="{{ route('mypage.account') }}">アカウント管理の案内</a></p>
                    @elseif ($pageKey === 'security')
                        <p class="member-help">現在のパスワードを確認してから、新しいパスワードに変更します。</p>
                        <form method="POST" action="{{ route('mypage.password.update') }}">
                            @csrf
                            @method('PUT')
                            <x-form-field
                                name="current_password"
                                label="現在のパスワード"
                                type="password"
                                autocomplete="current-password"
                                :required="true"
                                :error="$errors->first('current_password')"
                            />
                            <x-form-field
                                name="password"
                                label="新しいパスワード（12文字以上）"
                                type="password"
                                autocomplete="new-password"
                                :required="true"
                                :error="$errors->first('password')"
                            />
                            <x-form-field
                                name="password_confirmation"
                                label="新しいパスワード（確認）"
                                type="password"
                                autocomplete="new-password"
                                :required="true"
                            />
                            <button class="btn btn--primary" type="submit">パスワードを変更</button>
                        </form>
                    @elseif ($pageKey === 'account')
                        <p class="member-help">アカウント情報はログイン中のご本人のみ更新できます。</p>
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
                            title="提供範囲について"
                            message="登録情報の更新とパスワード変更を利用できます。退会手続きはまだ提供していません。"
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
