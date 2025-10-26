@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">申請一覧</h2>

    {{-- タブ切り替え --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}"
                href="{{ route('admin.requests.index', ['status' => 'pending']) }}">
                承認待ち
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'approved' ? 'active' : '' }}"
                href="{{ route('admin.requests.index', ['status' => 'approved']) }}">
                承認済み
            </a>
        </li>
    </ul>

    <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $req)
            <tr>
                <td>
                    @if ($req->status === 'pending')
                    <span class="badge bg-warning text-dark">承認待ち</span>
                    @elseif ($req->status === 'approved')
                    <span class="badge bg-success">承認済み</span>
                    @endif
                </td>
                <td>{{ $req->user->name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($req->target_date)->format('Y/m/d') }}</td>
                <td>{{ $req->reason }}</td>
                <td>{{ \Carbon\Carbon::parse($req->created_at)->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('admin.requests.show', $req->id) }}"
                        class="btn btn-outline-primary btn-sm">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-muted">申請はありません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection