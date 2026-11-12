# Laravel開発基盤

## 実装範囲

Laravel 13の `/` はGunMatchのブランドTOPです。共通Bladeレイアウトとヘッダー・フッター・セクション見出し・カテゴリーカード・理由カード・基本姿勢の匿名コンポーネントを使用します。ページ構成はヒーロー、カテゴリー案内、架空の商品カード表示例、ブランドの基本姿勢、最下部バナーです。TOPのカードはデザイン確認用で、掲載・取得募集・取引機能ではありません。GM-003時点の経緯は [GM-003.md](GM-003.md) を参照してください。

### 未実装機能と素材

- 会員登録・ログイン・メール確認・パスワード再設定・プロフィール管理と、SMS認証コードの生成・照合基盤を実装しています。本人確認書類のアップロード、実SMS配送、銃器・銃身の売買募集、取得希望者のマッチング、取引問い合わせは未実装です。
- 公開画面の見出し・説明はGunMatchのブランドや一般的な安全姿勢を表し、未提供機能を提供済みとは記載しません。開発状況は本書およびREADMEで管理します。
- カードの表示例には固定配列の一般的なアウトドア用品6点を各セクションに表示します。カードは取引リンクを持たず、架空の仮データである旨を明示しています。`public/assets/img/dummy/outdoor/` の6点のSVGはこの表示用に作成したイラストで、外部写真素材は使用していません。
- GM-027でTOP、FAQ、認証案内、マイページのプロフィール・通知表示を実際の提供範囲に合わせました。プロフィール表示は本人用サンプル、通知は準備中です。SMS事業者未選定のため実SMS配送はできません。
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

### MySQLバックアップ・復元（GM-031）

MySQLバックアップは会員の個人情報、パスワードハッシュ、セッション、通報・問い合わせ等を含み得る機密データです。バックアップ自動化は未設定です。作業前に `php artisan migrate:status --database=mysql` と接続先を確認し、`gunmatch_dev_gm023` 以外へ誤接続していないことを確認してください。MAMP MySQL 8.0.40では `mysqldump` の `--single-transaction --quick --hex-blob --default-character-set=utf8mb4 --set-gtid-purged=OFF --no-tablespaces` を使用できます。整合した論理ダンプを作るため、稼働中にDDL変更や大規模更新を並行しないでください。

バックアップ先 `database/backups/` はGit管理対象外です。ディレクトリはアクセス権 `700`、バックアップとMySQLクライアント設定ファイルは `600` にし、接続パスワードをコマンド引数やGit管理ファイルへ書かず、権限を制限したMySQL option fileを `--defaults-extra-file` で指定してください（このオプションはmysqlクライアントの先頭に置きます）。例：

```bash
umask 077
mkdir -p database/backups
chmod 700 database/backups
mysqldump --defaults-extra-file=/secure/local/mysql-client.cnf \
  --single-transaction --quick --hex-blob --default-character-set=utf8mb4 \
  --set-gtid-purged=OFF --no-tablespaces gunmatch_dev_gm023 \
  > database/backups/gunmatch_dev_gm023-YYYYMMDD-HHMMSS.sql
chmod 600 database/backups/gunmatch_dev_gm023-YYYYMMDD-HHMMSS.sql
```

復元確認では、開発DBを復元先にしないでください。重複しない一時DB名を選び、存在しないことを確認してから、開発DBと同じ文字セット・照合順序で一時DBを作成します。ダンプを一時DBへ復元し、テーブル一覧・全件数・`migrations`・外部キー参照整合性を元DBと比較した後、一時DBを削除します。検証用ダンプも確認後に削除し、保持するバックアップは暗号化・アクセス制限・保持期間・復旧責任者を運営で決めて保管してください。別サーバー・本番環境への接続や復元はこの手順の対象外です。

GM-031では未適用だった `2026_11_12_210000_create_terms_consents_table` のみを開発DBへ適用しました。追加テーブルのレコードは0件で、会員数・管理者権限を変更せず、実際の同意履歴も作成していません。`gunmatch_dev_gm023` のバックアップを同じMySQL 8.0.40上の専用一時DBへ復元して件数・履歴・外部キーを照合し、一時データは検証後に削除しました。この確認は手動の単発検証であり、自動バックアップや本番復旧手順の確認を完了したものではありません。

自動テストは通常の `phpunit.xml` によりSQLiteメモリDBを使います。MySQLでテストするときは `phpunit.mysql.xml` を指定してください。この設定は接続先をテスト専用の `gunmatch_test_gm024` に固定し、認証情報はローカル `.env` から読み込みます。各Featureテストの `RefreshDatabase` はこのテスト専用DBを対象にします。MySQL開発DBをテスト設定として指定したり、テスト用設定を使わずに破壊的なテストを実行したりしないでください。

会員認証にはLaravel標準のセッションGuard、パスワードBroker、署名付きメール確認を使用します。登録時は本名、表示名、メールアドレス、電話番号、都道府県、生年月日を検証し、18歳未満を拒否します。通常利用にはメールと電話番号（SMS）の両方の確認が必要です。確認未完了会員はメール確認・プロフィール補完・SMS確認のみ利用でき、通常の会員機能と管理機能は利用できません。電話番号変更時は確認状態をリセットします。所持許可欄は任意の自己申告であり、SMS認証・本人確認審査とは別で、運営による確認状態とは連動しません。本名・メールアドレス・都道府県・生年月日・電話番号・所持許可は公開プロフィールに表示せず、`User` の配列シリアライズからも除外します。ローカル・テストのメール transport は `array` で、本文・確認URLをログへ記録せず実配送もしません。旧設定等でmailer名 `log` が指定された場合も、メール内容をログ出力せずメモリ内transportへ置き換えます。メール確認URLはLaravelの署名付きURLとして有効期限を検証し、パスワード再設定tokenはハッシュ保管・60分期限・一回利用です。本番では認証済みSMTPと必須TLSが設定されない限りアプリ起動を拒否し、SMTP送信失敗はlog mailerへフォールバックせず、利用者へ失敗を表示します。さくらインターネットの契約環境での接続・実メール配送は未確認です。SMS実配送も未完了です。利用規約・プライバシーポリシーと運営情報の確認が完了するまで、公開環境で会員情報を収集しないでください。退会時は会員・関連セッション・再設定token・SMSチャレンジを削除しますが、通報・問い合わせ・監査履歴はユーザー参照をnull化して保持し得ます。保存期間と保持根拠は未確定で、自動削除処理はありません。確定・未確定の整理は [GUNMATCH_MVP_SPEC.md](GUNMATCH_MVP_SPEC.md)、情報棚卸しと公開前レビュー文案は [GM-028-privacy-inventory.md](GM-028-privacy-inventory.md) を参照してください。

`/terms`・`/privacy` は準備中ページです。公開前レビュー用文案は [GM-028-privacy-inventory.md](GM-028-privacy-inventory.md) にあり、正式な法務確認・運営判断済みの規約本文として扱ってはいけません。規約同意基盤は `config/gunmatch_legal.php` の公開状態・版・発効日時・本文セクションが揃って有効となるまで無効です。正式公開後は、規約版の本文を同設定に登録してから公開状態を有効化し、`php artisan config:cache` 後に表示・登録テストを行ってください。公開版の同意チェックが登録時に必須となり、`terms_consents` に明示チェックの版・日時・方法が追加保存されます。プライバシーポリシーの閲覧は同意記録に変換しません。既存会員へ遡及同意を作らず、版履歴は上書きしません。現在は設定が無効なので、登録時の同意取得・記録は行われません。会員退会時はFK cascadeで同意履歴も削除されますが、保持期間や退会後の保存要否は未確定です。

SMSコードは暗号学的乱数で発行し、ハッシュだけを `sms_verification_challenges` に保存します。コードの有効期限は5分、誤入力は5回まで、再送信は60秒間隔・1時間5回までで、再送時は旧コードを失効させます。電話番号そのものはチャレンジ表へ複製せず、比較用ハッシュのみを保存します。実SMS送信事業者は未選定です。既定の `SmsGateway` は送信事業者未設定として失敗を返し、電話確認済みへは変更しません。偽送信GatewayはFeatureテスト内にのみ定義しています。本番接続時は選定・契約した事業者のアダプターを `SmsGateway` として登録し、認証情報を環境変数／シークレット管理へ設定してから、事業者の実送信・失敗・再送制御をステージングで検証してください。コードを手動で確認済みにするCLIや管理者用迂回はありません。既存管理者を移行する際は、事業者とアダプターを本番反映前に準備し、ステージングでの全フロー検証後、既存管理者がプロフィール補完と電話確認を行ってから管理画面を利用する運用を周知してください。事業者未設定の状態で認証ゲートを本番反映しないでください。事業者未設定での実SMS配送は未確認・未完了です。

プロフィール補完とメール確認は認証未完了でも利用できます。その他のマイページ機能はメール・電話確認済みであることを要求します。本番の管理者には管理者権限に加え、アカウント有効状態・メール確認・電話確認が必要です。ローカル開発環境（`APP_ENV=local`）に限り、メール確認済み・有効・管理者権限の既存管理者はSMS事業者の設定中でも管理者画面を操作できます。これは開発作業を継続するためのローカル限定措置で、通常の会員機能には適用されず、本番環境では有効になりません。プロフィール更新は本人の `users` 行だけを対象とし、`admin_activity_logs` へ変更カラム名のみを記録します。変更値・本名・電話番号等は監査詳細に複製しません。取引等の機能は未実装です。

本番SMTPはデプロイ先の環境変数またはシークレット管理で設定します。さくらインターネット公式の[メールソフト設定](https://help.sakura.ad.jp/mail/2114/)では、SMTPサーバー名は契約サーバーの初期ドメイン（`***.sakura.ne.jp`）、送信ポートは587または465、465はSMTPS、SMTP認証を使用し、ユーザー名はメールアドレス全体とされています。初期ドメイン・メールアカウントは契約環境で確認し、推測で入力しないでください。TLS必須のため、公式にSMTPSと明記されている465を使う場合の例は `MAIL_MAILER=smtp`、`MAIL_SCHEME=smtps`、`MAIL_PORT=465`、`MAIL_REQUIRE_TLS=false` です。587を使う場合は、契約環境でSTARTTLSが利用できることを確認し、`MAIL_SCHEME=smtp`、`MAIL_REQUIRE_TLS=true` としてください。公式資料で587のSMTP暗号化方式までは確認できていないため、暗号化接続をステージングで検証するまで本番配送に使わないでください。必須設定はさらに `MAIL_HOST`、フルメールアドレスの `MAIL_USERNAME`、`MAIL_PASSWORD`、実際に管理する送信元 `MAIL_FROM_ADDRESS`、`MAIL_FROM_NAME` です。SMTP認証情報をGit追跡対象のファイル、ソースコード、コミット、公開ログや認証情報入り `MAIL_URL` に保存せず、個別の環境変数／シークレットを使ってください。`MAIL_MAILER` が `array`・`log`・`failover` 等の場合や認証・TLS・送信元設定が不足している場合、本番アプリは起動時にエラーとなります。SMTP送信失敗をlog mailerへ切り替える処理はありません。接続・配送テストは契約済みアカウントと管理下のテスト宛先を用い、ステージングで確認してください。GM-029では公式の一般設定と本番用の安全な設定構造を確認しましたが、対象契約の初期ドメイン・メールアカウント・本番接続、および実メール配送は未確認です。

管理者権限はWebフォームから設定できません。信頼できるサーバー上のCLIで `php artisan gunmatch:admin member@example.com` を実行し、対象メールアドレスと確認プロンプトを確認してから承認します。解除は `php artisan gunmatch:admin member@example.com --revoke` です。停止中・メール未確認会員への付与と、最後の有効な管理者権限の解除は拒否され、操作履歴へ記録されます。CLIを実行できるOS／デプロイ権限は限定し、対象メールアドレスと権限変更を別担当者が確認する運用を推奨します。`is_admin` を会員区分（個人会員・銃砲店会員）から変更・推定してはいけません。

管理者ページ `/admin` では会員総数・有効会員数・停止中会員数、検索可能な会員一覧、管理操作履歴を確認できます。表示名から `/admin/members/{user}` の詳細ページを開き、既存の会員情報と対象会員の操作履歴を確認できます。両ページは認証済み・有効・メール確認済み・電話確認済みの管理者に限定されています。管理操作のデザインと集計は既存データを利用し、新しいテーブルは作成しません。

会員詳細では本人確認・事業者確認の状態を未確認・確認待ち・確認済み・差戻しで管理します。既存会員と新規会員の初期状態は未確認です。会員本人が状態を変更する機能はなく、確認済みへの変更には実際に行った確認方法と確認日を管理者が入力します。審査担当者・審査日時・管理者用メモ・最終確認日時は `users` に記録し、状態変更履歴は既存の `admin_activity_logs` に保存します。監査履歴には状態・区分・記録有無のみを保存し、審査メモや確認方法の本文は複製しません。本人確認書類の画像、許可証画像、証明書番号は収集・保存しません。

`/contact` はログイン不要の一般運営問い合わせフォームです。CSRF、サーバー側入力検証、IP単位の送信回数制限（5回/分）を適用し、問い合わせ本文は `contact_inquiries` に保存します。管理者は `/admin/contact-inquiries` から確認し、未対応・対応中・対応済みを設定できます。対応済みへ変更した日時を保存し、他の状態へ戻すと対応日時を解除します。自動返信・添付・会員間のメッセージ機能はありません。個人情報を含む問い合わせの保持・削除期間は公開前に運用方針として確定してください。

管理者のお知らせ管理は `/admin/announcements` で行い、下書きの作成・編集・削除、公開状態と公開日時を設定できます。公開一覧 `/announcements`、詳細 `/announcements/{announcement}`、ログイン必須の `/mypage/announcements` は公開状態かつ公開日時到来済みの情報のみを新しい順で表示します。お知らせは `announcements` テーブルに保存し、メール一斉配信・プッシュ通知・既読管理は実装していません。

ログイン中の会員は `/mypage/reports/create` から規約違反・不正利用・安全上の懸念などを運営へ通報できます。送信者IDと受付日時をシステム側で記録し、入力検証・CSRF対策・5回/分の送信制限を適用します。通報内容は `abuse_reports` に保存され、一般会員向けの一覧・詳細画面はありません。管理者は `/admin/reports` から通報者・通報内容を確認し、対応状態と管理メモを記録できます。担当管理者・対応日時を保存し、対応記録は既存の `admin_activity_logs` に `report.updated` として監査記録します。通報情報の保持・削除期間は公開前に運用方針として確定してください。

管理者会員の検索は、表示名・メールアドレスに対する `LOWER(...) LIKE ?` のバインドクエリを使用します。会員状態、操作履歴、問い合わせ、お知らせ、通報、会員確認審査、プロフィール項目、SMS確認チャレンジのマイグレーションはLaravel Schema Builderの一般的な型・外部キー・インデックスを使い、SQLite固有SQLは追加していません。MAMP MySQL 8.0.40では開発DBと専用MySQLテストDBに全13マイグレーションが適用され、Laravel全74テスト（1,723 assertions）が双方で成功しました。MySQLの `utf8mb4_unicode_ci` とSQLiteでは文字列比較・照合順序の挙動が異なり得るため、メールアドレスはアプリケーションで小文字化し、検索では `LOWER` を使用しています。本番MySQLへの接続、実データ移行、照合順序・バックアップ復元試験は未実施です。本番採用時は本番同等のステージングで検証してください。

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
| 新規会員登録 | `/register` | `register` | 本名・表示名・メール・電話番号・都道府県・生年月日（18歳以上）、任意の所持許可自己申告、会員区分 |
| 電話番号確認 | `/phone/verify` | `phone-verification.notice` | メール確認済み会員のSMSコード送信・検証。事業者未設定のため実配送なし |
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

GM-025では `users` に `legal_name`、`prefecture`、`birth_date`、`phone`、`phone_verified_at`、`has_firearm_permit` を追加しました。既存会員の列値はNULLのまま維持し、プロフィールから補完できます。GM-026では認証コードのハッシュ・期限・送信／入力回数を記録する `sms_verification_challenges` を追加しました。会員行・管理者権限は保持しています。電話番号は登録とプロフィール更新で必須ですが、既存会員が未登録情報を補完できるようDB列はNULL可能です。所持許可の自己申告は任意です。SMS事業者契約がないため、実SMS配送は未実装で、電話番号確認は完了できません。

会員登録後はメールを先に確認し、次に電話番号をSMSで確認します。確認コードは6桁・5分有効・一回限りで、DBにはコードそのものではなくハッシュを保存します。60秒の再送間隔、1時間あたり5回の送信上限、コードごと5回の誤入力上限を適用します。SMS事業者未設定時は送信処理を明示的に失敗させ、確認状態を変更しません。外部SMSの送信失敗・実配送は未確認です。

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
