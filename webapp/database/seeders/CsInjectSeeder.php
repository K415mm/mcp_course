<?php

namespace Database\Seeders;

use App\Models\CsInject;
use Illuminate\Database\Seeder;

class CsInjectSeeder extends Seeder
{
    public function run(): void
    {
        CsInject::query()->delete();

        // ── PHANTOM GRID Scenario ──────────────────────────────────
        $phantom = [
            // Standard injects
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'ALERTE MJ #1',
                'content'      => 'Signaux faibles détectés simultanément dans 3 secteurs. Traitez l\'information avec discernement.',
                'color'        => 'amber',
                'phase_hint'   => '1',
                'is_surprise'  => false,
                'sort_order'   => 10,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'ALERTE MJ #2',
                'content'      => 'Ransomware "Mimic" confirmé sur 2 serveurs du secteur Finance — 180 000 dossiers potentiellement compromis.',
                'color'        => 'red',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 20,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'ALERTE MJ #3',
                'content'      => 'Journaliste Mosaïque FM en ligne — demande si la Tunisie confirme une cyberattaque d\'envergure nationale.',
                'color'        => 'amber',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 30,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'INJECT #4',
                'content'      => 'Influenceur 800K abonnés publie : "données postales de 2 millions de Tunisiens vendues sur le darkweb" — non confirmé.',
                'color'        => 'red',
                'phase_hint'   => '3',
                'is_surprise'  => false,
                'sort_order'   => 40,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'INJECT #5',
                'content'      => 'Briefing urgent demandé par le Ministère de l\'Intérieur dans 20 minutes. Préparez un état des lieux.',
                'color'        => 'purple',
                'phase_hint'   => '3',
                'is_surprise'  => false,
                'sort_order'   => 50,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'PHANTOM GRID',
                'content'      => 'PHANTOM GRID — Phase 2 de l\'opération initiée. Les 18 prochaines heures vous appartiennent si vous le pouvez.',
                'color'        => 'red',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 60,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'INJECT #7',
                'content'      => 'Le réseau ferroviaire signale des anomalies dans le système de signalisation SCADA. Aucun incident confirmé pour l\'instant.',
                'color'        => 'amber',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 70,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'INJECT #8',
                'content'      => 'Al Jazeera demande une déclaration officielle sur la cybersécurité tunisienne. Réponse attendue dans 15 minutes.',
                'color'        => 'amber',
                'phase_hint'   => '3',
                'is_surprise'  => false,
                'sort_order'   => 80,
            ],
            // Surprise cards
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'SURPRISE A',
                'content'      => 'VPN du CERT coupé pendant 10 minutes — aucune connexion sécurisée possible pendant cette période.',
                'color'        => 'red',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 100,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'SURPRISE B',
                'content'      => 'Agent interne suspecté de fuite d\'informations — rapport anonyme reçu par le CERT. Procédure à suivre ?',
                'color'        => 'purple',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 110,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'SURPRISE C',
                'content'      => 'PHANTOM GRID publie en ligne les données personnelles de 2 000 fonctionnaires tunisiens avec preuves.',
                'color'        => 'red',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 120,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'SURPRISE D',
                'content'      => 'Un pays voisin subit la même attaque et demande une assistance technique d\'urgence à la Tunisie.',
                'color'        => 'amber',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 130,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'SURPRISE E',
                'content'      => 'Panne de courant dans le datacenter principal — basculement vers le système de secours. 5 minutes d\'indisponibilité.',
                'color'        => 'red',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 140,
            ],
            [
                'scenario_key' => 'phantom_grid',
                'tag'          => 'SURPRISE F',
                'content'      => 'Le porte-parole du gouvernement improvise une déclaration TV sans coordination — créant de la confusion publique.',
                'color'        => 'purple',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 150,
            ],
        ];

        // ── ATLAS BREACH Injects ───────────────────────────────────
        $atlas = [
            [
                'scenario_key' => 'atlas_breach',
                'tag'          => 'DÉCOUVERTE #1',
                'content'      => 'L\'éditeur LogiSoft signale une mise à jour compromise distribuée la semaine dernière à 47 ministères.',
                'color'        => 'red',
                'phase_hint'   => '1',
                'is_surprise'  => false,
                'sort_order'   => 10,
            ],
            [
                'scenario_key' => 'atlas_breach',
                'tag'          => 'DÉCOUVERTE #2',
                'content'      => 'Backdoor identifiée dans le module de gestion des identités — ATLAS peut s\'authentifier comme n\'importe quel agent.',
                'color'        => 'red',
                'phase_hint'   => '1',
                'is_surprise'  => false,
                'sort_order'   => 20,
            ],
            [
                'scenario_key' => 'atlas_breach',
                'tag'          => 'ESCALADE #3',
                'content'      => 'ATLAS a exfiltré les mappings VPN de 12 ministères. Un accès distant non autorisé est probable depuis 90 jours.',
                'color'        => 'red',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 30,
            ],
            [
                'scenario_key' => 'atlas_breach',
                'tag'          => 'SURPRISE A',
                'content'      => 'Le fournisseur logiciel nie toute compromission publiquement, compliquant la coordination de la réponse.',
                'color'        => 'purple',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 100,
            ],
        ];

        // ── GHOST PROTOCOL Injects ─────────────────────────────────
        $ghost = [
            [
                'scenario_key' => 'ghost_protocol',
                'tag'          => 'RANSOMWARE #1',
                'content'      => '3 hôpitaux signalent que leurs systèmes de gestion des patients sont cryptés. Consultations annulées.',
                'color'        => 'red',
                'phase_hint'   => '1',
                'is_surprise'  => false,
                'sort_order'   => 10,
            ],
            [
                'scenario_key' => 'ghost_protocol',
                'tag'          => 'RANSOMWARE #2',
                'content'      => 'L\'aéroport de Tunis-Carthage passe en procédure manuelle — systèmes d\'enregistrement hors ligne.',
                'color'        => 'red',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 20,
            ],
            [
                'scenario_key' => 'ghost_protocol',
                'tag'          => 'DEMANDE #3',
                'content'      => 'GHOST COLLECTIVE exige 2 millions USD en Bitcoin. Deadline dans 6 heures. Compte à rebours actif.',
                'color'        => 'red',
                'phase_hint'   => '3',
                'is_surprise'  => false,
                'sort_order'   => 30,
            ],
            [
                'scenario_key' => 'ghost_protocol',
                'tag'          => 'SURPRISE A',
                'content'      => 'Un patient en état critique est transféré en urgence à cause des systèmes hors ligne — pression médiatique maximale.',
                'color'        => 'red',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 100,
            ],
        ];

        // ── CARTHAGE SHIELD 26 Injects ──────────────────────────────
        $cs26 = [
            [
                'scenario_key' => 'carthage_shield_26',
                'tag'          => 'ALERTE NATIONALE #1',
                'content'      => 'Des indicateurs convergents montrent une compromission simultanée de FINANCE et TRANSPORT.',
                'color'        => 'red',
                'phase_hint'   => '1',
                'is_surprise'  => false,
                'sort_order'   => 10,
            ],
            [
                'scenario_key' => 'carthage_shield_26',
                'tag'          => 'INJECT MEDIA #2',
                'content'      => 'Une rumeur de fuite massive devient virale. La pression publique s\'accelere.',
                'color'        => 'amber',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 20,
            ],
            [
                'scenario_key' => 'carthage_shield_26',
                'tag'          => 'ARBITRAGE #3',
                'content'      => 'Les priorites sectorielles divergent. Une decision nationale est requise sous 15 minutes.',
                'color'        => 'purple',
                'phase_hint'   => '3',
                'is_surprise'  => false,
                'sort_order'   => 30,
            ],
            [
                'scenario_key' => 'carthage_shield_26',
                'tag'          => 'SURPRISE A',
                'content'      => 'Un faux communique officiel circule et contredit la cellule de crise.',
                'color'        => 'red',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 100,
            ],
        ];

        // ── NEPTUNE STRIKE Injects ──────────────────────────────────
        $neptune = [
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'CRITICAL ALERT',
                'content'      => 'Simultaneous OT System Failure — Port Marseille-Fos. SCADA system controlling Terminal J cranes frozen. MV Olympia with IMO Class 3 hazardous cargo immobilized at Quay J4. Remote connection from UAE/Romania.',
                'color'        => 'red',
                'phase_hint'   => '1',
                'is_surprise'  => false,
                'sort_order'   => 10,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'WEAK SIGNAL',
                'content'      => 'CROSS Med reports MV Silver Horizon disabled AIS transponder for 47 minutes last night near the SEA-ME-WE 5 submarine cable landing point.',
                'color'        => 'amber',
                'phase_hint'   => '1',
                'is_surprise'  => false,
                'sort_order'   => 20,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'AI ALERT',
                'content'      => 'SENTINEL v3.2 correlation alert: 3 similar incidents in past 72 hours (Genoa, Tanger Med, Valencia). Consistent with APT-POSEIDON. Recommendation: MARSEC CYBER ALPHA.',
                'color'        => 'blue',
                'phase_hint'   => '1',
                'is_surprise'  => false,
                'sort_order'   => 30,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'FORENSIC INTEL',
                'content'      => '4-Stage Attack Vector Confirmed. Trojanised Kongsberg update PDF, CVE-2024-28921 pivot, Siemens S7-1500 firmware implant, Coordinated trigger.',
                'color'        => 'blue',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 40,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'POLITICAL DECISION',
                'content'      => 'Attribution Decision Required. Threat intelligence points to state-linked APT-POSEIDON. France must decide: public attribution or confidential coordination?',
                'color'        => 'red',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 50,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'NAVAL INTEL',
                'content'      => 'SIGINT confirms unidentified hydrographic research vessel operating near MEDEX-3 submarine cable. Panamanian flag of convenience, deep-sea ROV emissions.',
                'color'        => 'blue',
                'phase_hint'   => '2',
                'is_surprise'  => false,
                'sort_order'   => 60,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'CRITICAL IMMEDIATE',
                'content'      => 'GPS spoofing active in Marseille approach. MV Adriatic Star drifting, GMDSS broadcasting corrupted distress signals. Fos LNG valves receiving unauthorized open commands.',
                'color'        => 'red',
                'phase_hint'   => '3',
                'is_surprise'  => false,
                'sort_order'   => 70,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'MEDIA CRISIS',
                'content'      => 'BFM TV Live: "Cyberattaque sur les ports français". Frozen cranes, vessels diverted to Genoa/Barcelona, stocks down 2.3%.',
                'color'        => 'amber',
                'phase_hint'   => '3',
                'is_surprise'  => false,
                'sort_order'   => 80,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'STRATEGIC COORD',
                'content'      => 'ENISA activated, Italy & Spain confirm incidents, IMO emergency session in 72h. Mediterranean regional governance architecture is the central question.',
                'color'        => 'purple',
                'phase_hint'   => '4',
                'is_surprise'  => false,
                'sort_order'   => 90,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'LESSONS IDENTIFIED',
                'content'      => 'Post-Incident Analysis: 5 critical systemic gaps identified. Delay in CERT notification, no BCP testing, lack of real-time IoC sharing, legal gaps for flags of convenience, GPS/AIS not in SOLAS.',
                'color'        => 'blue',
                'phase_hint'   => '4',
                'is_surprise'  => false,
                'sort_order'   => 100,
            ],
            // Surprise / Optional injects
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'WIKILEAKS ALERT',
                'content'      => 'WIKILEAKS: Attacker has sent exploit code to WikiLeaks. Public release in 6h. How does your team respond?',
                'color'        => 'red',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 110,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'HOSTILE ACTION',
                'content'      => 'HOSTILE ACTION: Suspect vessel has fired on a French patrol boat. One crew member wounded. Rules of engagement?',
                'color'        => 'red',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 120,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'RANSOM DEMAND',
                'content'      => 'RANSOM DEMAND: Attacker offers decryption keys for €2M in cryptocurrency. Do you pay? Who decides?',
                'color'        => 'red',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 130,
            ],
            [
                'scenario_key' => 'neptune_strike',
                'tag'          => 'FALSE FLAG',
                'content'      => 'FALSE FLAG: A Southern Med minister claims this was a European false flag operation. How does this change your attribution posture?',
                'color'        => 'purple',
                'phase_hint'   => null,
                'is_surprise'  => true,
                'sort_order'   => 140,
            ],
        ];

        $all = array_merge($phantom, $atlas, $ghost, $cs26, $neptune);
        CsInject::insert($all);
    }
}
