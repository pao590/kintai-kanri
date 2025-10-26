@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">スタッフ一覧</h2>

    <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <a href="{{ route('admin.attendance.staff', $user->id) }}" class="btn btn-outline-primary btn-sm">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection