<?php

namespace Database\Seeders;

use App\Models\CsInject;
use Illuminate\Database\Seeder;

class NeptuneInjectSeeder extends Seeder
{
    public function run(): void
    {
        CsInject::where('scenario_key', 'neptune_strike')->delete();

        $neptune = [
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'CRITICAL ALERT',
                'content'      => 'Simultaneous OT System Failure — Port Marseille-Fos. SCADA system controlling Terminal J cranes frozen. MV Olympia with IMO Class 3 hazardous cargo immobilized at Quay J4. Remote connection detected. [ACTION REQUIRED: Decision needed]',
                'color'        => 'red',
                'phase_hint'   => '1',
                'is_surprise'  => false,
                'sort_order'   => 10,
                'requires_action' => true,
                'expected_action_type' => 'decision',
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'WEAK SIGNAL',
                'content'      => 'CROSS Med reports MV Silver Horizon disabled AIS transponder for 47 minutes last night near the SEA-ME-WE 5 submarine cable landing point.',
                'color'        => 'amber',
                'phase_hint'   => '1',
                'is_surprise'  => false,
                'sort_order'   => 20,
                'requires_action' => false,
                'expected_action_type' => null,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'AI ALERT',
                'content'      => 'SENTINEL v3.2 correlation alert: 3 similar incidents in past 72 hours (Genoa, Tanger Med, Valencia). Consistent with APT-POSEIDON. Recommendation: MARSEC CYBER ALPHA. [ACTION REQUIRED: Communication needed]',
                'color'        => 'blue',
                'phase_hint'   => '1',
                'is_surprise'  => false,
                'sort_order'   => 30,
                'requires_action' => true,
                'expected_action_type' => 'communication',
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'FORENSIC INTEL',
                'content'      => '4-Stage Attack Vector Confirmed. Trojanised Kongsberg update PDF, CVE-2024-28921 pivot, Siemens S7-1500 firmware implant, Coordinated trigger.',
                'color'        => 'blue',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 40,
                'requires_action' => false,
                'expected_action_type' => null,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'POLITICAL DECISION',
                'content'      => 'Attribution Decision Required. Threat intelligence points to state-linked APT-POSEIDON. The command cell must decide: public attribution or confidential coordination? [ACTION REQUIRED: Decision needed]',
                'color'        => 'red',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 50,
                'requires_action' => true,
                'expected_action_type' => 'decision',
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'NAVAL INTEL',
                'content'      => 'SIGINT confirms unidentified hydrographic research vessel operating near MEDEX-3 submarine cable. Panamanian flag of convenience, deep-sea ROV emissions. [ACTION REQUIRED: Escalation needed]',
                'color'        => 'blue',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 60,
                'requires_action' => true,
                'expected_action_type' => 'escalade',
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'CRITICAL IMMEDIATE',
                'content'      => 'GPS spoofing active in Marseille approach. MV Adriatic Star drifting, GMDSS broadcasting corrupted distress signals. Fos LNG valves receiving unauthorized open commands. [ACTION REQUIRED: Escalation needed]',
                'color'        => 'red',
                'phase_hint'   => '3',
                'is_surprise'  => false,
                'sort_order'   => 70,
                'requires_action' => true,
                'expected_action_type' => 'escalade',
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'MEDIA CRISIS',
                'content'      => 'News TV Live: "Cyberattack on regional ports". Frozen cranes, vessels diverted to Genoa/Barcelona, local maritime stocks down 2.3%. [ACTION REQUIRED: Communication needed]',
                'color'        => 'amber',
                'phase_hint'   => '3',
                'is_surprise'  => false,
                'sort_order'   => 80,
                'requires_action' => true,
                'expected_action_type' => 'communication',
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'STRATEGIC COORD',
                'content'      => 'ENISA activated, regional partners confirm incidents, IMO emergency session in 72h. Mediterranean regional governance architecture is the central question.',
                'color'        => 'purple',
                'phase_hint'   => '4',
                'is_surprise'  => false,
                'sort_order'   => 90,
                'requires_action' => false,
                'expected_action_type' => null,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'LESSONS IDENTIFIED',
                'content'      => 'Post-Incident Analysis: 5 critical systemic gaps identified. Delay in CERT notification, no BCP testing, lack of real-time IoC sharing, legal gaps for flags of convenience, GPS/AIS not in SOLAS.',
                'color'        => 'blue',
                'phase_hint'   => '4',
                'is_surprise'  => false,
                'sort_order'   => 100,
                'requires_action' => false,
                'expected_action_type' => null,
            ],
            // Surprise / Optional injects
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'WIKILEAKS ALERT',
                'content'      => 'WIKILEAKS: Attacker has sent exploit code to WikiLeaks. Public release in 6h. How does your team respond? [ACTION REQUIRED: Communication needed]',
                'color'        => 'red',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 110,
                'requires_action' => true,
                'expected_action_type' => 'communication',
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'HOSTILE ACTION',
                'content'      => 'HOSTILE ACTION: Suspect vessel has fired on a patrol boat. One crew member wounded. Rules of engagement? [ACTION REQUIRED: Decision needed]',
                'color'        => 'red',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 120,
                'requires_action' => true,
                'expected_action_type' => 'decision',
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'RANSOM DEMAND',
                'content'      => 'RANSOM DEMAND: Attacker offers decryption keys for €2M in cryptocurrency. Do you pay? Who decides? [ACTION REQUIRED: Decision needed]',
                'color'        => 'red',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 130,
                'requires_action' => true,
                'expected_action_type' => 'decision',
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'FALSE FLAG',
                'content'      => 'FALSE FLAG: A Southern Med minister claims this was a European false flag operation. How does this change your attribution posture? [ACTION REQUIRED: Escalation needed]',
                'color'        => 'purple',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 140,
                'requires_action' => true,
                'expected_action_type' => 'escalade',
            ],
        ];

        CsInject::insert($neptune);
    }
}
