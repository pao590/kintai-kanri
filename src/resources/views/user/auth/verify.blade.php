@extends('layouts.app')

@section('content')
<div class="container">
    <h2>メール認証が必要です</h2>
    <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
    <p>メール認証を完了してください。</p>

    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary">認証メールを再送する</button>
    </form>
</div>
@endsection