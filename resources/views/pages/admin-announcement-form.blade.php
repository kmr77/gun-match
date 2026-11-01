@extends('layouts.app')

@section('title', ($announcement->exists ? 'お知らせ編集' : 'お知らせ作成').'｜GunMatch')

@section('content')
    <section class="page-section">
        <div class="container">
            <x-page-heading
                :title="$announcement->exists ? 'お知らせ編集' : 'お知らせ作成'"
                description="タイトル・本文・公開状態と公開日時を設定します。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'お知らせ管理', 'route' => 'admin.announcements.index'], ['label' => $announcement->exists ? 'お知らせ編集' : 'お知らせ作成']]"
            />
            @if (session('status'))
                <x-feedback-message type="success" title="保存しました" :message="session('status')" />
            @endif
            @if ($errors->any())
                <x-feedback-message type="error" title="入力内容をご確認ください" message="各項目のエラーを修正して、もう一度保存してください。" />
            @endif
            <section class="admin-panel">
                <form method="POST" action="{{ $announcement->exists ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" class="content-sections">
                    @csrf
                    @if ($announcement->exists)
                        @method('PUT')
                    @endif
                    <x-form-field name="title" label="タイトル" :required="true" :value="$announcement->title" :error="$errors->first('title')" />
                    <div class="form-field">
                        <label for="body">本文<span aria-hidden="true"> *</span></label>
                        <textarea id="body" name="body" rows="12" maxlength="20000" required @if ($errors->has('body')) aria-invalid="true" aria-describedby="body-error" @endif>{{ old('body', $announcement->body) }}</textarea>
                        @if ($errors->has('body'))
                            <p class="form-field__error" id="body-error">{{ $errors->first('body') }}</p>
                        @endif
                    </div>
                    <x-form-field
                        name="status"
                        label="公開状態"
                        type="select"
                        :options="['draft' => '下書き', 'published' => '公開']"
                        :value="$announcement->status ?: 'draft'"
                        :required="true"
                        :error="$errors->first('status')"
                    />
                    <x-form-field
                        name="published_at"
                        label="公開日時"
                        type="datetime-local"
                        :value="$announcement->published_at?->format('Y-m-d\TH:i')"
                        description="公開日時が未来の場合、その日時になるまで利用者側には表示されません。公開状態で日時を空欄にした場合は現在時刻で公開します。"
                        :error="$errors->first('published_at')"
                    />
                    <button class="btn btn--primary" type="submit">保存する</button>
                </form>
            </section>
            <p><a class="btn btn--outline" href="{{ route('admin.announcements.index') }}">お知らせ一覧へ戻る</a></p>
        </div>
    </section>
@endsection
