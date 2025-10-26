<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\Rest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\AttendanceCorrectionRequest;
use App\Http\Requests\AttendanceCorrectionRequest as AttendanceCorrectionFormRequest;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        //打刻画面リクエストはいるのかチェック
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('clock_in', $today)
            ->with('rests')
            ->first();

        if (!$attendance) {
            $status = 'before_work';
        } elseif (!$attendance->clock_out) {
            if ($attendance->rests()->whereNull('rest_end')->exists()) {
                $status = 'break';
            } else {
                $status = 'working';
            }
        } else {
            $status = 'after_work';
        }

        return view('user.attendance.form', compact('status'));
    }

    public function store(Request $request)
    {
        //打刻処理
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('clock_in', $today)
            ->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id'   => $user->id,
                'status'    => 'before_work',
                'clock_in'  => null,
                'clock_out' => null,
            ]);
        }

        switch ($request->input('action')) {
            case 'start_work':
                if (!$attendance->clock_in) {
                    $attendance->clock_in = now();
                    $attendance->status = 'working';
                    $attendance->save();
                }
                break;

            case 'end_work':
                if (!$attendance->clock_out) {
                    $attendance->clock_out = now();
                    $attendance->status = 'after_work';
                    $attendance->save();
                }
                break;

            case 'rest_start':
                $attendance->rests()->create(['rest_start' => now()]);
                $attendance->status = 'break';
                $attendance->save();
                break;

            case 'rest_end':
                $rest = $attendance->rests()->whereNull('rest_end')->latest()->first();
                if ($rest) {
                    $rest->rest_end = now();
                    $rest->save();
                }
                $attendance->status = 'working';
                $attendance->save();
                break;
        }

        return redirect()->route('attendance.index');
    }

    public function list(Request $request)
    {
        //勤怠一覧
        $user = Auth::user();
        $month = $request->input('month', now()->format('Y-m'));
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in', [$start, $end])
            ->orderBy('clock_in', 'asc')
            ->with('rests')
            ->get();

        return view('user.attendance.index', compact('attendances', 'month'));
    }

    public function show($id)
    {
        //勤怠詳細
        $attendance = Attendance::with(['rests', 'correctionRequests'])->find($id);

        $latestRequest = null;
        $pendingRequest = null;
        if ($attendance) {
            $latestRequest = $attendance->correctionRequests()->latest()->first();
            $pendingRequest = $attendance->correctionRequests()
                ->where('status', 'pending')
                ->latest()
                ->first();
        }

        return view('user.attendance.show', compact('attendance','latestRequest', 'pendingRequest'));
    }

    public function update(AttendanceCorrectionFormRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        if ($attendance->correctionRequests()->where('status', 'pending')->exists()) {
            return back()->withErrors(['既に承認待ちの修正申請があります。']);
        }

        $validated = $request->validated();

        $correctionContent = [
            'clock_in'  => $validated['clock_in'] ?? null,
            'clock_out' => $validated['clock_out'] ?? null,
            'rests'     => $validated['rests'] ?? [],
        ];

        AttendanceCorrectionRequest::create([
            'attendance_id'      => $attendance->id,
            'user_id'            => auth()->id(),
            'correction_content' => json_encode($correctionContent, JSON_UNESCAPED_UNICODE),
            'reason'             => $validated['reason'],
            'status'             => 'pending',
        ]);

        return redirect()->route('attendance.show', $id)
            ->with('success', '修正申請を送信しました。');
    }

    public function clockIn()
    {
        $user = auth()->user();

        if (!Attendance::where('user_id', $user->id)
            ->whereDate('clock_in', now()->toDateString())
            ->exists()) {
            Attendance::create([
                'user_id' => $user->id,
                'clock_in' => now(),
                'clock_out' => null,
                'status' => 'working',
            ]);
        }

        return redirect()->route('attendance.index');
    }
}
