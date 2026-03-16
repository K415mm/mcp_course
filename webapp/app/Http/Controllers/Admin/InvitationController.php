<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    /** List all invitations */
    public function index()
    {
        $invitations = Invitation::with('invitedBy')
            ->latest()
            ->get();
        return view('admin.invitations.index', compact('invitations'));
    }

    /** Send a single invitation */
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'name'  => 'nullable|string|max:255',
        ]);

        // Prevent duplicate pending invites to the same email
        $existing = Invitation::where('email', $data['email'])->pending()->first();
        if ($existing) {
            return back()->withErrors(['email' => 'A pending invitation already exists for this email.']);
        }

        $invitation = $this->createAndSend($data['email'], $data['name'] ?? null);

        return back()->with('success', "Invitation sent to {$invitation->email}.");
    }

    /** Bulk send invitations from textarea (one email per line) or CSV file */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'emails'     => 'nullable|string',
            'csv_file'   => 'nullable|file|mimes:csv,txt|max:2048',
        ]);

        $rawEmails = [];

        if ($request->hasFile('csv_file')) {
            $rawEmails = array_map('str_getcsv', file($request->file('csv_file')->getRealPath()));
            $rawEmails = array_merge(...array_map(fn($row) => $row, $rawEmails));
        }

        if ($request->filled('emails')) {
            $rawEmails = array_merge(
                $rawEmails,
                preg_split('/[\r\n,]+/', $request->input('emails'))
            );
        }

        $sent = 0;
        $skipped = 0;
        $errors = [];

        foreach (array_unique($rawEmails) as $email) {
            $email = trim($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email: {$email}";
                continue;
            }
            if (Invitation::where('email', $email)->pending()->exists()) {
                $skipped++;
                continue;
            }
            $this->createAndSend($email);
            $sent++;
        }

        $msg = "Sent: {$sent}. Skipped (already invited): {$skipped}.";
        if ($errors) {
            $msg .= ' Errors: ' . implode(', ', $errors);
        }

        return back()->with('success', $msg);
    }

    /** Revoke / delete an invitation */
    public function destroy(Invitation $invitation)
    {
        $invitation->delete();
        return back()->with('success', 'Invitation revoked.');
    }

    /** Resend an invitation */
    public function resend(Invitation $invitation)
    {
        if ($invitation->isAccepted()) {
            return back()->withErrors(['error' => 'This invitation has already been accepted.']);
        }
        $invitation->update([
            'token'      => Str::random(48),
            'expires_at' => now()->addDays(7),
        ]);
        Mail::to($invitation->email)->send(new InvitationMail($invitation));
        return back()->with('success', "Invitation resent to {$invitation->email}.");
    }

    // ── Private helpers ────────────────────────────────────────────
    private function createAndSend(string $email, ?string $name = null): Invitation
    {
        $invitation = Invitation::create([
            'email'      => $email,
            'name'       => $name,
            'token'      => Str::random(48),
            'invited_by' => Auth::id(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($email)->send(new InvitationMail($invitation));

        return $invitation;
    }
}
