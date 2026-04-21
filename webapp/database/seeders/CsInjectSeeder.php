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

        $all = array_merge($phantom, $atlas, $ghost);
        CsInject::insert($all);
    }
}
