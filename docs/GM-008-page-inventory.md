# GM-008 ページ管理表

更新日：2026-10-26

この表は過去のページ制作計画と現行ルートを照合した一覧です。「フロントエンド制作済み」は表示UIのみを指し、データ連携や業務機能の完成を意味しません。「デザインのみ仮実装」は静的な画面見本です。バックエンドは会員認証・登録・保存・通知配信を含めすべて未実装です。「未制作」は別表に記載し、実装済みとして扱いません。PC/SP欄には実際に確認した幅と作業番号を記録します。全画面スクリーンショットを保存したページは別途指定します。

## 現在のページ

| ページ名 | URL | Laravelルート名 | デザイン実装状況 | 共通コンポーネント | PC/SP検証 | バックエンド要否 | 残作業 |
| --- | --- | --- | --- | --- | --- | --- | --- |
| GunMatch TOP | `/` | `home` | フロントエンド制作済み（固定表示・仮データ） | site-header, site-footer, section-heading, topic-card, listing-card等 | GM-008: 1440/375確認済み | 未実装（静的表示のため現状不要） | TOPデザインの細部確認 |
| カテゴリー一覧 | `/categories` | `categories.index` | フロントエンド制作済み（7カテゴリーの表示例） | layouts.app, site-header/footer, page-heading, topic-card | GM-009: 1440/375確認済み | 未実装（現状不要） | 機能を追加する場合は別途仕様・法令確認 |
| GunMatchについて | `/about` | `about` | フロントエンド制作済み（静的案内） | layouts.app, site-header/footer, page-heading | GM-008: 1440/375確認済み | 未実装（不要） | 公開内容の最終確認 |
| 初めての方・利用ガイド | `/guide` | `guide` | フロントエンド制作済み（静的案内） | layouts.app, site-header/footer, page-heading | GM-008: 1440/375確認済み | 未実装（不要） | 公開内容の最終確認 |
| 安全・法令について | `/safety` | `safety` | フロントエンド制作済み（静的案内） | layouts.app, site-header/footer, page-heading | GM-008: 1440/375確認済み | 未実装（不要） | 公開前に専門的・法的確認 |
| 禁止事項・禁止品目 | `/prohibited-items` | `prohibited-items` | フロントエンド制作済み（正式一覧未確定） | layouts.app, site-header/footer, page-heading | GM-008: 1440/375確認済み | 未実装（不要） | 正式な範囲を確認して公開 |
| よくある質問 | `/faq` | `faq` | フロントエンド制作済み（静的FAQ） | layouts.app, page-heading, details/summary | GM-008: 1440/375確認済み | 未実装（不要） | 内容の最終確認 |
| 利用規約 | `/terms` | `terms` | デザインのみ仮実装（準備中表示） | layouts.app, page-heading, feedback/notice | GM-008: 1440/375確認済み | 未実装（静的文書のため不要） | 運営情報・提供条件・法的事項を確認して本文作成 |
| プライバシーポリシー | `/privacy` | `privacy` | デザインのみ仮実装（準備中表示） | layouts.app, page-heading, feedback/notice | GM-008: 1440/375確認済み | 未実装（静的文書のため不要） | 実際のデータ取扱・運用を確認して本文作成 |
| お問い合わせ案内 | `/contact` | `contact` | フロントエンド制作済み（案内のみ、フォームなし） | layouts.app, page-heading, feedback/notice | GM-008: 1440/375確認済み | 未実装（受付開始時に要検討） | 運営窓口確定後に案内を更新 |
| ログイン | `/login` | `login` | デザインのみ仮実装（送信不可） | layouts.app, page-heading, form-field, feedback-message | GM-010: 1440/375確認済み | 未実装（認証・保存時に必要） | 認証方式決定後に実装 |
| 新規会員登録 | `/register` | `register` | デザインのみ仮実装（個人・事業者共通UI） | layouts.app, page-heading, form-field, feedback-message | GM-010: 1440/375確認済み | 未実装（登録・認証・保存時に必要） | 登録・同意・認証仕様を決定 |
| パスワード再設定案内 | `/forgot-password` | `password.request` | デザインのみ仮実装（送信不可） | layouts.app, page-heading, form-field, feedback-message | GM-010: 1440/375確認済み | 未実装（認証・メール送信時に必要） | 認証・メール送信仕様を決定 |
| マイページTOP | `/mypage` | `mypage` | デザインのみ仮実装（会員データなし） | layouts.mypage, page-heading, mypage-nav, feedback-message | GM-010: 1440/375確認済み | 未実装（会員データ連携時に必要） | 認証・表示対象を設計 |
| プロフィール編集 | `/mypage/profile` | `mypage.profile` | デザインのみ仮実装（保存不可） | layouts.mypage, mypage-nav, form-field, feedback-message | GM-010: 1440/375確認済み | 未実装（保存時に必要） | 保存・公開項目を別途設計 |
| 公開プロフィール | `/mypage/profile/public` | `mypage.profile.public` | デザインのみ仮実装（個人情報のない例） | layouts.mypage, mypage-nav, profile-preview-card | GM-010: 1440/375確認済み | 未実装（実プロフィール表示時に必要） | 公開仕様・表示項目を決定 |
| プロフィールプレビュー | `/mypage/profile/preview` | `mypage.profile.preview` | デザインのみ仮実装（保存・公開なし） | layouts.mypage, mypage-nav, profile-preview-card, feedback-message | GM-010: 1440/375確認済み | 未実装（編集データ連携時に必要） | プレビュー対象データを決定 |
| アカウント設定 | `/mypage/settings` | `mypage.settings` | デザインのみ仮実装（変更不可） | layouts.mypage, mypage-nav, status-pill | GM-010: 1440/375確認済み | 未実装（設定変更時に必要） | 提供設定項目を決定 |
| セキュリティ設定 | `/mypage/security` | `mypage.security` | デザインのみ仮実装（認証機能なし） | layouts.mypage, mypage-nav, status-pill, feedback-message | GM-010: 1440/375確認済み | 未実装（認証導入時に必要） | セキュリティ方式を決定 |
| アカウント管理のご案内 | `/mypage/account` | `mypage.account` | フロントエンド制作済み（静的案内） | layouts.mypage, mypage-nav, feedback-message | GM-010: 1440/375確認済み | 未実装（現状不要） | 運用確定後に案内を更新 |
| 通知設定 | `/mypage/notifications` | `mypage.notifications` | デザインのみ仮実装（配信・変更なし） | layouts.mypage, mypage-nav, status-pill | GM-010: 1440/375確認済み | 未実装（通知配信時に必要） | 通知種別・配信方式を決定 |
| お知らせ一覧 | `/mypage/announcements` | `mypage.announcements` | デザインのみ仮実装（固定の仮データ） | layouts.mypage, mypage-nav, announcement-list, feedback-message | GM-010: 1440/375確認済み | 未実装（実告知・既読管理時に必要） | 配信・既読仕様を決定 |
| Not Found | 存在しないURL | Laravel標準404 | フロントエンド制作済み（GunMatchデザイン） | layouts.app, site-header/footer | GM-008: 1440/375確認済み | 未実装（不要） | なし |

## 以前の計画にある未実装ページ・機能

以下は未制作です。URLとルート名が「未確定」の項目にはリンクを置いていません。公開前の確認が必要な画面・内容は上表の残作業と併せて管理します。

| ページ・機能 | URL/ルート名 | デザイン実装状況 | 共通コンポーネント | PC/SP検証 | バックエンド要否 | 残作業 |
| --- | --- | --- | --- | --- | --- | --- |
| カテゴリー別案内ページ | 過去の候補：`/categories/{category}` / `categories.show`（未登録） | 未実装 | なし | 未実施 | 案内のみなら不要 | ページが必要か、内容とURLを決定 |
| 一覧ページ（一般の一覧画面候補） | URL・ルート名未確定 | 未実装 | なし | 未実施 | 表示データに応じて必要 | 一覧対象・情報範囲を決定 |
| 詳細ページ（一般の詳細画面候補） | URL・ルート名未確定 | 未実装 | なし | 未実施 | 表示データに応じて必要 | 対象・表示項目を決定 |
| 銃器等の出品・取得募集・相手連絡・マッチング | URL・ルート名未確定 | 未実装・今回対象外 | なし | 未実施 | 実装判断前に法令・運用確認が必要 | 仕様・法的確認なしに実装しない |
| 取引問い合わせ・決済・通知等 | URL・ルート名未確定 | 未実装・今回対象外 | なし | 未実施 | 要件確定後に判断 | 今回の画面整理に含めない |

## GM-008で追加した共通管理UI

- `x-mypage-nav`：既存のサイドメニューを全9画面に拡張。小画面では折り返すメニューにし、各アイコン記号に日本語ラベルを併記。
- `x-feedback-message`：案内・成功表示例・入力エラー表示例を共通化。成功・エラー例も処理結果ではなく画面表示サンプルであることを明記。
- 公開プロフィールとプレビューは個人情報を含まない固定の表示サンプル。お知らせ一覧の項目もすべて仮データ。

認証情報、個人情報、実ユーザーデータ、実際のお知らせは使用していません。これらの画面は表示確認用で、保存・認証・配信処理はありません。

## GM-009で追加した画面・共通部品

- `/categories` は既存の7カテゴリーと画像を再利用する表示専用の一覧です。出品・取得募集・検索・取引へのリンクや個別の品目説明は設けていません。
- カテゴリー定義は `config/gunmatch_categories.php` に集約し、TOPとカテゴリー一覧で共有します。
- アカウント管理画面と通知・プロフィールの表示部品はGM-008で整備済みのため、重複する画面・部品は追加していません。
- 商品一覧・詳細、検索、取引・連絡機能は未実装です。正式な規約・プライバシー文書、禁止品目の確定も公開前の確認事項です。

## GM-010で整理した共通画面

- 会員登録・ログイン・パスワード再設定は `pages.auth-preview` と `x-form-field` を共用する静的UIです。個人会員・銃砲店会員の区分表示を加えていますが、選択・登録・認証・メール送信・保存は未実装です。
- `x-form-field` は入力欄と選択欄、補足説明を共通表示し、補足説明を入力コントロールへ関連付けます。
- 公開情報と会員画面のパンくず・見出しは `x-page-heading` を再利用します。マイページの現在位置は `aria-current="page"` で示し、メニュー名を「アカウント管理のご案内」に統一しました。
- プロフィールプレビューから自身に戻る誤リンクを解消し、編集画面と公開プロフィールへの導線に整理しました。
- 全22件の名前付きページルートを維持しています。会員バックエンドは引き続き未実装です。
