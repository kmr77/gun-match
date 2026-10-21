# Laravel開発基盤

## 実装範囲

Laravel 13の標準アプリケーションを追加しています。現在の `/` はLaravel標準のWelcomeページです。GunMatchのTOPデザインの実装完了を意味しません。

既存のREADME.md、CODEX_作業指示.md、index.html、gunmatch.css、PNG素材、AGENTS.mdは上書きしていません。元のHTMLはLaravelのルートから配信していません。

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

フロントエンドのnpm依存関係は未導入です。現在の標準Welcomeページは、Viteのビルド成果物がない場合にLaravel標準のフォールバック表示を使用します。

## Git管理

`.env`、認証ファイル、vendor、node_modules、ログ、キャッシュ、SQLite実体はコミット対象から除外します。元から存在する資料と素材はAGENTS.mdを除き、今回のLaravel基盤のコミットに含めません。

## 今回の検証結果（2026-10-21）

- PHP 8.4.1でLaravel Framework 13.38.0が起動。
- `/` と `/up` のHTTP 200応答を確認。
- ルーティング一覧の取得、Bladeのコンパイルに成功。
- 標準テスト2件、アサーション2件に成功。
- アプリ・設定・ルート・標準テスト等のPHP 25ファイルで構文エラーなし。
- 作業前から存在した全ファイルのSHA-256が作業後と一致。
- `.env`、vendor、ログ、SQLite実体がGit除外対象であることを確認。
- PC・スマートフォンのブラウザ表示、参考画像との比較は未実施。
- Composer 2.5.8はPHP 8.4環境で非推奨警告を出しましたが、依存関係の導入は終了コード0で完了。グローバルComposerは変更していません。

## 参考資料

- [Laravel 13 インストール](https://laravel.com/framework/docs/13.x/installation)
- [Laravel 13 サーバー要件と公開ディレクトリ](https://laravel.com/framework/docs/13.x/deployment)
