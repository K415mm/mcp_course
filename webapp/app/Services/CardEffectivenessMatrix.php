<?php

namespace App\Services;

/**
 * Card → Infrastructure Node effectiveness matrix.
 *
 * For each card, defines which nodes give 100%, 80%, 50%, or 0% of the card's base points.
 * Scenario critical-path nodes add a 20% bonus on top.
 */
class CardEffectivenessMatrix
{
    // ── Red Team Card Effectiveness ─────────────────────────────────
    private const RED_MATRIX = [
        'GitHub secret scan' => [
            100 => ['github'],
            80  => ['vault'],
            50  => ['cicd'],
        ],
        'Supply chain attack' => [
            100 => ['npm', 'docker'],
            80  => ['cicd', 'k8s'],
            50  => ['aws'],
        ],
        'Phishing développeur' => [
            100 => ['slack', 'jira'],
            80  => ['github'],
            50  => ['cicd', 'vault'],
        ],
        'Pivot via CI/CD' => [
            100 => ['cicd'],
            80  => ['docker', 'k8s'],
            50  => ['aws'],
        ],
        'Exfiltration S3' => [
            100 => ['aws', 'dbprod'],
            80  => ['dbdev'],
            50  => [],
        ],
        'Crypto-miner K8s' => [
            100 => ['k8s'],
            80  => ['aws'],
            50  => ['docker'],
        ],
        'Ransomware partiel' => [
            100 => ['dbdev', 'github'],
            80  => ['dbprod'],
            50  => ['cicd'],
        ],
        'Defacement' => [
            100 => ['apigw'],
            80  => [],
            50  => [],
        ],
        'CRON persistance' => [
            100 => ['k8s', 'aws'],
            80  => ['cicd'],
            50  => [],
        ],
        'IAM backdoor' => [
            100 => ['aws', 'vault'],
            80  => ['k8s'],
            50  => [],
        ],
        'Credential stuffing' => [
            100 => ['apigw', 'slack'],
            80  => ['github'],
            50  => ['jira'],
        ],
        'Lateral movement' => [
            100 => ['k8s', 'docker', 'aws', 'dbprod'],
            80  => ['cicd', 'vault'],
            50  => ['dbdev', 'github'],
        ],
    ];

    // ── Blue Team Card Effectiveness ────────────────────────────────
    private const BLUE_MATRIX = [
        'Audit de code' => [
            100 => ['github'],
            80  => ['cicd'],
            50  => ['docker', 'npm'],
        ],
        'Rotation des secrets' => [
            100 => ['vault'],
            80  => ['github', 'aws'],
            50  => ['cicd'],
        ],
        'Alerte SIEM' => [
            100 => ['apigw', 'k8s', 'aws', 'dbprod', 'github', 'cicd', 'docker', 'npm', 'dbdev', 'vault', 'slack', 'jira'],
            80  => [],
            50  => [],
        ],
        'WAF renforcé' => [
            100 => ['apigw'],
            80  => [],
            50  => [],
        ],
        'Isolation CI/CD' => [
            100 => ['cicd'],
            80  => ['docker'],
            50  => [],
        ],
        'Restauration snapshot' => [
            100 => ['dbprod'],
            80  => ['dbdev'],
            50  => [],
        ],
        'Investigation forensique' => [
            100 => ['apigw', 'k8s', 'aws', 'dbprod', 'github', 'cicd', 'docker', 'npm', 'dbdev', 'vault', 'slack', 'jira'],
            80  => [],
            50  => [],
        ],
        'Notification CNIL' => [
            // No target needed — regulatory action
            100 => [],
            80  => [],
            50  => [],
        ],
        'Patch zero-day' => [
            100 => ['apigw', 'k8s'],
            80  => ['aws'],
            50  => [],
        ],
        'Honeypot' => [
            // Decoy — no target needed
            100 => [],
            80  => [],
            50  => [],
        ],
        'Threat hunting proactif' => [
            100 => ['apigw', 'k8s', 'aws', 'dbprod', 'github', 'cicd', 'docker', 'npm', 'dbdev', 'vault', 'slack', 'jira'],
            80  => [],
            50  => [],
        ],
        'Micro-segmentation' => [
            100 => ['k8s'],
            80  => ['aws'],
            50  => ['dbprod'],
        ],
    ];

    // ── Scenario Critical Path Nodes (+20% bonus) ───────────────────
    private const SCENARIO_CRITICAL = [
        1 => ['github', 'cicd', 'aws'],           // NightOwl
        2 => ['npm', 'docker', 'k8s'],            // Supply Chain
        3 => ['vault', 'github', 'aws'],           // Insider Threat
        4 => ['apigw', 'k8s', 'dbprod'],           // Zero-Day API
    ];

    // ── Node name → ID mapping ──────────────────────────────────────
    private const NODE_MAP = [
        'GitHub Repos'        => 'github',
        'CI/CD Pipeline'      => 'cicd',
        'Docker Registry'     => 'docker',
        'npm Registry'        => 'npm',
        'Kubernetes Cluster'  => 'k8s',
        'AWS Production'      => 'aws',
        'DB Production'       => 'dbprod',
        'DB Dev/Test'         => 'dbdev',
        'API Gateway'         => 'apigw',
        'Secrets Vault'       => 'vault',
        'Slack/Comms'         => 'slack',
        'Jira/Tickets'        => 'jira',
        'Internet'            => 'internet',
    ];

    /**
     * Calculate points for playing a card on a target system.
     *
     * @return array{points: int, effectiveness: int, isCriticalPath: bool, message: string}
     */
    public static function calculate(string $cardName, string $cardType, int $basePoints, ?string $targetSystem, int $scenario): array
    {
        // Cards with no target requirement get full points
        if (!$targetSystem) {
            $noTargetCards = ['Notification CNIL', 'Honeypot', 'Alerte SIEM', 'Investigation forensique', 'Threat hunting proactif'];
            if (in_array($cardName, $noTargetCards)) {
                return [
                    'points'         => $basePoints,
                    'effectiveness'  => 100,
                    'isCriticalPath' => false,
                    'message'        => 'Action globale — plein effet',
                ];
            }
            // If card normally needs a target but none provided → 50%
            return [
                'points'         => (int) round($basePoints * 0.5),
                'effectiveness'  => 50,
                'isCriticalPath' => false,
                'message'        => 'Aucune cible — efficacité réduite (50%)',
            ];
        }

        $nodeId = self::NODE_MAP[$targetSystem] ?? null;
        if (!$nodeId) {
            return [
                'points'         => 0,
                'effectiveness'  => 0,
                'isCriticalPath' => false,
                'message'        => 'Cible invalide',
            ];
        }

        $matrix = $cardType === 'red' ? self::RED_MATRIX : self::BLUE_MATRIX;
        $cardMap = $matrix[$cardName] ?? null;

        if (!$cardMap) {
            // Resource cards get flat points
            return [
                'points'         => $basePoints,
                'effectiveness'  => 100,
                'isCriticalPath' => false,
                'message'        => 'Carte ressource — effet fixe',
            ];
        }

        // Determine base effectiveness
        $effectiveness = 0;
        if (in_array($nodeId, $cardMap[100] ?? [])) {
            $effectiveness = 100;
        } elseif (in_array($nodeId, $cardMap[80] ?? [])) {
            $effectiveness = 80;
        } elseif (in_array($nodeId, $cardMap[50] ?? [])) {
            $effectiveness = 50;
        }

        if ($effectiveness === 0) {
            return [
                'points'         => 0,
                'effectiveness'  => 0,
                'isCriticalPath' => false,
                'message'        => 'Cette carte n\'a aucun effet sur ' . $targetSystem,
            ];
        }

        // Scenario critical path bonus (+20%)
        $criticalNodes = self::SCENARIO_CRITICAL[$scenario] ?? [];
        $isCritical = in_array($nodeId, $criticalNodes);
        $finalEffectiveness = min(100, $effectiveness + ($isCritical ? 20 : 0));

        $points = (int) round($basePoints * ($finalEffectiveness / 100));

        $label = match($effectiveness) {
            100 => 'Cible optimale',
            80  => 'Cible secondaire (80%)',
            50  => 'Cible marginale (50%)',
            default => '',
        };
        if ($isCritical) $label .= ' + Bonus chemin critique (+20%)';

        return [
            'points'         => $points,
            'effectiveness'  => $finalEffectiveness,
            'isCriticalPath' => $isCritical,
            'message'        => $label,
        ];
    }

    /**
     * Get effectiveness preview for all nodes for a given card.
     * Used by the frontend to show which nodes are best targets.
     */
    public static function getCardTargetPreview(string $cardName, string $cardType, int $basePoints, int $scenario): array
    {
        $results = [];
        foreach (self::NODE_MAP as $nodeName => $nodeId) {
            if ($nodeId === 'internet') continue;
            $calc = self::calculate($cardName, $cardType, $basePoints, $nodeName, $scenario);
            $results[$nodeId] = [
                'nodeName'      => $nodeName,
                'effectiveness' => $calc['effectiveness'],
                'points'        => $calc['points'],
                'isCritical'    => $calc['isCriticalPath'],
            ];
        }
        return $results;
    }

    /**
     * Get node ID from display name.
     */
    public static function nodeId(string $nodeName): ?string
    {
        return self::NODE_MAP[$nodeName] ?? null;
    }

    /**
     * Get all node IDs.
     */
    public static function nodeMap(): array
    {
        return self::NODE_MAP;
    }
}
