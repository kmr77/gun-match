# Laravel開発基盤

## 実装範囲

Laravel 13の `/` はGunMatchのブランドTOPです。共通Bladeレイアウトとヘッダー・フッター・セクション見出し・カテゴリカード・理由カード・利用ステップの匿名コンポーネントを使用します。ページ構成はヒーロー、カテゴリー、新着の「譲る」、新着の「探す」、選ばれる理由、利用の流れ、最下部バナーです。商品帯には一般的なアウトドア用品のデザイン確認用仮データだけを表示します。GM-003時点の経緯は [GM-003.md](GM-003.md) を参照してください。

### 未実装機能と素材

- 銃器・銃身の売買募集、取得希望者のマッチング、取引問い合わせ、会員登録、本人確認は未実装です。DBや取引機能はこのTOPの対象外です。
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

セッション・キャッシュはファイル、キューは同期実行に設定し、表示確認にDBを必要としない構成にしています。DBの作成・マイグレーションは実施していません。

## 検証コマンド

```bash
export PATH="/Applications/MAMP/bin/php/php8.4.1/bin:$PATH"
php artisan --version
php artisan route:list
php artisan view:cache
php artisan test
```

フロントエンドのnpm依存関係は未導入です。現在のTOPは `public/assets/css/gunmatch.css` と `public/assets/js/gunmatch.js` を直接読み込むため、Viteのビルドは不要です。`welcome.blade.php` は既存資料として保持していますが、現在の `/` からは使用しません。

## ページ一覧とルーティング（GM-007）

以下は `routes/web.php` に登録した静的ページのルートです。会員関連はデザイン確認用であり、認証やデータ保存を行いません。

| 種別 | URL | ルート名 | 実装範囲 |
| --- | --- | --- | --- |
| ブランドTOP | `/` | `home` | TOP・固定の仮データ表示 |
| GunMatchについて | `/about` | `about` | 公開案内 |
| 初めての方・利用ガイド | `/guide` | `guide` | 公開案内 |
| 安全・法令について | `/safety` | `safety` | 一般的な案内。個別の法的判断なし |
| 禁止事項・禁止品目 | `/prohibited-items` | `prohibited-items` | 正式一覧は公開前確認中 |
| よくある質問 | `/faq` | `faq` | 静的FAQ |
| 利用規約 | `/terms` | `terms` | 公開前確認中 |
| プライバシーポリシー | `/privacy` | `privacy` | 公開前確認中 |
| お問い合わせ案内 | `/contact` | `contact` | 案内のみ。送信フォームなし |
| ログイン | `/login` | `login` | 表示のみ。会員認証なし |
| 新規会員登録 | `/register` | `register` | 表示のみ。登録・保存なし |
| パスワード再設定案内 | `/forgot-password` | `password.request` | 表示のみ。メール送信なし |
| マイページTOP | `/mypage` | `mypage` | 共通UI見本。会員データなし |
| プロフィール編集 | `/mypage/profile` | `mypage.profile` | 表示のみ。保存なし |
| アカウント設定 | `/mypage/settings` | `mypage.settings` | 表示のみ。変更なし |
| 通知設定 | `/mypage/notifications` | `mypage.notifications` | 表示のみ。配信・変更なし |
| Not Found | 存在しないURL | Laravel標準404 | `resources/views/errors/404.blade.php` |
| Laravelヘルスチェック | `/up` | `healthcheck` | フレームワーク提供 |

共通レイアウトは公開案内・静的会員プレビューで既存の `layouts.app` とヘッダー・フッターを再利用し、マイページは同じ共通パーツを使う操作性重視のレイアウトです。ページ見出し、入力欄、マイページメニューはBladeコンポーネント化しています。

### 公開前に確認が必要な事項

- 運営者の正式情報、適用法令、提供条件、責任範囲を確認してから利用規約を作成する。
- 実際の情報取得・利用・保存・委託・第三者提供・問い合わせ運用を確定してからプライバシーポリシーを作成する。
- 運営者の受付体制と連絡先を確定するまで問い合わせ窓口を公開しない。
- 法令・安全に関する正式な説明や禁止品目一覧は、必要な専門的・法的確認を経るまで断定しない。

全ページのフロントエンド確認まで、会員認証・DB・売買募集・銃器/銃身のマッチング・取引問い合わせ・通知・決済等の業務処理は実装しません。

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
