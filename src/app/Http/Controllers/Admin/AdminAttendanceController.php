<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\AttendanceCorrectionRequest;


class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date',Carbon::today()->toDateString());

        $attendances = Attendance::with(['user', 'rests'])
            ->whereDate('clock_in', $date)
            ->get();

        return view('admin.attendance.index', compact('attendances', 'date'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);

        $correction = AttendanceCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('user_id', $attendance->user_id)
            ->latest('updated_at')
            ->first();

        return view('admin.attendance.show', compact('attendance', 'correction'));
    }

    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('rests')->findOrFail($id);

        DB::transaction(function () use ($attendance, $request) {
            $date = Carbon::parse($attendance->clock_in)->toDateString();

            $attendance->update([
                'clock_in'  => Carbon::parse($date . ' ' . $request->clock_in),
                'clock_out' => $request->clock_out ? Carbon::parse($date . ' ' . $request->clock_out) : null,
            ]);

            $attendance->rests()->delete();
            if ($request->rests) {
                foreach ($request->rests as $restData) {
                    if (!empty($restData['start']) && !empty($restData['end'])) {
                        Rest::create([
                            'attendance_id' => $attendance->id,
                            'rest_start' => Carbon::parse($date . ' ' . $restData['start']),
                            'rest_end'   => Carbon::parse($date . ' ' . $restData['end']),
                        ]);
                    }
                }
            }

            AttendanceCorrectionRequest::create([
                'attendance_id'      => $attendance->id,
                'user_id'            => $attendance->user_id,
                'correction_content' => '管理者による勤怠修正',
                'reason'             => $request->reason ?? '', 
                'status'             => 'approved',
            ]);
        });

        return redirect()->route('admin.attendance.show', $id)
            ->with('success', '勤怠情報を修正しました。');
    }

    public function stafflist($id, Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $user = User::findOrFail($id);

        $attendances = Attendance::with('rests')
            ->where('user_id', $id)
            ->whereYear('clock_in', Carbon::parse($month)->year)
            ->whereMonth('clock_in', Carbon::parse($month)->month)
            ->get();

        return view('admin.attendance.staff', compact('user', 'attendances', 'month'));
    }
}