<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;

class AdminRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $query = AttendanceCorrectionRequest::with('user');

        if ($status === 'approved') {
            $requests = $query->where('status', 'approved')->get();
        } else {
            $requests = $query->where('status', 'pending')->get();
        }

        return view('admin.requests.index', compact('requests', 'status'));
    }

    public function show($id)
    {
        $requestData = AttendanceCorrectionRequest::with(['user', 'attendance.rests'])
            ->findOrFail($id);

        return view('admin.requests.approve', compact('requestData'));
    }

    public function approveView($id)
    {
        $requestData = AttendanceCorrectionRequest::with(['user', 'attendance.rests'])
            ->findOrFail($id);

        return view('admin.requests.approve', compact('requestData'));
    }

    public function approve($id)
    {
        $request = AttendanceCorrectionRequest::findOrFail($id);
        $request->status = 'approved';
        $request->save();

        return redirect()->route('admin.requests.approve.view', $id);
    }
}
