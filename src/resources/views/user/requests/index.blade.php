@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">申請一覧</h2>

    <ul class="nav nav-tabs" id="requestTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                承認待ち
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                承認済み
            </button>
        </li>
    </ul>

    <div class="tab-content mt-3" id="requestTabsContent">
        <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
            @if($pending->isEmpty())
            <p>現在、承認待ちの申請はありません。</p>
            @else
            <table class="table table-bordered">
                <thead>
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
                    @foreach($pending as $request)
                    <tr>
                        <td>{{ $request->status }}</td>
                        <td>{{ $request->user->name ?? '-' }}</td>
                        <td>{{ optional($request->attendance->clock_in)->format('Y-m-d') ?? '-' }}</td>
                        <td>{{ $request->reason }}</td>
                        <td>{{ $request->created_at->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('attendance.show', $request->attendance_id) }}" class="btn btn-sm btn-info">詳細</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
            @if($approved->isEmpty())
            <p>現在、承認済みの申請はありません。</p>
            @else
            <table class="table table-bordered">
                <thead>
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
                    @foreach($approved as $request)
                    <tr>
                        <td>{{ $request->status }}</td>
                        <td>{{ $request->user->name ?? '-' }}</td>
                        <td>{{ optional($request->attendance->clock_in)->format('Y-m-d') ?? '-' }}</td>
                        <td>{{ $request->reason }}</td>
                        <td>{{ $request->created_at->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('attendance.show', $request->attendance_id) }}" class="btn btn-sm btn-info">詳細</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection
