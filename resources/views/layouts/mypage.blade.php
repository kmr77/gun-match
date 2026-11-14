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
                @if (session('mail_delivery_error'))
                    <x-feedback-message type="error" title="メールを送信できませんでした" :message="session('mail_delivery_error')" />
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
                <x-mypage-nav :active="$pageKey" :unread-notification-count="$unreadNotificationCount ?? 0" />
                <section class="mypage-content" aria-labelledby="mypage-content-title">
                    <h2 id="mypage-content-title">{{ $title }}</h2>

                    @if ($pageKey === 'home')
                        <div class="empty-state">
                            <h3>会員アカウント</h3>
                            <dl class="account-summary">
                                <div><dt>表示名</dt><dd>{{ auth()->user()->name }}</dd></div>
                                <div><dt>メールアドレス</dt><dd>{{ auth()->user()->email }}</dd></div>
                                <div><dt>会員区分</dt><dd>{{ auth()->user()->member_type->label() }}</dd></div>
                                <div><dt>本名</dt><dd>{{ auth()->user()->legal_name ?: '未登録。プロフィールから入力してください。' }}</dd></div>
                                <div><dt>都道府県</dt><dd>{{ auth()->user()->prefecture ?: '未登録。プロフィールから入力してください。' }}</dd></div>
                                <div><dt>生年月日</dt><dd>{{ auth()->user()->birth_date?->format('Y年n月j日') ?: '未登録。プロフィールから入力してください。' }}</dd></div>
                                <div>
                                    <dt>所持許可</dt>
                                    <dd>
                                        @if (auth()->user()->has_firearm_permit === null)
                                            未回答
                                        @else
                                            {{ auth()->user()->has_firearm_permit ? '所持している（自己申告）' : '所持していない（自己申告）' }}
                                        @endif
                                    </dd>
                                </div>
                                <div><dt>メール認証</dt><dd>認証済み</dd></div>
                                <div><dt>電話番号確認</dt><dd>確認済み（本人確認審査とは別です）</dd></div>
                            </dl>
                            <p>プロフィールやパスワードは各メニューから更新できます。掲載・検索・取引に関する機能は提供していません。</p>
                        </div>
                    @elseif ($pageKey === 'profile')
                        <p class="member-help">本名・表示名などを更新できます。公開プロフィールには表示名と会員区分のみを掲載し、本名、メールアドレス、生年月日、都道府県、電話番号、所持許可の自己申告は公開しません。メールアドレスを変更するとメール再確認が必要です。電話番号を変更するとSMS認証状態がリセットされ、再確認が必要です。</p>
                        <form method="POST" action="{{ route('mypage.profile.update') }}">
                            @csrf
                            @method('PUT')
                            <x-form-field
                                name="legal_name"
                                label="本名（一般公開されません）"
                                :required="true"
                                :value="auth()->user()->legal_name"
                                :error="$errors->first('legal_name')"
                            />
                            <x-form-field
                                name="name"
                                label="表示名"
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
                            <x-form-field
                                name="prefecture"
                                label="都道府県"
                                type="select"
                                :required="true"
                                :options="config('gunmatch_registration.prefectures')"
                                :value="auth()->user()->prefecture"
                                :error="$errors->first('prefecture')"
                            />
                            <x-form-field
                                name="birth_date"
                                label="生年月日（18歳以上）"
                                type="date"
                                :required="true"
                                :value="auth()->user()->birth_date?->format('Y-m-d')"
                                :error="$errors->first('birth_date')"
                            />
                            <x-form-field
                                name="phone"
                                label="電話番号"
                                type="tel"
                                autocomplete="tel"
                                :required="true"
                                :value="auth()->user()->phone"
                                :error="$errors->first('phone')"
                            />
                            <x-form-field
                                name="has_firearm_permit"
                                label="所持許可の有無（自己申告・任意）"
                                type="select"
                                :options="['1' => '所持している（自己申告）', '0' => '所持していない（自己申告）']"
                                :value="auth()->user()->has_firearm_permit === null ? null : (auth()->user()->has_firearm_permit ? '1' : '0')"
                                :error="$errors->first('has_firearm_permit')"
                            />
                            <button class="btn btn--primary" type="submit">プロフィールを保存</button>
                        </form>
                        <p class="member-help"><a href="{{ route('mypage.profile.public') }}">プロフィール表示サンプル</a> ・ <a href="{{ route('mypage.profile.preview') }}">プロフィールプレビュー（本人のみ）</a></p>
                    @elseif ($pageKey === 'profile-public' || $pageKey === 'profile-preview')
                        <p class="member-help">
                            公開プロフィールには表示名と会員区分のみを掲載します。本名、生年月日、電話番号、メールアドレス、所持許可の自己申告、運営による確認審査の状態・メモは公開しません。
                        </p>
                        @if ($pageKey === 'profile-preview')
                            <x-feedback-message
                                type="info"
                                title="プロフィールプレビュー"
                                message="現在のアカウント情報から、公開される項目だけを表示しています。このプレビュー自体は第三者には公開されません。"
                            />
                        @endif
                        <article class="profile-preview-card" aria-label="会員公開プロフィール">
                            <span class="profile-preview-card__label">{{ auth()->user()->member_type->label() }}</span>
                            <h3>{{ auth()->user()->name }}</h3>
                            <dl>
                                <div><dt>会員区分</dt><dd>{{ auth()->user()->member_type->label() }}</dd></div>
                                <div><dt>公開情報</dt><dd>表示名・会員区分</dd></div>
                            </dl>
                        </article>
                        @if ($pageKey === 'profile-preview')
                            <p class="member-help"><a href="{{ route('mypage.profile') }}">プロフィール編集画面へ</a> ・ <a href="{{ route('mypage.profile.public') }}">公開プロフィール表示へ</a></p>
                        @else
                            <p class="member-help"><a href="{{ route('members.show', auth()->user()) }}">公開プロフィールを確認する</a> ・ <a href="{{ route('mypage.profile') }}">プロフィール編集画面へ</a></p>
                        @endif
                    @elseif ($pageKey === 'settings')
                        <p class="member-help">会員区分、運営による会員確認審査、メール・電話番号の認証状態をそれぞれ表示します。SMSによる電話確認は本人確認・事業者確認審査や所持許可の確認とは別です。</p>
                        <div class="setting-row"><div><h3>会員区分</h3><p>{{ auth()->user()->member_type->label() }}</p></div><span class="status-pill">登録済み</span></div>
                        <div class="setting-row"><div><h3>会員確認審査</h3><p>本人または事業者に関する運営の確認状況</p></div><span class="status-pill">{{ auth()->user()->verification_status?->label() ?? '未確認' }}</span></div>
                        <div class="setting-row"><div><h3>メール認証</h3><p>{{ auth()->user()->email }}</p></div><span class="status-pill">{{ auth()->user()->hasVerifiedEmail() ? '認証済み' : '未認証' }}</span></div>
                        <div class="setting-row"><div><h3>電話番号確認（SMS）</h3><p>{{ auth()->user()->phone ?: '電話番号未登録' }}</p></div><span class="status-pill">{{ auth()->user()->phone_verified_at ? '確認済み' : '未確認' }}</span></div>
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
                        <p class="member-help">アカウント情報はログイン中のご本人のみ更新できます。メール確認が未完了の場合はプロフィール補完とメール確認を先に行ってください。電話番号確認も通常の会員機能を使う前に必要です。管理者画面も本番環境ではメール・電話番号の両方を確認した管理者に限り利用できます。</p>
                        <section class="account-guide">
                            <h3>アカウントを利用する前に</h3>
                            <ol>
                                <li>サービスの提供範囲と利用条件を確認する。</li>
                                <li>個人情報の取扱方針を確認する。</li>
                                <li>パスワード等の認証情報を適切に管理する。</li>
                            </ol>
                        </section>
                        <p class="member-help"><a href="{{ route('terms') }}">利用規約（準備中）</a> ・ <a href="{{ route('privacy') }}">プライバシーポリシー（準備中）</a></p>
                        <section class="account-guide account-guide--danger" aria-labelledby="account-delete-title">
                            <h3 id="account-delete-title">アカウントの削除</h3>
                            <p>退会すると表示名、本名、メールアドレス、会員区分、パスワードなどのアカウント情報を削除します。この操作は取り消せません。</p>
                            <form method="POST" action="{{ route('mypage.account.destroy') }}">
                                @csrf
                                @method('DELETE')
                                <x-form-field
                                    name="password"
                                    label="現在のパスワード"
                                    type="password"
                                    autocomplete="current-password"
                                    :required="true"
                                    :error="$errors->first('password')"
                                />
                                <button class="btn btn--danger" type="submit">アカウントを削除する</button>
                            </form>
                        </section>
                    @elseif ($pageKey === 'announcements')
                        <ul class="announcement-list">
                            @forelse ($announcements as $announcement)
                                <li>
                                    <time datetime="{{ $announcement->published_at->toIso8601String() }}">{{ $announcement->published_at->format('Y年m月d日 H:i') }}</time>
                                    <h3><a href="{{ route('announcements.show', $announcement) }}">{{ $announcement->title }}</a></h3>
                                    <p>{{ \Illuminate\Support\Str::limit($announcement->body, 180) }}</p>
                                </li>
                            @empty
                                <li>現在公開中のお知らせはありません。</li>
                            @endforelse
                        </ul>
                        {{ $announcements->links() }}
                        <p class="member-help"><a href="{{ route('announcements.index') }}">お知らせ一覧を開く</a></p>
                    @elseif ($pageKey === 'notifications')
                        <p class="member-help">運営からのお知らせをサイト内で確認できます。メール・SMS配信、会員間の取引連絡・マッチング通知は対象外です。</p>
                        @if ($unreadNotificationCount > 0)
                            <form method="POST" action="{{ route('mypage.notifications.read-all') }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn--outline" type="submit">未読通知をすべて既読にする（{{ $unreadNotificationCount }}件）</button>
                            </form>
                        @endif
                        <ul class="announcement-list">
                            @forelse ($notifications as $notification)
                                <li>
                                    <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->format('Y年m月d日 H:i') }}</time>
                                    <h3><a href="{{ route('announcements.show', $notification->announcement) }}">{{ $notification->announcement->title }}</a></h3>
                                    <p>{{ $notification->read_at ? '既読' : '未読' }} ・ 運営からのお知らせ</p>
                                    @if (! $notification->read_at)
                                        <form method="POST" action="{{ route('mypage.notifications.read', $notification) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn--outline btn--small" type="submit">既読にする</button>
                                        </form>
                                    @endif
                                </li>
                            @empty
                                <li>現在、確認できる通知はありません。</li>
                            @endforelse
                        </ul>
                        {{ $notifications->links() }}
                        <p class="member-help"><a href="{{ route('announcements.index') }}">公開お知らせ一覧を開く</a></p>
                    @else
                        <p class="member-help">通知の設定ページはありません。</p>
                    @endif
                </section>
            </div>
        </div>
    </main>
    <x-site-footer />
</body>
</html>
