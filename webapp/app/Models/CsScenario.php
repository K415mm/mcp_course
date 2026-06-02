<?php

namespace App\Models;

/**
 * CsScenario — Static scenario registry.
 * Scenarios are defined in code; new ones can be added here.
 * Each scenario defines its phases, teams, and narrative.
 */
class CsScenario
{
    public static function all(): array
    {
        return [
            'phantom_grid'   => self::phantomGrid(),
            'atlas_breach'   => self::atlasBreach(),
            'ghost_protocol' => self::ghostProtocol(),
            'carthage_shield_26' => self::carthageShield26(),
        ];
    }

    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function list(): array
    {
        return collect(self::all())->map(fn($s) => [
            'key'         => $s['key'],
            'title'       => $s['title'],
            'description' => $s['description'],
            'difficulty'  => $s['difficulty'],
            'duration'    => $s['duration_minutes'] . ' min',
        ])->values()->all();
    }

    // ── SCENARIO 1: PHANTOM GRID ────────────────────────────────────
    // The fully fleshed-out default scenario
    private static function phantomGrid(): array
    {
        return [
            'key'              => 'phantom_grid',
            'title'            => 'Opération PHANTOM GRID',
            'description'      => 'Une cyberattaque coordonnée menace les infrastructures critiques de la Tunisie. Le groupe PHANTOM GRID a simultanément ciblé le secteur bancaire, les transports et les services e-gouvernement. La Coalition Nationale Cyber doit répondre.',
            'difficulty'       => 'Avancé',
            'duration_minutes' => 150,
            'attacker_name'    => 'PHANTOM GRID',
            'attacker_icon'    => '☠️',
            'phantom_messages' => [
                'PHANTOM GRID is watching. Vos systèmes nous appartiennent.',
                'Phase 2 initiée. 18 heures vous appartiennent.',
                'Vous ne pouvez pas nous arrêter. Nous sommes partout.',
                'Chaque décision que vous prenez, nous la voyons.',
                'La Coalition va s\'effondrer. Resistez si vous pouvez.',
            ],
            'phases' => [
                [
                    'index' => 0, 'name' => 'OUVERTURE', 'tag' => 'PHASE 0',
                    'desc' => 'Mise en situation & prise de rôle', 'duration_seconds' => 600,
                    'decision_matrix' => null,
                ],
                [
                    'index' => 1, 'name' => 'DÉTECTION', 'tag' => 'PHASE 1',
                    'desc' => 'Signaux faibles & incertitude initiale', 'duration_seconds' => 1800,
                    'decision_matrix' => [
                        'context' => '"En moins d\'une heure, vos équipes détectent des comportements anormaux. Corrélation entre secteurs émergente. 30 minutes pour qualifier et décider."',
                        'injects' => ['[Finance] x40 API interbancaires anormales', '[E-Gov] 47 000 tentatives portail impôts', '[Transport] Compte OACA compromis', '[Énergie] SCADA niveau 1 — 3 sous-stations'],
                        'options' => [
                            ['key' => 'A', 'label' => 'Surveiller & documenter', 'points' => 5,  'note' => '⚠️ Mauvais : veille passive insuffisante face à signaux corrélés. L\'OMBRE gagne du terrain.'],
                            ['key' => 'B', 'label' => 'Escalade + isolation partielle', 'points' => 20, 'note' => '✅ Optimal : NIST IR "Contain early, communicate fast". Coordination nationale activée.'],
                            ['key' => 'C', 'label' => 'Coupure totale', 'points' => 10, 'note' => '⚡ Excessif sans preuve confirmée — mais proactif. Accepter si justifié.'],
                        ],
                        'hint' => 'Si l\'équipe hésite : "Quelle est votre chaîne d\'escalade documentée ?" (Référence : ANSSI PGSSI Phase 1)',
                    ],
                ],
                [
                    'index' => 2, 'name' => 'AMPLIFICATION', 'tag' => 'PHASE 2',
                    'desc' => 'Impacts sur les services essentiels', 'duration_seconds' => 2400,
                    'decision_matrix' => [
                        'context' => '"L\'OMBRE a franchi un cap. Les interdépendances entre secteurs sont exploitées. Impacts visibles des citoyens. Réponse formelle attendue dans 30 min."',
                        'injects' => ['Transport ✗ SNTRI hors ligne — 180 bus — Aéroport manuel', 'Finance ✗ 340k transactions bloquées — 3 banques en mode dégradé', 'E-Gov ~ CNI hors ligne — Portails admin perturbés'],
                        'options' => [
                            ['key' => 'A', 'label' => 'Cellule sectorielle / coordination volontaire', 'points' => 5,  'note' => '⚠️ Insuffisant : les silos sont exactement ce qu\'exploite l\'OMBRE.'],
                            ['key' => 'B', 'label' => 'Activation BOUCLIER TN formelle', 'points' => 20, 'note' => '✅ Optimal : ISO 27035 & ANSSI — Crise nationale = gouvernance centralisée.'],
                            ['key' => 'C', 'label' => 'État d\'urgence numérique', 'points' => 12, 'note' => '⚡ Acceptable si justifié — mesures d\'exception proportionnées.'],
                        ],
                        'hint' => 'Point clé : Le prestataire logistique a servi de pivot. Demandez : "Votre institution a-t-elle un protocole interministériel ?"',
                    ],
                ],
                [
                    'index' => 3, 'name' => 'CRISE MÉDIAS', 'tag' => 'PHASE 3',
                    'desc' => 'Pression médiatique & institutionnelle', 'duration_seconds' => 2400,
                    'decision_matrix' => [
                        'context' => '"Journaliste Mosaïque FM en ligne. Influenceur 800K publie fausses données. Briefing Ministère de l\'Intérieur dans 20 min."',
                        'injects' => ['Mosaïque FM demande confirmation cyberattaque nationale', 'Influenceur 800K : données 2M Tunisiens (non confirmé)', 'Briefing urgent Ministère de l\'Intérieur (20 min)'],
                        'options' => [
                            ['key' => 'A', 'label' => 'Communication minimale / silence', 'points' => 5,  'note' => '⚠️ Risque vide informationnel — rumeurs & panique amplifient la crise.'],
                            ['key' => 'B', 'label' => 'Communiqué officiel structuré', 'points' => 20, 'note' => '✅ Optimal : ANSSI Communication de crise — message cadré, proactif, factuels.'],
                            ['key' => 'C', 'label' => 'Conférence de presse immédiate', 'points' => 10, 'note' => '⚡ Acceptable si informations factuelles confirmées — risque sur données incomplètes.'],
                        ],
                        'hint' => 'Demandez : "Avez-vous un porte-parole désigné et des éléments de langage préparés ?"',
                    ],
                ],
                [
                    'index' => 4, 'name' => 'ARBITRAGE', 'tag' => 'PHASE 4',
                    'desc' => 'Décisions stratégiques sous contrainte', 'duration_seconds' => 1200,
                    'decision_matrix' => [
                        'context' => '"Grand Choix : chaque équipe vote la stratégie nationale de réponse finale."',
                        'injects' => ['VOTE NATIONAL : Quelle posture adopter pour répondre à PHANTOM GRID ?'],
                        'options' => [
                            ['key' => 'A', 'label' => 'Défensive — containment total', 'points' => 15, 'note' => 'Isolation complète. Sécurisé mais risque isolement diplomatique.'],
                            ['key' => 'B', 'label' => 'Diplomatique — signalement international', 'points' => 18, 'note' => 'Coordination INTERPOL/partenaires. Bonne pratique souveraine.'],
                            ['key' => 'C', 'label' => 'Coalition — SENTINEL-1 + international', 'points' => 25, 'note' => '✅ Optimal : Réponse souveraine + coalition régionale. Maximum de points.'],
                        ],
                        'hint' => 'Le vote est collectif. Résultat visible sur le dashboard.',
                    ],
                ],
                [
                    'index' => 5, 'name' => 'CLÔTURE', 'tag' => 'FIN',
                    'desc' => 'Restitution & cérémonie des badges', 'duration_seconds' => 600,
                    'decision_matrix' => null,
                ],
            ],
            'vote_options' => [
                ['key' => 'A', 'label' => 'Défensive',   'color' => '#00b4d8'],
                ['key' => 'B', 'label' => 'Diplomatique','color' => '#f4a261'],
                ['key' => 'C', 'label' => 'Coalition',   'color' => '#2dc653'],
            ],
        ];
    }

    // ── SCENARIO 2: ATLAS BREACH ────────────────────────────────────
    // Supply chain attack on government digital infrastructure
    private static function atlasBreach(): array
    {
        return [
            'key'              => 'atlas_breach',
            'title'            => 'Opération ATLAS BREACH',
            'description'      => 'Un acteur étatique a compromis un fournisseur logiciel critique desservant plusieurs ministères. La chaîne d\'approvisionnement numérique est infectée. Réponse coordonnée requise avant la propagation.',
            'difficulty'       => 'Expert',
            'duration_minutes' => 120,
            'attacker_name'    => 'ATLAS COLLECTIVE',
            'attacker_icon'    => '🌐',
            'phantom_messages' => [
                'ATLAS a déjà accès à 12 systèmes ministériels.',
                'La mise à jour que vous avez installée hier — c\'était nous.',
                'Votre chaîne d\'approvisionnement est compromise depuis 90 jours.',
            ],
            'phases' => [
                ['index' => 0, 'name' => 'BRIEFING',     'tag' => 'PHASE 0', 'desc' => 'Découverte de la compromission',             'duration_seconds' => 600],
                ['index' => 1, 'name' => 'INVESTIGATION','tag' => 'PHASE 1', 'desc' => 'Étendue de la compromission inconnue',        'duration_seconds' => 2400],
                ['index' => 2, 'name' => 'CONTAINMENT',  'tag' => 'PHASE 2', 'desc' => 'Isolation et qualification',                 'duration_seconds' => 2400],
                ['index' => 3, 'name' => 'ERADICATION',  'tag' => 'PHASE 3', 'desc' => 'Remédiation et reconstruction de confiance', 'duration_seconds' => 1800],
                ['index' => 4, 'name' => 'RECOVERY',     'tag' => 'PHASE 4', 'desc' => 'Reprise des services & communication',       'duration_seconds' => 1200],
                ['index' => 5, 'name' => 'LESSONS',      'tag' => 'FIN',     'desc' => 'Retour d\'expérience',                       'duration_seconds' => 600],
            ],
            'vote_options' => [
                ['key' => 'A', 'label' => 'Isoler immédiatement', 'color' => '#e63946'],
                ['key' => 'B', 'label' => 'Surveiller discrètement', 'color' => '#f4a261'],
                ['key' => 'C', 'label' => 'Notifier les partenaires', 'color' => '#2dc653'],
            ],
        ];
    }

    // ── SCENARIO 3: GHOST PROTOCOL ──────────────────────────────────
    // Ransomware targeting critical infrastructure OT/IT
    private static function ghostProtocol(): array
    {
        return [
            'key'              => 'ghost_protocol',
            'title'            => 'Opération GHOST PROTOCOL',
            'description'      => 'Un ransomware sophistiqué frappe simultanément les hôpitaux, les aéroports et les centrales électriques. Les attaquants exigent une rançon en crypto-monnaie. Chaque heure de délai aggrave les impacts humains.',
            'difficulty'       => 'Expert',
            'duration_minutes' => 180,
            'attacker_name'    => 'GHOST COLLECTIVE',
            'attacker_icon'    => '👻',
            'phantom_messages' => [
                'Payez dans les 6 heures ou nous publions les données.',
                'Vos hôpitaux sont hors ligne. La vie de vos citoyens a un prix.',
                'Le temps joue contre vous. Avons-nous votre attention maintenant ?',
            ],
            'phases' => [
                ['index' => 0, 'name' => 'ALERTE',      'tag' => 'PHASE 0', 'desc' => 'Premiers rapports de systèmes chiffrés',     'duration_seconds' => 600],
                ['index' => 1, 'name' => 'TRIAGE',      'tag' => 'PHASE 1', 'desc' => 'Identification des systèmes touchés',        'duration_seconds' => 1800],
                ['index' => 2, 'name' => 'CRISE',       'tag' => 'PHASE 2', 'desc' => 'Escalade & impacts sur la population',       'duration_seconds' => 2400],
                ['index' => 3, 'name' => 'NÉGOCIATION', 'tag' => 'PHASE 3', 'desc' => 'Pression & décision de paiement',            'duration_seconds' => 1800],
                ['index' => 4, 'name' => 'RÉCUPÉRATION','tag' => 'PHASE 4', 'desc' => 'Restauration des systèmes critiques',        'duration_seconds' => 2400],
                ['index' => 5, 'name' => 'BILAN',       'tag' => 'FIN',     'desc' => 'Analyse et recommandations',                 'duration_seconds' => 600],
            ],
            'vote_options' => [
                ['key' => 'A', 'label' => 'Ne pas payer',      'color' => '#e63946'],
                ['key' => 'B', 'label' => 'Négocier',          'color' => '#f4a261'],
                ['key' => 'C', 'label' => 'Payer & investiguer','color' => '#8b5cf6'],
            ],
        ];
    }

    // ── SCENARIO 4: CARTHAGE SHIELD 26 ─────────────────────────────
    private static function carthageShield26(): array
    {
        return [
            'key'              => 'carthage_shield_26',
            'title'            => 'Carthage Shield 26',
            'description'      => 'Scenario national 2026: attaque coordonnee multi-secteurs avec role mentor ANCS non-score.',
            'difficulty'       => 'Expert',
            'duration_minutes' => 140,
            'attacker_name'    => 'RANSOMHUB / PHANTOM GRID',
            'attacker_icon'    => '☠️',
            'secret_vote_phases' => [1, 3],
            'teams' => [
                ['type' => 'ancs',      'name' => 'ANCS',      'role_label' => 'Mentorat national',      'color' => '#00b4d8', 'icon' => '🏛️', 'is_scored' => false, 'can_vote' => false, 'badge_eligible' => false, 'show_in_ranking' => false, 'role_mode' => 'mentor'],
                ['type' => 'cert',      'name' => 'CERT',      'role_label' => 'Detection technique',    'color' => '#2dc653', 'icon' => '🔍', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
                ['type' => 'finance',   'name' => 'FINANCE',   'role_label' => 'Secteur bancaire',       'color' => '#f4a261', 'icon' => '🏦', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
                ['type' => 'transport', 'name' => 'TRANSPORT', 'role_label' => 'Mobilite critique',      'color' => '#8b5cf6', 'icon' => '🚆', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
                ['type' => 'egov',      'name' => 'E-GOV',     'role_label' => 'Services citoyens',      'color' => '#fbbf24', 'icon' => '🖥️', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
                ['type' => 'comm',      'name' => 'COMM',      'role_label' => 'Communication de crise', 'color' => '#e63946', 'icon' => '📡', 'is_scored' => true,  'can_vote' => true,  'badge_eligible' => true,  'show_in_ranking' => true,  'role_mode' => 'participant'],
            ],
            'phantom_messages' => [
                'Signal faible confirme. La chaine de confiance est compromise.',
                'Le bruit mediatique est une arme. Gardez le cap.',
                'Le pivot inter-sectoriel est en cours. Vos choix seront traces.',
            ],
            'phases' => [
                ['index' => 0, 'name' => 'PHASE 1', 'tag' => 'REVEIL',      'desc' => 'Phantom Awakening',                    'duration_seconds' => 1800],
                ['index' => 1, 'name' => 'PHASE 2', 'tag' => 'ESCALADE',    'desc' => 'Escalade et choix strategique',       'duration_seconds' => 2400],
                ['index' => 2, 'name' => 'PHASE 3', 'tag' => 'MEDIA',       'desc' => 'Pression mediatique et institutionnelle','duration_seconds' => 2400],
                ['index' => 3, 'name' => 'PHASE 4', 'tag' => 'ARBITRAGE',   'desc' => 'Arbitrage national et riposte',       'duration_seconds' => 1200],
                ['index' => 4, 'name' => 'PHASE 5', 'tag' => 'DEBRIEF',     'desc' => 'Debrief et cloture',                  'duration_seconds' => 600],
            ],
            'vote_options' => [
                ['key' => 'A', 'label' => 'Defensive',   'color' => '#00b4d8'],
                ['key' => 'B', 'label' => 'Diplomatique','color' => '#f4a261'],
                ['key' => 'C', 'label' => 'Coalition',   'color' => '#2dc653'],
            ],
        ];
    }
}
