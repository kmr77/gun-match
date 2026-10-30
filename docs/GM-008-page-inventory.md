# GM-008 ページ管理表

更新日：2026-10-30

この表は過去のページ制作計画と現行ルートを照合した一覧です。会員登録・認証・プロフィール管理・本人による退会申請、管理者向け会員検索・状態管理・操作履歴・会員詳細を実装済みです。管理者権限の付与・解除はCLI専用です。掲載・取引等の業務機能、通知配信、本人確認は未実装です。「未制作」は別表に記載し、実装済みとして扱いません。PC/SP欄には実際に確認した幅と作業番号を記録します。

## 現在のページ

| ページ名 | URL | Laravelルート名 | デザイン実装状況 | 共通コンポーネント | PC/SP検証 | バックエンド要否 | 残作業 |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GunMatch TOP | `/` | `home` | フロントエンド制作済み（固定表示・仮データ） | site-header, site-footer, section-heading, topic-card, listing-card等 | GM-008: 1440/375確認済み | 未実装（静的表示のため現状不要） | TOPデザインの細部確認 |
| カテゴリー一覧 | `/categories` | `categories.index` | フロントエンド制作済み（7カテゴリーの表示例） | layouts.app, site-header/footer, page-heading, topic-card | GM-009: 1440/375確認済み | 未実装（現状不要） | 機能を追加する場合は別途仕様・法令確認 |
| カテゴリー別案内（7種） | `/categories/{slug}` | `categories.show` | 1つの共通Bladeテンプレートで表示 | layouts.app, site-header/footer, page-heading, topic-card | GM-011: 1440/375代表2種確認 | 未実装（情報表示のみ） | 案内内容の公開前確認 |
| アウトドア用品サンプル一覧 | `/outdoor-samples` | `outdoor-samples.index` | 共通一覧Blade（架空の一般用品サンプル） | layouts.app, page-heading, listing-card | GM-012: 1440/375確認予定 | 未実装（表示確認のみ） | なし |
| アウトドア用品サンプル詳細 | `/outdoor-samples/{slug}` | `outdoor-samples.show` | 共通詳細Blade（識別子で表示を切替） | layouts.app, page-heading, listing-card | GM-012: 1440/375確認予定 | 未実装（表示確認のみ） | なし |
| GunMatchについて | `/about` | `about` | フロントエンド制作済み（静的案内） | layouts.app, site-header/footer, page-heading | GM-008: 1440/375確認済み | 未実装（不要） | 公開内容の最終確認 |
| 初めての方・利用ガイド | `/guide` | `guide` | フロントエンド制作済み（静的案内） | layouts.app, site-header/footer, page-heading | GM-008: 1440/375確認済み | 未実装（不要） | 公開内容の最終確認 |
| 安全・法令について | `/safety` | `safety` | フロントエンド制作済み（静的案内） | layouts.app, site-header/footer, page-heading | GM-008: 1440/375確認済み | 未実装（不要） | 公開前に専門的・法的確認 |
| 禁止事項・禁止品目 | `/prohibited-items` | `prohibited-items` | フロントエンド制作済み（正式一覧未確定） | layouts.app, site-header/footer, page-heading | GM-008: 1440/375確認済み | 未実装（不要） | 正式な範囲を確認して公開 |
| よくある質問 | `/faq` | `faq` | フロントエンド制作済み（静的FAQ） | layouts.app, page-heading, details/summary | GM-008: 1440/375確認済み | 未実装（不要） | 内容の最終確認 |
| 利用規約 | `/terms` | `terms` | デザインのみ仮実装（準備中表示） | layouts.app, page-heading, feedback/notice | GM-008: 1440/375確認済み | 未実装（静的文書のため不要） | 運営情報・提供条件・法的事項を確認して本文作成 |
| プライバシーポリシー | `/privacy` | `privacy` | デザインのみ仮実装（準備中表示） | layouts.app, page-heading, feedback/notice | GM-008: 1440/375確認済み | 未実装（静的文書のため不要） | 実際のデータ取扱・運用を確認して本文作成 |
| お問い合わせ案内 | `/contact` | `contact` | フロントエンド制作済み（案内のみ、フォームなし） | layouts.app, page-heading, feedback/notice | GM-008: 1440/375確認済み | 未実装（受付開始時に要検討） | 運営窓口確定後に案内を更新 |
| ログイン | `/login` | `login` | 実装済み（Laravelセッション認証） | layouts.app, page-heading, form-field, feedback-message | GM-013: Feature検証済み | DB接続・認証基盤 | メール送信設定を本番用に構成 |
| 新規会員登録 | `/register` | `register` | 実装済み（個人会員・銃砲店会員） | layouts.app, page-heading, form-field, feedback-message | GM-013: Feature検証済み | DB接続・認証基盤 | 利用規約・プライバシー公開前確認 |
| パスワード再設定案内 | `/forgot-password` | `password.request` | 実装済み（Laravel Password Broker） | layouts.app, page-heading, form-field, feedback-message | GM-013: Feature検証済み | DBとメール配信設定 | 本番メール配信を設定 |
| マイページTOP | `/mypage` | `mypage` | 実装済み（本人の会員情報表示） | layouts.mypage, page-heading, mypage-nav, feedback-message | GM-013: Feature検証済み | DB・認証基盤 | なし |
| プロフィール編集 | `/mypage/profile` | `mypage.profile` | 実装済み（本人情報を更新） | layouts.mypage, mypage-nav, form-field, feedback-message | GM-013: Feature検証済み | DB・認証基盤 | 追加公開プロフィール項目は未定 |
| 公開プロフィール | `/mypage/profile/public` | `mypage.profile.public` | デザインのみ仮実装（個人情報のない例） | layouts.mypage, mypage-nav, profile-preview-card | GM-010: 1440/375確認済み | 未実装（実プロフィール表示時に必要） | 公開仕様・表示項目を決定 |
| プロフィールプレビュー | `/mypage/profile/preview` | `mypage.profile.preview` | デザインのみ仮実装（保存・公開なし） | layouts.mypage, mypage-nav, profile-preview-card, feedback-message | GM-010: 1440/375確認済み | 未実装（編集データ連携時に必要） | プレビュー対象データを決定 |
| アカウント設定 | `/mypage/settings` | `mypage.settings` | デザインのみ仮実装（変更不可） | layouts.mypage, mypage-nav, status-pill | GM-010: 1440/375確認済み | 未実装（設定変更時に必要） | 提供設定項目を決定 |
| セキュリティ設定 | `/mypage/security` | `mypage.security` | 実装済み（現在のパスワード検証・変更） | layouts.mypage, mypage-nav, form-field | GM-013: Feature検証済み | DB・認証基盤 | なし |
| アカウント管理のご案内 | `/mypage/account` | `mypage.account` | 案内と退会フォームを実装 | layouts.mypage, mypage-nav, form-field, feedback-message | GM-014: 画面表示・削除フロー確認済み | 退会処理を実装（本人のアカウント・セッション・再設定トークン削除） | ログ・バックアップの保持方針を公開前に確定 |
| アカウント削除 | `DELETE /mypage/account` | `mypage.account.destroy` | 実装済み（現在のパスワード再確認あり） | layouts.mypage, form-field, feedback-message | GM-014: Feature検証済み | 実装済み（本人のアカウント・セッション・再設定トークン削除） | ログ・バックアップの保持と消去方針を公開前に確定 |
| 管理者会員管理 | `/admin` | `admin.dashboard` | 会員数集計、検索、会員状態・認証状態表示、操作履歴を実装 | layouts.app, page-heading, feedback-message, admin-table, admin-summary | GM-018: Feature・375px表示確認 | 会員検索・状態変更・監査履歴を実装 | 管理者権限はCLI付与。運用監視・ログ保持方針を確定 |
| 管理者会員状態変更 | `PATCH /admin/members/{user}/status` | `admin.members.status.update` | 管理画面の有効化・停止操作 | layouts.app, admin-table, CSRF form | GM-015: Feature検証済み | 停止時のセッション無効化と監査履歴を実装 | 管理者アカウント運用ルールを確定 |
| 管理者会員詳細 | `/admin/members/{user}` | `admin.members.show` | 共通詳細Bladeで会員情報・対象会員の監査履歴・状態操作を表示 | layouts.app, page-heading, feedback-message, admin-table | GM-017: Feature・画面遷移確認、GM-018: 375px表示確認 | 既存会員・監査履歴テーブルを利用 | 管理者権限はCLI付与。運用監視・ログ保持方針を確定 |
| 通知設定 | `/mypage/notifications` | `mypage.notifications` | デザインのみ仮実装（配信・変更なし） | layouts.mypage, mypage-nav, status-pill | GM-010: 1440/375確認済み | 未実装（通知配信時に必要） | 通知種別・配信方式を決定 |
| お知らせ一覧 | `/mypage/announcements` | `mypage.announcements` | デザインのみ仮実装（固定の仮データ） | layouts.mypage, mypage-nav, announcement-list, feedback-message | GM-010: 1440/375確認済み | 未実装（実告知・既読管理時に必要） | 配信・既読仕様を決定 |
| Not Found | 存在しないURL | Laravel標準404 | フロントエンド制作済み（GunMatchデザイン） | layouts.app, site-header/footer | GM-008: 1440/375確認済み | 未実装（不要） | なし |

## GM-011 共通画面確認追記（2026-10-26）

- 現行22ページと未知URLの404を、PC 1440px・スマートフォン320/375/390/430pxで確認しました。ページ幅・主要要素の画面外はみ出しはありません。
- 全ページの内部リンク、名前付きGETルート、ヘッダー・フッター・マイページメニューを再確認しました。既存22ページのURL・ルート名に変更はありません。
- 会員画面の入力例は送信先のないフォーム要素を表示していたため、GET送信も発生しないグループ表示へ変更しました。実行できないボタンは無効状態とし、ラベル・必須表示・補足説明・入力エラー表示領域は維持しています。
- 会員登録の区分表記は「個人会員」「銃砲店会員」に統一しています。GM-013で登録・ログイン・メール確認・再設定・プロフィール管理を実装しました。
- 404ページ自体は期待どおりHTTP 404です。ブラウザーのリソース404ログは、その404 URLを開いた際の表示と区別して評価してください。

## GM-011 カテゴリー別共通テンプレート

- 7カテゴリーは `config/gunmatch_categories.php` の名前・画像・slugを参照し、`pages.category` の単一テンプレートで表示します。
- TOPとカテゴリー一覧のカードから同じ `categories.show` ルートへ遷移します。`/categories` の既存URLは維持します。
- ページは表示確認用の一般案内のみで、掲載募集・在庫・取得希望・問い合わせ・取引機能はありません。カテゴリーの案内文言は公開前に確認してください。

## GM-012 共通一覧・詳細テンプレート

- `/outdoor-samples` と `/outdoor-samples/{slug}` はそれぞれ共通一覧・詳細Bladeを使用します。サンプル配列は `config/gunmatch_samples.php` に集約しています。
- 対象は一般的なアウトドア用品の架空表示データです。価格、募集、購入・譲渡、問い合わせ、取引連絡、マッチング機能はありません。DB・認証・保存処理も未実装です。

## 以前の計画にある未実装ページ・機能

以下は未制作です。URLとルート名が「未確定」の項目にはリンクを置いていません。公開前の確認が必要な画面・内容は上表の残作業と併せて管理します。

| ページ・機能 | URL/ルート名 | デザイン実装状況 | 共通コンポーネント | PC/SP検証 | バックエンド要否 | 残作業 |
| --- | --- | --- | --- | --- | --- | --- |
| 共通一覧・詳細画面（カテゴリー別案内以外） | URL・ルート名未確定 | 未実装 | listing-card等の既存部品を再利用候補とする | 未実施 | 表示データに応じて必要 | 必要な対象と共通テンプレートを決定 |
| 一覧ページ（一般の一覧画面候補） | URL・ルート名未確定 | 未実装 | なし | 未実施 | 表示データに応じて必要 | 一覧対象・情報範囲を決定 |
| 詳細ページ（一般の詳細画面候補） | URL・ルート名未確定 | 未実装 | なし | 未実施 | 表示データに応じて必要 | 対象・表示項目を決定 |
| 銃器等の出品・取得募集・相手連絡・マッチング | URL・ルート名未確定 | 未実装・今回対象外 | なし | 未実施 | 実装判断前に法令・運用確認が必要 | 仕様・法的確認なしに実装しない |
| 取引問い合わせ・決済・通知等 | URL・ルート名未確定 | 未実装・今回対象外 | なし | 未実施 | 要件確定後に判断 | 今回の画面整理に含めない |

## GM-008で追加した共通管理UI

- `x-mypage-nav`：既存のサイドメニューを全9画面に拡張。小画面では折り返すメニューにし、各アイコン記号に日本語ラベルを併記。
- `x-feedback-message`：案内・成功表示例・入力エラー表示例を共通化。成功・エラー例も処理結果ではなく画面表示サンプルであることを明記。
- 公開プロフィールとプレビューは個人情報を含まない固定の表示サンプル。お知らせ一覧の項目もすべて仮データ。

GM-013以降、認証情報と個人情報はログイン中の本人に限定して扱います。お知らせは引き続き表示確認用で、通知配信・既読管理はありません。

## GM-009で追加した画面・共通部品

- `/categories` は既存の7カテゴリーと画像を再利用する表示専用の一覧です。出品・取得募集・検索・取引へのリンクや個別の品目説明は設けていません。
- カテゴリー定義は `config/gunmatch_categories.php` に集約し、TOPとカテゴリー一覧で共有します。
- アカウント管理画面と通知・プロフィールの表示部品はGM-008で整備済みのため、重複する画面・部品は追加していません。
- 商品一覧・詳細、検索、取引・連絡機能は未実装です。正式な規約・プライバシー文書、禁止品目の確定も公開前の確認事項です。

## GM-010で整理した共通画面

- 会員登録・ログイン・パスワード再設定は `pages.auth-preview` と `x-form-field` を共用し、Laravel標準認証と接続しています。ローカルメールはログ出力設定で、本番配信は未設定です。
- `x-form-field` は入力欄と選択欄、補足説明を共通表示し、補足説明を入力コントロールへ関連付けます。
- 公開情報と会員画面のパンくず・見出しは `x-page-heading` を再利用します。マイページの現在位置は `aria-current="page"` で示し、メニュー名を「アカウント管理のご案内」に統一しました。
- プロフィールプレビューから自身に戻る誤リンクを解消し、編集画面と公開プロフィールへの導線に整理しました。
- 全22件の名前付きページルートを維持しています。会員バックエンドは引き続き未実装です。
