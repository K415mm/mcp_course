<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class GameCard extends Model
{
    protected $fillable = [
        'name', 'type', 'phase', 'description', 'effect', 'mitre_id', 'mitre_name', 'mitre_description', 'cost', 'points', 'duration', 'team', 'data',
    ];

    protected function casts(): array
    {
        return [
            'cost'   => 'integer',
            'points' => 'integer',
            'data'   => 'json',
        ];
    }

    // ── Scopes ──────────────────────────────────────────────────────

    public function scopeBlue(Builder $q): Builder   { return $q->where('type', 'blue'); }
    public function scopeRed(Builder $q): Builder    { return $q->where('type', 'red'); }
    public function scopeResources(Builder $q): Builder { return $q->where('type', 'resource'); }
    public function scopeEvents(Builder $q): Builder { return $q->where('type', 'event'); }

    public function scopeForTeam(Builder $q, string $team): Builder
    {
        return $q->whereIn('team', [$team, 'all']);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    public function cssClass(): string
    {
        if ($this->type === 'event') {
            return 'card-' . ($this->subtype ?? 'warning');
        }
        return 'card-' . $this->type;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'blue'     => 'Défense',
            'red'      => 'Attaque',
            'resource' => 'Ressource',
            'event'    => match ($this->subtype) {
                'danger'    => 'Danger',
                'success'   => 'Opportunité',
                'joker'     => 'Joker',
                'situation' => 'Situation',
                'alerte'    => 'Alerte',
                default     => 'Événement',
            },
            default => ucfirst($this->type),
        };
    }
}
