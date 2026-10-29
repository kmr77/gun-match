# GunMatch

## 現在の構成（2026-10-22）

- Laravel 13の `/` はGunMatchのブランドTOPです。ヒーロー、カテゴリー、新着の「譲る」「探す」（一般的なアウトドア用品の仮データ）、選ばれる理由、利用の流れ、最下部バナーを表示します。
- 共通レイアウトは `resources/views/layouts/app.blade.php`、ヘッダー・フッター・セクション見出し・カテゴリカード・仮データカードは `resources/views/components/`、TOP本文は `resources/views/home.blade.php` です。
- 起動方法は [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md)、GM-003時点の実装記録は [docs/GM-003.md](docs/GM-003.md) を参照してください。
- 作業指示書は [docs/CODEX_作業指示.md](docs/CODEX_作業指示.md)、旧デザインの参考HTMLは [docs/reference/index.html](docs/reference/index.html) に保存しています。
- CSSは `public/assets/css/gunmatch.css`、画像は `public/assets/img/logo/`・`hero/`・`categories/`・`dummy/outdoor/` に配置しています。
- GM-002でカテゴリPNG 7枚・SVG 5点・バナー画像・JavaScriptがそろっています。TOPでは既存のロゴ・ヒーロー・カテゴリ画像7枚を使用しています。
- `public/assets/js/gunmatch.js` はモバイルメニューの開閉用です。
- 取引や許認可の詳細案内は提供していません。銃器等の取り扱いには関係法令の遵守が必要です。
- 会員登録・ログイン・メール確認・パスワード再設定・基本プロフィール管理・本人による退会は実装済みです。管理者ページはアクセス制御のみで、管理機能は未実装です。本番メール配信の認証情報は未設定です。本人確認、売買募集、取得希望者のマッチング、取引問い合わせは未実装です。
- TOPの商品帯は画像と固定配列を使うデザイン確認用表示です。一般的なアウトドア用品だけを扱い、実在の商品・募集や取引へのリンクはありません。ルート一覧と将来のページ計画は [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md) を参照してください。
- 夕景の山並みと鹿を写した最下部バナー画像は未提供です。素材が追加されるまでは、他の画像で代用しません。

以下は受領時のスターター説明を参考資料として保持したものです。記載のファイル構成・サービス仕様は、現在の実装状態とは一致しません。

---

# GunMatch TOPデザイン・Laravel Bladeスターター（受領時の説明）

2026-10-21 / 第一案（静的UIベース）

## 結論

GunMatchのTOPを、Laravel Blade / 外部CSS / 外部JavaScript / 画像素材に分離した**開発用ひな形**です。
**Laravelアプリ本体・DB・認証・検索機能を完成させたものではありません。**
Codexに渡す際は `CODEX_作業指示.md` と本フォルダをセットで使ってください。

## ディレクトリ

```text
app/Http/Controllers/HomeController.php         # データと表示の橋渡し。local環境のみデモ掲載
resources/views/layouts/app.blade.php           # head、ヘッダー、フッター、CSS/JS読み込み
resources/views/partials/header.blade.php       # 共通ヘッダー、SPメニュー
resources/views/partials/footer.blade.php       # 共通フッター
resources/views/components/category-card.blade.php
resources/views/components/listing-card.blade.php
resources/views/home.blade.php                  # TOPのBlade
routes/web.php                                  # ルート設定の参考。既存ファイルとマージ
public/assets/css/gunmatch.css                 # CSS本体
public/assets/js/gunmatch.js                   # SPナビだけ。業務ロジックなし
public/assets/img/branding/                   # ヒーロー、ロゴ、バナー
public/assets/img/categories/                 # 以前作成した7カテゴリの透過WebP
public/assets/icons/                          # 手作業SVGのUIアイコン
preview/index.html                             # HTML静的確認用（Bladeとは別の見本）
```

## 新規Laravelプロジェクトへの仮組み込み

1. バックアップまたはGit差分を確認してから、本フォルダ中の `app` / `resources` / `public/assets` をプロジェクト同名パスへコピー。
2. `routes/web.php` は**既存ファイルを上書きせず**、TOPの `Route::get('/', ...)` を適切に統合。
3. `php artisan serve` を起動し `/` を確認。
4. `.env` の `APP_ENV=local` では実在しない**デザイン確認専用**の掲載カードが表示されます。`local` 以外では架空の掲載を出さず、空状態になります。
5. `GET /listings`、`/login`、`/register` 等の遷移先は**今後実装するURLの想定**です。現段階で404でもTOPテンプレートの故障ではありません。

## 重要な仕様

- `譲る` と `探す` の2モード。カテゴリは**7カテゴリのみ**（散弾銃、ライフル、ハーフライフル、エアライフル、銃身、アクセサリー、無可動銃）。
- 弾薬・火薬・雷管等は取扱対象外。デザインにも商品として掲載しない。
- `所持許可あり` は**自己申告**。GunMatchが許可証を確認したかのような「本人確認済み」「許可証確認済み」は使わない。
- 画像：依頼者と作成してきたGunMatch専用素材を同梱。カテゴリ画像は透明背景のWebP。
- 色はCSS `:root` 変数へ集約。共通カードはBlade anonymous componentへ分割。
- サイト内メッセージ、SMS認証、検索結果、詳細、マイページなどの機能は今後Codexが別工程で実装。
- ロゴと商品画像は試作画像です。実運用時は実掲載の商品画像をDB経由で表示。

## 静的プレビュー

`preview/index.html` はLaravelをインストールせず、PC・SPデザインをブラウザで確認するためのHTML見本です。
画像・CSSのパスは `../public/assets/...` を参照します。BladeやDBとは接続していません。

## 推奨動作環境

PHP 8.3以上を想定。Laravel / MySQLのバージョン確定、さくらレンタルサーバーとの互換性（DocumentRoot、スケジューラ、メール送信、SMSサービス）はCodexの実プロジェクト検証で判断。
