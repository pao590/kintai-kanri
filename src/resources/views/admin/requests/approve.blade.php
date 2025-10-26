@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-white">修正申請承認</h2>

    <div class="card p-4 shadow-sm">
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label fw-bold">名前</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $requestData->user->name }}</p>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label fw-bold">日付</label>
            <div class="col-sm-10">
                @php
                $date = \Carbon\Carbon::parse($requestData->attendance->clock_in);
                @endphp
                <p class="form-control-plaintext">{{ $date->format('Y年 n月 j日') }}</p>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label fw-bold">出勤・退勤</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">
                    {{ \Carbon\Carbon::parse($requestData->attendance->clock_in)->format('H:i') }}
                    〜
                    {{ $requestData->attendance->clock_out ? \Carbon\Carbon::parse($requestData->attendance->clock_out)->format('H:i') : '-' }}
                </p>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label fw-bold">休憩</label>
            <div class="col-sm-10">
                @if($requestData->attendance->rests->count() >= 1)
                @php $rest = $requestData->attendance->rests[0]; @endphp
                <p class="form-control-plaintext">
                    {{ \Carbon\Carbon::parse($rest->rest_start)->format('H:i') }}
                    〜
                    {{ $rest->rest_end ? \Carbon\Carbon::parse($rest->rest_end)->format('H:i') : '-' }}
                </p>
                @else
                <p class="form-control-plaintext">-</p>
                @endif
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label fw-bold">休憩2</label>
            <div class="col-sm-10">
                @if($requestData->attendance->rests->count() >= 2)
                @php $rest2 = $requestData->attendance->rests[1]; @endphp
                <p class="form-control-plaintext">
                    {{ \Carbon\Carbon::parse($rest2->rest_start)->format('H:i') }}
                    〜
                    {{ $rest2->rest_end ? \Carbon\Carbon::parse($rest2->rest_end)->format('H:i') : '-' }}
                </p>
                @else
                <p class="form-control-plaintext"></p>
                @endif
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label fw-bold">修正内容</label>
            <div class="col-sm-10">
                <p class="form-control-plaintext">{{ $requestData->correction_content ?? '-' }}</p>
            </div>
        </div>

        
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label fw-bold">備考</label>
            <div class="col-sm-10">
                <textarea class="form-control" rows="3" readonly>{{ $requestData->reason }}</textarea>
            </div>
        </div>

        <div class="text-end mt-4">
            @if($requestData->status === 'approved')
            <button type="button" class="btn btn-secondary px-4" disabled>承認済み</button>
            @else
            <form action="{{ route('admin.requests.approve', $requestData->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success px-4">承認</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection