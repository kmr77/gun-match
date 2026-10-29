@extends('layouts.app')

@section('title', '管理者ページ｜GunMatch')

@section('content')
    <section class="page-section">
        <div class="container">
            <x-page-heading
                title="管理者ページ"
                description="管理者権限を持つ会員専用の画面です。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => '管理者ページ']]"
            />
            <x-feedback-message
                type="info"
                title="管理機能は未実装です"
                message="この画面はアクセス制御の確認用です。会員管理などの操作機能はありません。"
            />
        </div>
    </section>
@endsection
