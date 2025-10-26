@extends('layouts.app')

@section('content')
<div class="attendance-container">
    <div class="attendance-info">
        <div class="status-badge">
            @if($status === 'before_work')
            勤務外
            @elseif($status === 'working')
            出勤中
            @elseif($status === 'break')
            休憩中
            @elseif($status === 'after_work')
            退勤済
            @endif
        </div>
        @php
            $weekMap = ['日','月','火','水','木','金','土'];
            $today = \Carbon\Carbon::today();
        @endphp
        <p class="today-date">
            {{ $today->format('Y年n月j日') }} ({{ $weekMap[$today->dayOfWeek] }})
        </p>
        <p class="current-time">
            <span id="clock"></span>
        </p>
    </div>

    @if($status === 'before_work')
    <div class="attendance-actions">
        <form method="POST" action="{{ route('attendance.store') }}" class="attendance-form">
            @csrf
            <button type="submit" name="action" value="start_work" class="btn btn-start-work">
                出勤
            </button>
        </form>
    </div>
    @endif

    @if($status === 'working')
    <div class="attendance-actions">
        <form method="POST" action="{{ route('attendance.store') }}" class="attendance-form">
            @csrf
            <button type="submit" name="action" value="rest_start" class="btn btn-rest-start">
                休憩入
            </button>
            <button type="submit" name="action" value="end_work" class="btn btn-end-work">
                退勤
            </button>
        </form>
    </div>
    @endif

    @if($status === 'break')
    <div class="attendance-actions">
        <form method="POST" action="{{ route('attendance.store') }}" class="attendance-form">
            @csrf
            <button type="submit" name="action" value="rest_end" class="btn btn-rest-end">
                休憩戻
            </button>
        </form>
    </div>
    @endif

    @if($status === 'after_work')
    <div class="attendance-message">
        <p class="end-message">お疲れさまでした。</p>
    </div>
    @endif
</div>

<script>
    function updateClock() {
        const now = new Date();
        const formatted = now.toLocaleTimeString('ja-Jp', {
            hour: '2-digit',
            minute: '2-digit'
        });
        document.getElementById('clock').textContent = formatted;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection