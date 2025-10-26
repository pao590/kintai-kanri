@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">勤怠一覧</h2>

    <div class="d-flex justify-content-center align-items-center mb-4">
        <a href="{{ route('attendance.list', ['month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}" class="btn btn-link">&larr; 前月</a>

        <div class="mx-3">
            <i class="fa fa-calendar"></i>
            {{ \Carbon\Carbon::parse($month)->format('Y/m') }}
        </div>

        <a href="{{ route('attendance.list', ['month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}" class="btn btn-link">翌月 &rarr;</a>
    </div>

    <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @php
            $start = \Carbon\Carbon::parse($month)->startOfMonth();
            $end = \Carbon\Carbon::parse($month)->endOfMonth();
            $dates = \Carbon\CarbonPeriod::create($start, $end);

            $attendanceMap = $attendances->mapWithKeys(function ($attendance) {
            if ($attendance->clock_in) {
            $dateKey = \Carbon\Carbon::parse($attendance->clock_in)->toDateString();
            return [$dateKey => $attendance];
            }
            return [];
            });
            @endphp

            @foreach ($dates as $date)
            @php
            $day = $attendanceMap[$date->toDateString()] ?? null;
            @endphp
            <tr>
                <td>{{ $date->translatedFormat('m/d (ddd)') }}</td>
                <td>{{ $day && $day->clock_in ? \Carbon\Carbon::parse($day->clock_in)->format('H:i') : '' }}</td>
                <td>{{ $day && $day->clock_out ? \Carbon\Carbon::parse($day->clock_out)->format('H:i') : '' }}</td>
                <td>
                    @if ($day && $day->rests->count())
                    @php
                    $restMinutes = 0;
                    foreach ($day->rests as $rest) {
                    if ($rest->rest_start && $rest->rest_end) {
                    $restMinutes += \Carbon\Carbon::parse($rest->rest_end)
                    ->diffInMinutes(\Carbon\Carbon::parse($rest->rest_start));
                    }
                    }
                    @endphp
                    {{ sprintf('%d:%02d', floor($restMinutes / 60), $restMinutes % 60) }}
                    @endif
                </td>
                <td>
                    @if ($day && $day->clock_in && $day->clock_out)
                    @php
                    $workMinutes = \Carbon\Carbon::parse($day->clock_out)
                    ->diffInMinutes(\Carbon\Carbon::parse($day->clock_in));
                    $restMinutes = 0;
                    foreach ($day->rests as $rest) {
                    if ($rest->rest_start && $rest->rest_end) {
                    $restMinutes += \Carbon\Carbon::parse($rest->rest_end)
                    ->diffInMinutes(\Carbon\Carbon::parse($rest->rest_start));
                    }
                    }
                    $totalMinutes = $workMinutes - $restMinutes;
                    @endphp
                    {{ sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60) }}
                    @endif
                </td>
                <td>
                    <a href="{{ route('attendance.show', $day->id ?? 0) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection