# Laravel開発基盤

## 実装範囲

Laravel 13の `/` はGunMatchのブランドTOPです。共通Bladeレイアウトとヘッダー・フッター・セクション見出し・カテゴリカード・理由カード・利用ステップの匿名コンポーネントを使用します。ページ構成はヒーロー、カテゴリー、新着の「譲る」、新着の「探す」、選ばれる理由、利用の流れ、最下部バナーです。商品帯には一般的なアウトドア用品のデザイン確認用仮データだけを表示します。GM-003時点の経緯は [GM-003.md](GM-003.md) を参照してください。

### 未実装機能と素材

- 会員登録・ログイン・メール確認・パスワード再設定・基本プロフィール管理を実装しています。銃器・銃身の売買募集、取得希望者のマッチング、取引問い合わせ、本人確認は未実装です。
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

開発用DBは既存の `.env` 設定に従いSQLite（`database/database.sqlite`）を使用します。DBファイルがない場合は `touch database/database.sqlite` で空ファイルを作成してから `php artisan migrate` を実行してください。マイグレーションで会員・パスワード再設定・セッションの各テーブルを作成します。現在のローカル環境は `SESSION_DRIVER=database` を使用しています。`.env` のAPP_KEYや既存DB接続設定を上書きしないでください。

会員認証にはLaravel標準のセッションGuard、パスワードBroker、署名付きメール確認を使用します。ローカルのメール設定はログ出力用で、本番メール配信は未設定です。利用規約・プライバシーポリシーと運営情報の確認が完了するまで、公開環境で会員情報を収集しないでください。

メールアドレス未確認の会員もログイン後にマイページと基本プロフィール管理を利用できますが、管理者ページはメール確認済みかつ管理者権限のある会員に限定します。取引等の機能は未実装です。メール認証・パスワード再設定通知は、ローカルでは既存のログ出力設定（`MAIL_MAILER=log`）を使用します。

本番メール配信を設定する場合は、デプロイ先の環境変数またはホスティング事業者のシークレット管理機能で `MAIL_MAILER=smtp`、`MAIL_HOST`、`MAIL_PORT`、`MAIL_USERNAME`、`MAIL_PASSWORD`、必要な場合の `MAIL_SCHEME`、`MAIL_FROM_ADDRESS`、`MAIL_FROM_NAME` を設定し、使用するメール事業者の接続情報に合わせてください。SMTP認証情報を `.env` のGit追跡対象化、ソースコード、コミット、公開ログに保存してはいけません。認証情報を含む `MAIL_URL` は使わず、個別の環境変数／シークレットを利用してください。設定後はステージングで配送・送信元・迷惑メール対策を確認します。現時点では実メール配送の事業者情報・認証情報は未設定です。

管理者権限はWebフォームから設定できません。信頼できるサーバー上のCLIで `php artisan gunmatch:admin member@example.com` を実行し、対象メールアドレスと確認プロンプトを確認してから承認します。解除は `php artisan gunmatch:admin member@example.com --revoke` です。停止中・メール未確認会員への付与と、最後の有効な管理者権限の解除は拒否され、操作履歴へ記録されます。CLIを実行できるOS／デプロイ権限は限定し、対象メールアドレスと権限変更を別担当者が確認する運用を推奨します。`is_admin` を会員区分（個人会員・銃砲店会員）から変更・推定してはいけません。

管理者会員の検索は、名前・メールアドレスに対する `LOWER(...) LIKE ?` のバインドクエリを使用します。会員状態と操作履歴のマイグレーションはLaravel Schema Builderのboolean、foreignId、text、通常の複合indexを使い、SQLite固有SQLは追加していません。MySQLでも一般的な型に対応する設計ですが、本番MySQLへの接続・マイグレーション・照合順序・バックアップ復元試験は未実施です。MySQL採用時は本番同等のステージングで検証してください。

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
| 新規会員登録 | `/register` | `register` | 個人会員・銃砲店会員、メール重複防止 |
| パスワード再設定案内 | `/forgot-password` | `password.request` | Laravel Password Broker・メール配信はローカルログ設定 |
| パスワード再設定 | `/reset-password/{token}` | `password.reset` | 期限・一回限りのトークンを検証 |
| メール確認 | `/email/verify` | `verification.notice` | 認証済みユーザー向け署名付き確認フロー |
| マイページTOP | `/mypage` | `mypage` | 認証必須・ログイン中の会員情報を表示 |
| プロフィール編集 | `/mypage/profile` | `mypage.profile` | 認証中の本人情報のみ更新 |
| アカウント削除 | `DELETE /mypage/account` | `mypage.account.destroy` | 現在のパスワード再確認、関連セッション・再設定トークンを無効化して本人アカウントを削除 |
| 管理者会員管理 | `/admin` | `admin.dashboard` | メール確認済みかつ有効な管理者のみ。名前・メール検索、会員状態、登録日時、メール認証状態、直近の操作履歴を表示 |
| 会員状態変更 | `PATCH /admin/members/{user}/status` | `admin.members.status.update` | 管理者専用。停止時にDBセッションを削除し、履歴を保存 |
| 公開プロフィール | `/mypage/profile/public` | `mypage.profile.public` | 個人情報を含まない表示サンプル |
| プロフィールプレビュー | `/mypage/profile/preview` | `mypage.profile.preview` | 保存・公開されない表示例 |
| アカウント設定 | `/mypage/settings` | `mypage.settings` | 表示のみ。変更なし |
| セキュリティ設定 | `/mypage/security` | `mypage.security` | 現在のパスワードを検証して変更 |
| アカウント管理案内 | `/mypage/account` | `mypage.account` | 静的な案内。操作なし |
| 通知設定 | `/mypage/notifications` | `mypage.notifications` | 表示のみ。配信・変更なし |
| お知らせ一覧 | `/mypage/announcements` | `mypage.announcements` | 固定の表示確認用仮データ |
| Not Found | 存在しないURL | Laravel標準404 | `resources/views/errors/404.blade.php` |
| Laravelヘルスチェック | `/up` | `healthcheck` | フレームワーク提供 |

共通レイアウトは公開案内で `layouts.app` を再利用し、マイページは認証必須の操作性重視レイアウトです。ページ見出し、入力欄・選択欄、マイページメニュー、状態メッセージはBladeコンポーネント化しています。プロフィール画面はログインユーザー自身の情報に限定します。管理者権限は会員区分（個人会員・銃砲店会員）と独立した `is_admin` で判定し、会員フォームから変更できません。管理者権限の付与・解除は確認プロンプト付きのCLIコマンドに限定します。管理操作履歴はDBに記録します。

退会では本人の現在のパスワードを再確認し、usersのアカウント行、本人に紐づくDBセッション、パスワード再設定トークンを削除します。現時点でアカウントに紐づく他の業務データはありません。将来、関連データを追加する際は削除・匿名化の対象を見直してください。アプリケーションログやバックアップの保持期間・消去時期は運営方針と法令確認後に定める必要があり、この削除処理だけでバックアップ上の即時消去を保証するものではありません。の情報に限定します。通知設定・お知らせ・公開プロフィールの実公開機能、退会処理、本人確認および取引機能は未実装です。

### 公開前に確認が必要な事項

- 運営者の正式情報、適用法令、提供条件、責任範囲を確認してから利用規約を作成する。
- 実際の情報取得・利用・保存・委託・第三者提供・問い合わせ運用を確定してからプライバシーポリシーを作成する。
- 運営者の受付体制と連絡先を確定するまで問い合わせ窓口を公開しない。
- 法令・安全に関する正式な説明や禁止品目一覧は、必要な専門的・法的確認を経るまで断定しない。

会員認証基盤のローカル検証では、`php artisan test` を使用します。DBマイグレーションを戻す `migrate:rollback` や `migrate:fresh` は既存データを消去する可能性があるため、明示的な承認なしに実行しないでください。

会員認証・プロフィール管理以外の売買募集・銃器/銃身のマッチング・取引問い合わせ・通知配信・決済等の業務処理は実装しません。

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
