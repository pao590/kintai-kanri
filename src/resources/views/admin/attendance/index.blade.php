@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance-admin container py-4">

    {{-- タイトル --}}
    <h2 class="attendance-admin__title text-center mb-4">
        {{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }} の勤怠
    </h2>

    {{-- 日付ナビゲーション --}}
    <div class="attendance-admin__date-nav d-flex justify-content-center align-items-center mb-4 gap-3 flex-wrap">
        <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d')]) }}"
            class="attendance-admin__date-btn btn btn-outline-primary btn-sm">
            ← 前日
        </a>

        <div class="attendance-admin__date-picker">
            <input
                type="date"
                class="attendance-admin__date-input form-control form-control-sm"
                value="{{ $date }}"
                onchange="location.href='{{ route('admin.attendance.list') }}?date=' + this.value">
        </div>

        <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d')]) }}"
            class="attendance-admin__date-btn btn btn-outline-primary btn-sm">
            翌日 →
        </a>
    </div>

    {{-- 勤怠テーブル --}}
    <div class="attendance-admin__table-wrapper table-responsive">
        <table class="attendance-admin__table table table-bordered table-hover align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th class="attendance-admin__th">名前</th>
                    <th class="attendance-admin__th">出勤</th>
                    <th class="attendance-admin__th">退勤</th>
                    <th class="attendance-admin__th">休憩</th>
                    <th class="attendance-admin__th">合計</th>
                    <th class="attendance-admin__th">詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                @php
                $restMinutes = 0;
                foreach ($attendance->rests as $rest) {
                if ($rest->rest_start && $rest->rest_end) {
                $restMinutes += \Carbon\Carbon::parse($rest->rest_end)
                ->diffInMinutes(\Carbon\Carbon::parse($rest->rest_start));
                }
                }
                @endphp
                <tr class="attendance-admin__tr">
                    <td class="attendance-admin__td">{{ $attendance->user->name }}</td>
                    <td class="attendance-admin__td">
                        {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}
                    </td>
                    <td class="attendance-admin__td">
                        {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}
                    </td>
                    <td class="attendance-admin__td">
                        {{ $restMinutes ? sprintf('%d:%02d', floor($restMinutes / 60), $restMinutes % 60) : '' }}
                    </td>
                    <td class="attendance-admin__td">
                        @if ($attendance->clock_in && $attendance->clock_out)
                        @php
                        $workMinutes = $attendance->clock_out->diffInMinutes($attendance->clock_in);
                        $totalMinutes = $workMinutes - $restMinutes;
                        @endphp
                        {{ sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60) }}
                        @endif
                    </td>
                    <td class="attendance-admin__td">
                        <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}"
                            class="attendance-admin__detail-btn btn btn-sm btn-outline-secondary">
                            詳細
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="attendance-admin__no-data py-3 text-muted" colspan="6">
                        この日の勤怠データはありません。
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection