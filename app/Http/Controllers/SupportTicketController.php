<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
        ]);

        $ticket = SupportTicket::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($ticket, 201);
    }

    public function mine(Request $request)
    {
        return response()->json(
            SupportTicket::where('user_id', $request->user()->id)->latest()->get()
        );
    }
}
