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

        if (isset($invitation->mail_failed) && $invitation->mail_failed) {
            return back()->with('success', "Invitation saved in database for {$invitation->email}, but notification email failed to send (Error: {$invitation->mail_error}). You can manually copy the link below.");
        }

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
        $mailErrors = 0;
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
            $invitation = $this->createAndSend($email);
            if (isset($invitation->mail_failed) && $invitation->mail_failed) {
                $mailErrors++;
            } else {
                $sent++;
            }
        }

        $msg = "Bulk processing complete. Total invitations saved: " . ($sent + $mailErrors) . " (Emails delivered: {$sent}, Emails failed: {$mailErrors}). Skipped: {$skipped}.";
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
        try {
            Mail::to($invitation->email)->send(new InvitationMail($invitation));
            return back()->with('success', "Invitation resent to {$invitation->email}.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to resend invitation to {$invitation->email}: " . $e->getMessage());
            return back()->withErrors(['error' => "Failed to deliver email: " . $e->getMessage()]);
        }
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

        try {
            Mail::to($email)->send(new InvitationMail($invitation));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Mail delivery failed for invitation to {$email}: " . $e->getMessage());
            $invitation->mail_failed = true;
            $invitation->mail_error = $e->getMessage();
        }

        return $invitation;
    }
}
