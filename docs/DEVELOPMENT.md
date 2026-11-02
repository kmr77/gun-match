# Laravel開発基盤

## 実装範囲

Laravel 13の `/` はGunMatchのブランドTOPです。共通Bladeレイアウトとヘッダー・フッター・セクション見出し・カテゴリカード・理由カード・利用ステップの匿名コンポーネントを使用します。ページ構成はヒーロー、カテゴリー、新着の「譲る」、新着の「探す」、選ばれる理由、利用の流れ、最下部バナーです。商品帯には一般的なアウトドア用品のデザイン確認用仮データだけを表示します。GM-003時点の経緯は [GM-003.md](GM-003.md) を参照してください。

### 未実装機能と素材

- 会員登録・ログイン・メール確認・パスワード再設定・基本プロフィール管理を実装しています。会員確認審査の管理基盤はありますが、本人確認書類のアップロードやSMS認証は未実装です。銃器・銃身の売買募集、取得希望者のマッチング、取引問い合わせも未実装です。
- 公開画面の見出し・説明はGunMatchのブランドや一般的な安全姿勢を表し、未提供機能を提供済みとは記載しません。開発状況は本書およびREADMEで管理します。
- 新着商品帯は固定配列の一般的なアウトドア用品6点を各セクションに表示します。カードは取引リンクを持たず、仮データである旨を明示しています。`public/assets/img/dummy/outdoor/` の6点のSVGはこの表示用に作成したイラストで、外部写真素材は使用していません。
- 既存の画像素材を調査しましたが、夕景の山並みと鹿が写る最下部バナー素材は見つかりませんでした。他の画像による代用はせず、素材提供後に差し替えます。

初期導入では既存ファイルを保持しました。その後、下記「素材・資料の整理」に従って配置を整理しています。元のHTMLはLaravelのルートから配信していません。

## ローカル起動

通常の `php` はPHP 8.2.8を指しているため、MAMPのPHP 8.4.1を使用します。

```bash
export PATH="/Applications/MAMP/bin/php/php8.4.1/bin:$PATH"
composer install
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi
php artisan serve --host=127.0.0.1 --port=8000
```

`.env` がすでにある場合は、APP_KEYを再生成せず既存設定を確認してください。

ブラウザで http://127.0.0.1:8000 を開きます。MAMPから配信する場合、PHP 8.3以上を選択し、DocumentRootをこのプロジェクトの `public/` に設定してください。プロジェクトルートをそのまま公開しないでください。

ローカルの通常接続先はMAMP MySQL 8.0.40のGunMatch専用DB `gunmatch_dev_gm023`（ポート `8889`）です。Git管理対象外の `.env` では `DB_CONNECTION=mysql`、`DB_HOST=127.0.0.1`、`DB_PORT=8889`、`DB_DATABASE=gunmatch_dev_gm023` とし、`DB_USERNAME`・`DB_PASSWORD` はMAMPのローカル設定に合わせてください。接続情報をソースコードやGit管理ファイルへ記載しないでください。

SQLiteの `database/database.sqlite` は削除・初期化せず、GM-024時点のデータをそのままバックアップとして保持します。既存の会員・管理者とアプリケーションデータは専用のMySQL開発DBへ移行しています。SQLiteへ戻す場合は `.env` の `DB_CONNECTION=sqlite`、`DB_DATABASE=database/database.sqlite`（またはSQLiteファイルの絶対パス）に切り替え、`php artisan config:clear` を実行してください。設定を確実に反映するため、起動中の `php artisan serve` も再起動してください。

自動テストは通常の `phpunit.xml` によりSQLiteメモリDBを使います。MySQLでテストするときは `phpunit.mysql.xml` を指定してください。この設定は接続先をテスト専用の `gunmatch_test_gm024` に固定し、認証情報はローカル `.env` から読み込みます。各Featureテストの `RefreshDatabase` はこのテスト専用DBを対象にします。MySQL開発DBをテスト設定として指定したり、テスト用設定を使わずに破壊的なテストを実行したりしないでください。

会員認証にはLaravel標準のセッションGuard、パスワードBroker、署名付きメール確認を使用します。登録時は本名、表示名、メールアドレス、都道府県、生年月日を検証し、18歳未満を拒否します。電話番号と所持許可の有無は任意入力で、SMS認証は未実装です。所持許可欄は自己申告であり、運営による確認状態・会員確認審査とは連動しません。本名・メールアドレス・都道府県・生年月日・電話番号・所持許可は公開プロフィールに表示せず、`User` の配列シリアライズからも除外します。ローカルのメール設定はログ出力用で、本番メール配信は未設定です。利用規約・プライバシーポリシーと運営情報の確認が完了するまで、公開環境で会員情報を収集しないでください。確定・未確定の整理は [GUNMATCH_MVP_SPEC.md](GUNMATCH_MVP_SPEC.md) を参照してください。

メールアドレス未確認の会員もログイン後にマイページと基本プロフィール管理を利用できますが、管理者ページはメール確認済みかつ管理者権限のある会員に限定します。プロフィール更新は本人の `users` 行だけを対象とし、`admin_activity_logs` へ変更カラム名のみを記録します。変更値・本名・電話番号等は監査詳細に複製しません。取引等の機能は未実装です。メール認証・パスワード再設定通知は、ローカルでは既存のログ出力設定（`MAIL_MAILER=log`）を使用します。

本番メール配信を設定する場合は、デプロイ先の環境変数またはホスティング事業者のシークレット管理機能で `MAIL_MAILER=smtp`、`MAIL_HOST`、`MAIL_PORT`、`MAIL_USERNAME`、`MAIL_PASSWORD`、必要な場合の `MAIL_SCHEME`、`MAIL_FROM_ADDRESS`、`MAIL_FROM_NAME` を設定し、使用するメール事業者の接続情報に合わせてください。SMTP認証情報を `.env` のGit追跡対象化、ソースコード、コミット、公開ログに保存してはいけません。認証情報を含む `MAIL_URL` は使わず、個別の環境変数／シークレットを利用してください。設定後はステージングで配送・送信元・迷惑メール対策を確認します。現時点では実メール配送の事業者情報・認証情報は未設定です。

管理者権限はWebフォームから設定できません。信頼できるサーバー上のCLIで `php artisan gunmatch:admin member@example.com` を実行し、対象メールアドレスと確認プロンプトを確認してから承認します。解除は `php artisan gunmatch:admin member@example.com --revoke` です。停止中・メール未確認会員への付与と、最後の有効な管理者権限の解除は拒否され、操作履歴へ記録されます。CLIを実行できるOS／デプロイ権限は限定し、対象メールアドレスと権限変更を別担当者が確認する運用を推奨します。`is_admin` を会員区分（個人会員・銃砲店会員）から変更・推定してはいけません。

管理者ページ `/admin` では会員総数・有効会員数・停止中会員数、検索可能な会員一覧、管理操作履歴を確認できます。表示名から `/admin/members/{user}` の詳細ページを開き、既存の会員情報と対象会員の操作履歴を確認できます。両ページは認証済み・有効・メール確認済みの管理者に限定されています。管理操作のデザインと集計は既存データを利用し、新しいテーブルは作成しません。

会員詳細では本人確認・事業者確認の状態を未確認・確認待ち・確認済み・差戻しで管理します。既存会員と新規会員の初期状態は未確認です。会員本人が状態を変更する機能はなく、確認済みへの変更には実際に行った確認方法と確認日を管理者が入力します。審査担当者・審査日時・管理者用メモ・最終確認日時は `users` に記録し、状態変更履歴は既存の `admin_activity_logs` に保存します。監査履歴には状態・区分・記録有無のみを保存し、審査メモや確認方法の本文は複製しません。本人確認書類の画像、許可証画像、証明書番号は収集・保存しません。

`/contact` はログイン不要の一般運営問い合わせフォームです。CSRF、サーバー側入力検証、IP単位の送信回数制限（5回/分）を適用し、問い合わせ本文は `contact_inquiries` に保存します。管理者は `/admin/contact-inquiries` から確認し、未対応・対応中・対応済みを設定できます。対応済みへ変更した日時を保存し、他の状態へ戻すと対応日時を解除します。自動返信・添付・会員間のメッセージ機能はありません。個人情報を含む問い合わせの保持・削除期間は公開前に運用方針として確定してください。

管理者のお知らせ管理は `/admin/announcements` で行い、下書きの作成・編集・削除、公開状態と公開日時を設定できます。公開一覧 `/announcements`、詳細 `/announcements/{announcement}`、ログイン必須の `/mypage/announcements` は公開状態かつ公開日時到来済みの情報のみを新しい順で表示します。お知らせは `announcements` テーブルに保存し、メール一斉配信・プッシュ通知・既読管理は実装していません。

ログイン中の会員は `/mypage/reports/create` から規約違反・不正利用・安全上の懸念などを運営へ通報できます。送信者IDと受付日時をシステム側で記録し、入力検証・CSRF対策・5回/分の送信制限を適用します。通報内容は `abuse_reports` に保存され、一般会員向けの一覧・詳細画面はありません。管理者は `/admin/reports` から通報者・通報内容を確認し、対応状態と管理メモを記録できます。担当管理者・対応日時を保存し、対応記録は既存の `admin_activity_logs` に `report.updated` として監査記録します。通報情報の保持・削除期間は公開前に運用方針として確定してください。

管理者会員の検索は、表示名・メールアドレスに対する `LOWER(...) LIKE ?` のバインドクエリを使用します。会員状態、操作履歴、問い合わせ、お知らせ、通報、会員確認審査、プロフィール項目のマイグレーションはLaravel Schema Builderの一般的な型・外部キー・インデックスを使い、SQLite固有SQLは追加していません。MAMP MySQL 8.0.40では開発DBに全12マイグレーションが適用済みで、専用MySQLテストDB上のLaravel全63テスト（1,610 assertions）が成功しました。SQLiteメモリDBでも同じ全テストが成功しました。MySQLの `utf8mb4_unicode_ci` とSQLiteでは文字列比較・照合順序の挙動が異なり得るため、メールアドレスはアプリケーションで小文字化し、検索では `LOWER` を使用しています。本番MySQLへの接続、実データ移行、照合順序・バックアップ復元試験は未実施です。本番採用時は本番同等のステージングで検証してください。

## 検証コマンド

```bash
export PATH="/Applications/MAMP/bin/php/php8.4.1/bin:$PATH"
php artisan --version
php artisan route:list
php artisan view:cache
php artisan test
```

フロントエンドのnpm依存関係は未導入です。現在のTOPは `public/assets/css/gunmatch.css` と `public/assets/js/gunmatch.js` を直接読み込むため、Viteのビルドは不要です。`welcome.blade.php` は既存資料として保持していますが、現在の `/` からは使用しません。

## ページ一覧とルーティング（GM-010）

以下は `routes/web.php` に登録したページのルートです。会員関連の登録・認証・基本プロフィールはDBと連携しますが、取引・掲載等の業務機能は提供しません。
ページごとのデザイン、共通部品、PC/SP検証、バックエンド要否、残作業の棚卸しは [GM-008-page-inventory.md](GM-008-page-inventory.md) を参照してください。

| 種別 | URL | ルート名 | 実装範囲 |
| --- | --- | --- | --- |
| ブランドTOP | `/` | `home` | TOP・固定の仮データ表示 |
| カテゴリー一覧 | `/categories` | `categories.index` | 既存7カテゴリーの表示例。掲載・検索・譲渡・取得機能なし |
| カテゴリー別案内 | `/categories/{slug}` | `categories.show` | 設定データを単一Bladeで表示。案内のみ、取引機能なし |
| アウトドア用品サンプル一覧 | `/outdoor-samples` | `outdoor-samples.index` | 架空サンプルの表示のみ。取引機能なし |
| アウトドア用品サンプル詳細 | `/outdoor-samples/{slug}` | `outdoor-samples.show` | 共通詳細表示。価格・問い合わせ・取引機能なし |
| GunMatchについて | `/about` | `about` | 公開案内 |
| 初めての方・利用ガイド | `/guide` | `guide` | 公開案内 |
| 安全・法令について | `/safety` | `safety` | 一般的な案内。個別の法的判断なし |
| 禁止事項・禁止品目 | `/prohibited-items` | `prohibited-items` | 正式一覧は公開前確認中 |
| よくある質問 | `/faq` | `faq` | 静的FAQ |
| 利用規約 | `/terms` | `terms` | 公開前確認中 |
| プライバシーポリシー | `/privacy` | `privacy` | 公開前確認中 |
| お問い合わせ案内 | `/contact` | `contact` | 案内のみ。送信フォームなし |
| ログイン | `/login` | `login` | LaravelセッションGuard・ログイン試行制限 |
| ログアウト | `POST /logout` | `logout` | セッション無効化とCSRF保護 |
| 新規会員登録 | `/register` | `register` | 本名・表示名・メール・都道府県・生年月日（18歳以上）、任意電話番号・所持許可自己申告、会員区分 |
| パスワード再設定案内 | `/forgot-password` | `password.request` | Laravel Password Broker・メール配信はローカルログ設定 |
| パスワード再設定 | `/reset-password/{token}` | `password.reset` | 期限・一回限りのトークンを検証 |
| メール確認 | `/email/verify` | `verification.notice` | 認証済みユーザー向け署名付き確認フロー |
| マイページTOP | `/mypage` | `mypage` | 認証必須・ログイン中の会員情報を表示 |
| プロフィール編集 | `/mypage/profile` | `mypage.profile` | 本人の登録情報を更新。メール変更時は再確認し、変更カラム名を監査 |
| アカウント削除 | `DELETE /mypage/account` | `mypage.account.destroy` | 現在のパスワード再確認、関連セッション・再設定トークンを無効化して本人アカウントを削除 |
| 管理者会員管理 | `/admin` | `admin.dashboard` | メール確認済みかつ有効な管理者のみ。表示名・メール検索、会員状態、登録日時、メール認証状態、直近の操作履歴を表示 |
| 管理者会員詳細 | `/admin/members/{user}` | `admin.members.show` | 管理者専用。会員情報・対象会員の操作履歴・既存の状態変更操作を表示 |
| 会員状態変更 | `PATCH /admin/members/{user}/status` | `admin.members.status.update` | 管理者専用。停止時にDBセッションを削除し、履歴を保存 |
| お問い合わせ | `/contact`、`POST /contact` | `contact`、`contact.store` | ログイン不要、検証・CSRF・送信制限付きでDB保存 |
| お問い合わせ管理 | `/admin/contact-inquiries` | `admin.contact-inquiries.index` | 管理者専用の一覧・詳細・対応状態更新 |
| 会員通報フォーム | `/mypage/reports/create`、`POST /mypage/reports` | `mypage.reports.create`、`mypage.reports.store` | ログイン会員専用。一般公開なし、送信回数制限あり |
| 管理者通報管理 | `/admin/reports`、`/admin/reports/{report}` | `admin.reports.index`、`admin.reports.show`、`admin.reports.update` | 管理者専用。状態・管理メモ・担当者を記録し、既存監査履歴へ記録 |
| 公開お知らせ一覧・詳細 | `/announcements`、`/announcements/{announcement}` | `announcements.index`、`announcements.show` | 公開済みかつ公開日時到来済みのお知らせのみ表示 |
| マイページお知らせ | `/mypage/announcements` | `mypage.announcements` | 公開済みお知らせを共通データから表示 |
| 管理者お知らせ管理 | `/admin/announcements` | `admin.announcements.index` | 管理者専用の作成・編集・削除・公開状態管理 |
| 会員確認・審査管理 | `/admin/members/{user}`、`PATCH /admin/members/{user}/verification` | `admin.members.show`、`admin.members.verification.update` | 管理者専用。確認状態・区分・担当者・確認方法／日・管理メモと状態変更履歴 |
| 公開プロフィール | `/mypage/profile/public` | `mypage.profile.public` | 個人情報を含まない表示サンプル |
| プロフィールプレビュー | `/mypage/profile/preview` | `mypage.profile.preview` | 保存・公開されない表示例 |
| アカウント設定 | `/mypage/settings` | `mypage.settings` | 表示のみ。変更なし |
| セキュリティ設定 | `/mypage/security` | `mypage.security` | 現在のパスワードを検証して変更 |
| アカウント管理案内 | `/mypage/account` | `mypage.account` | 静的な案内。操作なし |
| 通知設定 | `/mypage/notifications` | `mypage.notifications` | 表示のみ。配信・変更なし |
| お知らせ一覧 | `/mypage/announcements` | `mypage.announcements` | 公開済みお知らせを共通データから表示 |
| Not Found | 存在しないURL | Laravel標準404 | `resources/views/errors/404.blade.php` |
| Laravelヘルスチェック | `/up` | `healthcheck` | フレームワーク提供 |

共通レイアウトは公開案内で `layouts.app` を再利用し、マイページは認証必須の操作性重視レイアウトです。ページ見出し、入力欄・選択欄、マイページメニュー、状態メッセージはBladeコンポーネント化しています。プロフィール画面はログインユーザー自身の情報に限定します。管理者権限は会員区分（個人会員・銃砲店会員）と独立した `is_admin` で判定し、会員フォームから変更できません。管理者権限の付与・解除は確認プロンプト付きのCLIコマンドに限定します。管理操作履歴はDBに記録します。

GM-025では `users` に `legal_name`、`prefecture`、`birth_date`、`phone`、`phone_verified_at`、`has_firearm_permit` を追加しました。既存会員の列値はNULLのまま維持し、プロフィールから補完できます。MySQL開発DBとローカルSQLiteの双方に追加マイグレーションを適用し、会員行・管理者権限は保持しています。電話番号の必須性と所持許可自己申告の必須性・銃砲店会員への適用は未確定です。SMS事業者契約がないため、SMS実配送・電話番号確認は行いません。

退会では本人の現在のパスワードを再確認し、usersのアカウント行、本人に紐づくDBセッション、パスワード再設定トークンを削除します。現時点でアカウントに紐づく他の業務データはありません。将来、関連データを追加する際は削除・匿名化の対象を見直してください。アプリケーションログやバックアップの保持期間・消去時期は運営方針と法令確認後に定める必要があり、この削除処理だけでバックアップ上の即時消去を保証するものではありません。の情報に限定します。通知設定・お知らせ・公開プロフィールの実公開機能、退会処理、本人確認および取引機能は未実装です。

### 公開前に確認が必要な事項

- 運営者の正式情報、適用法令、提供条件、責任範囲を確認してから利用規約を作成する。
- 実際の情報取得・利用・保存・委託・第三者提供・問い合わせ運用を確定してからプライバシーポリシーを作成する。
- 運営者の受付体制と連絡先を確定するまで問い合わせ窓口を公開しない。
- 法令・安全に関する正式な説明や禁止品目一覧は、必要な専門的・法的確認を経るまで断定しない。

会員認証基盤のローカル検証では、`php artisan test` を使用します。DBマイグレーションを戻す `migrate:rollback` や `migrate:fresh` は既存データを消去する可能性があるため、明示的な承認なしに実行しないでください。

会員認証・プロフィール管理以外の売買募集・銃器/銃身のマッチング・取引問い合わせ・通知配信・決済等の業務処理は実装しません。運営への一般問い合わせと規約違反等の通報はそれぞれ独立した受付・管理機能であり、会員間のメッセージ機能ではありません。

## Git管理

`.env`、認証ファイル、vendor、node_modules、ログ、キャッシュ、SQLite実体はコミット対象から除外します。初期コミット `f4a4766` ではLaravel基盤とAGENTS.mdを保存し、今回の整理でREADME・参考HTML・既存CSS・既存PNG・作業指示書も管理対象に追加します。

## 初期導入の検証結果（2026-10-21）

- PHP 8.4.1でLaravel Framework 13.38.0が起動。
- `/` と `/up` のHTTP 200応答を確認。
- ルーティング一覧の取得、Bladeのコンパイルに成功。
- 標準テスト2件、アサーション2件に成功。
- アプリ・設定・ルート・標準テスト等のPHP 25ファイルで構文エラーなし。
- 作業前から存在した全ファイルのSHA-256が作業後と一致。
- `.env`、vendor、ログ、SQLite実体がGit除外対象であることを確認。
- PC・スマートフォンのブラウザ表示、参考画像との比較は未実施。
- Composer 2.5.8はPHP 8.4環境で非推奨警告を出しましたが、依存関係の導入は終了コード0で完了。グローバルComposerは変更していません。

## 素材・資料の整理（2026-10-21）

- `logo.png` → `public/assets/img/logo/logo.png`
- `image.png` → `public/assets/img/hero/hero-main.png`
- `category_*.png`（既存6枚）→ `public/assets/img/categories/`
- `gunmatch.css` → `public/assets/css/gunmatch.css`（内容は変更なし）
- `CODEX_作業指示.md` → `docs/CODEX_作業指示.md`（内容は変更なし）
- `index.html` → `docs/reference/index.html`（参照パスのみ修正）
- `public/assets/js/` は `.gitkeep` のみ。ダミーJavaScriptは追加していません。
- `AGENTS.md` はルートで内容を保持。README.mdはルートで保持し、現状説明を追記。
- LaravelのPHP・Blade・ルート・依存関係・DB設定は変更していません。

### 参考資料の参照と不足ファイル

参考HTMLのCSS・ロゴ・カテゴリ画像への参照を新しい配置に合わせています。旧WebP参照は既存のPNGに合わせ、フッターロゴの未展開プレースホルダーも実際のロゴパスに修正しています。本文・掲載内容・スタイル指定は変更していません。

次の参照先は元から未同梱です。素材の代替や生成、機能実装は行っていないため、参考HTMLを開いても完全なデザイン表示にはなりません。

- `public/assets/img/categories/category_air_rifle.png`：プロジェクト全体を検索したが見つからず。カテゴリ画像は7種類中6枚。
- `public/assets/icons/` の `search.svg`、`mail-check.svg`、`badge-check.svg`、`message-circle.svg`、`bell.svg`。
- `public/assets/js/gunmatch.js`：元HTMLに参照のみ存在。メニュー等の動作は未実装。
- CSS内の `../img/branding/hero-mountain-hunter.webp` と `../img/branding/banner-hunting.webp`：元の参照を保持。`hero-main.png` は指定どおり保管したが、CSSの内容保持を優先して背景参照は変更していません。

参考HTMLは `docs/` 内の資料であり、Laravelの公開ルートに追加していません。

### 整理後の検証

- 作業前の70ファイルを記録し、画像・CSS・作業指示書・AGENTS.md・Laravel構成ファイルの内容保持を確認。変更は参考HTMLの参照パスとREADME・本書の説明のみ。
- Laravel 13.38.0の起動、既存4ルートの一覧取得、標準テスト2件の成功を確認。
- `/`・`/up`・CSS・画像8枚の計11件でHTTP 200。CSS・画像のHTTPレスポンスは実ファイルとハッシュ一致。
- 参考HTMLのローカル参照15種類を点検し、不足は上記の7ファイル（画像1枚・SVG 5点・JS 1点）のみ。CSS内の不足背景参照2種類は別途記載済み。
- `.env`・vendor・node_modules・auth.json・ログのGit除外を確認。
- 今回はファイル整理のみ。TOP実装やデザイン変更、ブラウザでのPC・スマートフォン表示確認は行っていません。

## 参考資料

上記の初期導入・整理時点の記録以降、GM-001でヒーロー参照を修正し、GM-002で不足素材8点を追加済みです。現在は素材不足を解消しています。履歴は [GM-001.md](GM-001.md)、現在のページ構成は [GM-003.md](GM-003.md) を参照してください。

- [Laravel 13 インストール](https://laravel.com/framework/docs/13.x/installation)
- [Laravel 13 サーバー要件と公開ディレクトリ](https://laravel.com/framework/docs/13.x/deployment)
