<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $requests = AttendanceCorrectionRequest::where('user_id', '$user->id')
            ->orderBy('created_at', 'desc')
            ->get();
        
            $pending = $requests->where('status', 'pending');
            $approved = $requests->where('status','approved');

            return view('user.requests.index', compact('pending', 'approved'));
    }
}
