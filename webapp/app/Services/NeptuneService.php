<?php

namespace App\Services;

use App\Models\NeptuneScenario;
use App\Models\CsSession;
use App\Models\CsTeam;
use App\Models\User;

class NeptuneService extends CsService
{
    public function createSession(User $moderator, array $data): CsSession
    {
        $scenario = NeptuneScenario::find($data['scenario_key'] ?? 'neptune_strike');

        $session = CsSession::create([
            'name'         => $data['name'],
            'scenario_key' => $scenario['key'],
            'moderator_id' => $moderator->id,
            'status'       => 'lobby',
            'atmosphere'   => 'calm',
        ]);

        $teamDefinitions = $scenario['teams'] ?? CsTeam::defaultTeams();
        foreach ($teamDefinitions as $teamData) {
            $session->teams()->create($teamData);
        }

        return $session->load('teams');
    }
}
