@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">勤怠詳細</h2>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card p-4">
        <div class="mb-3">
            <strong>名前：</strong> {{ $attendance->user->name }}
        </div>
        <div class="mb-3">
            <strong>日付：</strong> {{ $attendance->clock_in ? $attendance->clock_in->format('Y年n月j日') : '' }}
        </div>

        <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">出勤・退勤：</label><br>
                <input type="time" name="clock_in" value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
                ～
                <input type="time" name="clock_out" value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">
                @error('clock_out')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            @foreach($attendance->rests as $i => $rest)
            <div class="mb-3">
                <label class="form-label">休憩{{ $i + 1 }}：</label><br>
                <input type="time" name="rests[{{ $i }}][start]" value="{{ old("rests.$i.start", optional($rest->rest_start)->format('H:i')) }}">
                ～
                <input type="time" name="rests[{{ $i }}][end]" value="{{ old("rests.$i.end", optional($rest->rest_end)->format('H:i')) }}">
            </div>
            @endforeach


            <div class="mb-3">
                <label class="form-label">休憩{{ $attendance->rests->count() + 1 }}：</label><br>
                <input type="time" name="rests[{{ $attendance->rests->count() }}][start]">
                ～
                <input type="time" name="rests[{{ $attendance->rests->count() }}][end]">
            </div>

            <div class="mb-3 row">
                <label class="col-sm-2 col-form-label fw-bold">備考</label>
                <div class="col-sm-10">
                    {{-- 修正: JSONならデコードして必要なキーを表示 --}}
                    <textarea class="form-control" rows="3" readonly>{{ isset($correction->correction_content) ? json_decode($correction->correction_content, true)['reason'] ?? '' : '' }}</textarea>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">修正を保存</button>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">戻る</a>
            </div>
        </form>
    </div>
</div>
@endsection