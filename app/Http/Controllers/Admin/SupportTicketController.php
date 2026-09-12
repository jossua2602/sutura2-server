<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::query()->with('user:id,name,email');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority')->toString());
        }

        return response()->json($query->latest()->get());
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (in_array($validated['status'], ['resolved', 'closed'], true) && blank($validated['resolution_notes'] ?? null)) {
            return response()->json(['message' => 'Resolution notes are required when closing a ticket.'], 422);
        }

        $ticket->update([
            ...$validated,
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed'], true) ? now() : null,
        ]);

        return response()->json($ticket->load('user:id,name,email'));
    }
}
