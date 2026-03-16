<?php

namespace App\Mail;

use App\Models\ModuleCompletion;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ModuleCompletionMail extends Mailable
{
    use Queueable, SerializesModels;

    // Badge mapping per module number
    public static array $badgeLabels = [
        '01' => 'Agentic AI Foundations',
        '02' => 'MCP Fundamentals',
        '03' => 'Cyber Defence Foundations',
        '04' => 'Python Essentials',
        '05' => 'Building MCP Servers',
        '06' => 'Building MCP Clients',
        '07' => 'MCP Integrations',
        '08' => 'Policy & Boundaries',
    ];

    public function __construct(
        public User  $user,
        public array $module,
        public ?array $nextModule = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🏆 Module Complete: ' . $this->module['title'] . ' — RAISEGUARD Academy',
        );
    }

    public function content(): Content
    {
        // Extract module number from slug like "module-01-agentic-ai-foundations"
        preg_match('/(\d{2})/', $this->module['slug'] ?? '', $m);
        $moduleNum = $m[1] ?? '01';
        $badgeLabel = self::$badgeLabels[$moduleNum] ?? $this->module['title'];

        return new Content(
            view: 'emails.module_completion',
            with: [
                'user'          => $this->user,
                'module'        => $this->module,
                'nextModule'    => $this->nextModule,
                'moduleNum'     => $moduleNum,
                'badgeLabel'    => $badgeLabel,
                'continueUrl'   => url('/courses'),
                'badgeImageUrl' => url('/img/badges/module_badges.png'),
            ]
        );
    }
}
