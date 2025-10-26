@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">勤怠詳細</h2>

    @if(!$attendance)
    <div class="alert alert-info">
        勤怠情報がありません。
    </div>
    @else
    <div class="card p-4">
        <div class="mb-2">名前：{{ Auth::user()->name }}</div>
        <div class="mb-2">日付：{{ $attendance->clock_in ? $attendance->clock_in->format('Y年n月j日') : '' }}</div>

        <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
            @csrf

            <div class="mb-2">出勤・退勤：
                <input type="time" name="clock_in"
                    value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}"
                    {{ $pendingRequest ? 'disabled' : '' }}>
                ～
                <input type="time" name="clock_out"
                    value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}"
                    {{ $pendingRequest ? 'disabled' : '' }}>
            </div>

            @forelse($attendance->rests as $i => $rest)
            <div class="mb-2">休憩{{ $i+1 }} ：
                <input type="time" name="rests[{{ $i }}][start]"
                    value="{{ old('rests.'.$i.'.start', optional($rest->rest_start)->format('H:i')) }}"
                    {{ $pendingRequest ? 'disabled' : '' }}>
                ～
                <input type="time" name="rests[{{ $i }}][end]"
                    value="{{ old('rests.'.$i.'.end', optional($rest->rest_end)->format('H:i')) }}"
                    {{ $pendingRequest ? 'disabled' : '' }}>
            </div>
            @empty
            <div class="mb-2">休憩：
                <input type="time" name="rests[0][start]" {{ $pendingRequest ? 'disabled' : '' }}>
                ～
                <input type="time" name="rests[0][end]" {{ $pendingRequest ? 'disabled' : '' }}>
            </div>
            @endforelse

            @if(!$pendingRequest)
            <div class="mb-2">休憩{{ $attendance->rests->count() + 1 }}：
                <input type="time" name="rests[{{ $attendance->rests->count() }}][start]">
                ～
                <input type="time" name="rests[{{ $attendance->rests->count() }}][end]">
            </div>
            @endif

            <div class="mb-2">備考：
                <textarea name="reason" class="form-control" {{ $pendingRequest ? 'disabled' : '' }}>{{ old('reason', $latestRequest->reason ?? '') }}</textarea>
            </div>

            <div class="mt-3">
                @if(!$pendingRequest)
                <button type="submit" class="btn btn-primary">修正</button>
                @else
                <p class="text-danger">*承認待ちのため修正はできません。</p>
                @endif
            </div>
        </form>
    </div>
    @endif
</div>
@endsection