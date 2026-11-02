@extends('layouts.app')

@section('title', '運営への通報｜GunMatch')

@section('content')
    <section class="content-page">
        <div class="container content-page__inner">
            <x-page-heading
                title="運営への通報"
                description="規約違反や不正利用などを運営へお知らせください。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'マイページ', 'route' => 'mypage'], ['label' => '運営への通報']]"
            />
            @if (session('status'))
                <x-feedback-message type="success" title="通報を受け付けました" :message="session('status')" />
            @else
                <p class="page-notice" role="note">通報内容は運営のみが確認し、一般公開されません。会員間の取引連絡には使用できません。</p>
                <form method="POST" action="{{ route('mypage.reports.store') }}" class="content-sections">
                    @csrf
                    @if ($errors->any())
                        <x-feedback-message type="error" title="入力内容をご確認ください" :message="$errors->first()" />
                    @endif
                    <div class="form-field">
                        <label for="report-type">通報の種類<span aria-hidden="true"> *</span></label>
                        <select id="report-type" name="type" required aria-invalid="{{ $errors->has('type') ? 'true' : 'false' }}">
                            <option value="">選択してください</option>
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('type'))
                            <p class="form-field__error">{{ $errors->first('type') }}</p>
                        @endif
                    </div>
                    <x-form-field
                        name="target_information"
                        label="通報対象の情報"
                        description="ページURLや会員名など、対象を確認できる情報を入力してください。"
                        :required="true"
                        :error="$errors->first('target_information')"
                    />
                    <div class="form-field">
                        <label for="details">通報理由・詳細<span aria-hidden="true"> *</span></label>
                        <textarea id="details" name="details" rows="8" maxlength="10000" required aria-invalid="{{ $errors->has('details') ? 'true' : 'false' }}" @if ($errors->has('details')) aria-describedby="details-error" @endif>{{ old('details') }}</textarea>
                        @if ($errors->has('details'))
                            <p class="form-field__error" id="details-error">{{ $errors->first('details') }}</p>
                        @endif
                    </div>
                    <button class="btn btn--primary" type="submit">通報を送信</button>
                </form>
            @endif
        </div>
    </section>
@endsection
