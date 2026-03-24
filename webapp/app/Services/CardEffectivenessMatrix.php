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
    private const NODE_MAP = [
        'internet' => 'Internet',
        'laptop_dev' => 'Developer Laptop',
        'laptop_ex' => 'Ex-Employee Laptop',
        'laptop_ceo' => 'CEO Laptop',
        'laptop_finance' => 'Finance Laptop',
        'laptops' => 'Employee Laptops',
        'apigw' => 'API Gateway',
        'apigw2' => 'API Gateway v2',
        'aws_ec2' => 'AWS EC2',
        'aws_s3' => 'AWS S3',
        'aws_iam' => 'AWS IAM',
        'vpn' => 'VPN Gateway',
        'rdp' => 'RDP Gateway',
        'firewall_it_ot' => 'IT/OT Firewall',
        'dbprod' => 'DB Prod',
        'dbdev' => 'DB Dev/Test',
        'o365' => 'Office 365',
        'bank' => 'Bank Portal',
        'slack' => 'Slack',
        'jira' => 'Jira',
        'vault' => 'HashiCorp Vault',
        'backup' => 'Veeam Backup',
        'dns_c2' => 'DNS C2',
        'ad' => 'Domain Controller',
        'jenkins' => 'Jenkins CI',
        'gitlab' => 'GitLab CI',
        'github' => 'GitHub Enterprise',
        'npm' => 'npm Registry',
        'docker' => 'Docker Hub',
        'k8s' => 'K8s Cluster',
        'scada' => 'SCADA Server',
        'plc' => 'PLC Controllers',
        'hmi' => 'HMI Panel',
        'sis' => 'Safety System (SIS)',
    ];

    // ── Red Team Card Effectiveness ─────────────────────────────────
    private const RED_MATRIX = [
        // Original v1 cards
        'GitHub secret scan' => [
            100 => ['github'],
            80  => ['vault'],
            50  => ['jenkins', 'gitlab'],
        ],
        'Supply chain attack' => [
            100 => ['npm', 'docker'],
            80  => ['jenkins', 'gitlab', 'k8s'],
            50  => ['aws_ec2'],
        ],
        'Phishing développeur' => [
            100 => ['slack', 'jira', 'laptop_dev'],
            80  => ['github'],
            50  => ['jenkins', 'vault'],
        ],
        'Pivot via CI/CD' => [
            100 => ['jenkins', 'gitlab'],
            80  => ['docker', 'k8s'],
            50  => ['aws_ec2'],
        ],
        'Exfiltration S3' => [
            100 => ['aws_s3', 'aws_iam'],
            80  => ['dbprod'],
            50  => ['aws_ec2'],
        ],
        'Crypto-miner K8s' => [
            100 => ['k8s'],
            80  => ['aws_ec2'],
            50  => ['docker'],
        ],
        'Ransomware partiel' => [
            100 => ['laptops', 'dbdev', 'github', 'laptop_dev'],
            80  => ['dbprod'],
            50  => ['jenkins', 'gitlab'],
        ],
        'Defacement' => [
            100 => ['apigw', 'apigw2', 'internet'],
            80  => [],
            50  => [],
        ],
        'CRON persistance' => [
            100 => ['k8s', 'aws_ec2', 'ad'],
            80  => ['jenkins', 'gitlab'],
            50  => [],
        ],
        'IAM backdoor' => [
            100 => ['aws_iam', 'aws_ec2', 'vault'],
            80  => ['aws_s3'],
            50  => [],
        ],
        'Credential stuffing' => [
            100 => ['apigw', 'apigw2', 'o365', 'slack'],
            80  => ['github', 'vpn', 'rdp'],
            50  => ['jira'],
        ],
        'Lateral movement' => [
            100 => ['k8s', 'docker', 'aws_ec2', 'dbprod', 'ad', 'laptops'],
            80  => ['jenkins', 'gitlab', 'vault'],
            50  => ['dbdev', 'github'],
        ],
        // v2 — BEC Attack
        'CEO Impersonation' => [
            100 => ['o365', 'laptop_finance', 'bank'],
            80  => ['slack', 'laptop_ceo'],
            50  => [],
        ],
        'Invoice fraud' => [
            100 => ['bank', 'laptop_finance'],
            80  => ['o365', 'slack'],
            50  => ['jira'],
        ],
        'Account takeover email' => [
            100 => ['o365'],
            80  => ['slack', 'jira', 'github'],
            50  => ['vault'],
        ],
        // v2 — Ransomware
        'Déploiement ransomware' => [
            100 => ['dbprod', 'ad', 'laptops', 'backup'],
            80  => ['k8s', 'docker'],
            50  => ['aws_ec2', 'jenkins', 'gitlab'],
        ],
        'Double extorsion' => [
            100 => ['dbprod', 'aws_s3', 'backup'],
            80  => ['aws_ec2', 'laptops'],
            50  => ['github'],
        ],
        'Destruction backups' => [
            100 => ['backup', 'aws_s3'],
            80  => ['ad'],
            50  => ['dbprod'],
        ],
        // v2 — APT
        'Custom malware (APT)' => [
            100 => ['dns_c2', 'k8s', 'aws_ec2', 'github'],
            80  => ['jenkins', 'gitlab', 'docker'],
            50  => ['vault', 'apigw'],
        ],
        'Living off the land' => [
            100 => ['k8s', 'aws_ec2', 'laptop_ex', 'ad'],
            80  => ['jenkins', 'gitlab', 'github'],
            50  => ['vault', 'dbprod'],
        ],
        'Data staging' => [
            100 => ['dbprod', 'github', 'aws_s3', 'aws_ec2'],
            80  => ['vault', 'laptop_ceo'],
            50  => ['jira'],
        ],
        // v2 — Industrial
        'Exploitation SCADA' => [
            100 => ['scada', 'firewall_it_ot'],
            80  => ['plc', 'hmi'],
            50  => ['sis'],
        ],
        'Reprogrammation PLC' => [
            100 => ['plc'],
            80  => ['scada'],
            50  => ['hmi', 'sis'],
        ],
        'Bypass Safety Systems' => [
            100 => ['sis'],
            80  => ['plc', 'scada'],
            50  => ['hmi'],
        ],
    ];

    // ── Blue Team Card Effectiveness ────────────────────────────────
    private const BLUE_MATRIX = [
        // Original v1 cards
        'Audit de code' => [
            100 => ['github'],
            80  => ['jenkins', 'gitlab'],
            50  => ['docker', 'npm', 'laptop_dev'],
        ],
        'Rotation des secrets' => [
            100 => ['vault', 'aws_iam'],
            80  => ['github', 'aws_ec2'],
            50  => ['jenkins', 'gitlab'],
        ],
        'Alerte SIEM' => [
            100 => ['apigw', 'apigw2', 'k8s', 'aws_ec2', 'dbprod', 'github', 'jenkins', 'gitlab', 'docker', 'npm', 'vault', 'slack', 'jira', 'scada', 'plc', 'hmi', 'sis', 'ad', 'vpn', 'rdp', 'firewall_it_ot'],
            80  => [],
            50  => [],
        ],
        'WAF renforcé' => [
            100 => ['apigw', 'apigw2'],
            80  => [],
            50  => [],
        ],
        'Isolation CI/CD' => [
            100 => ['jenkins', 'gitlab'],
            80  => ['docker', 'github'],
            50  => ['aws_ec2'],
        ],
        'Restauration snapshot' => [
            100 => ['dbprod', 'aws_ec2', 'k8s'],
            80  => ['dbdev', 'aws_s3'],
            50  => [],
        ],
        'Investigation forensique' => [
            100 => ['apigw', 'apigw2', 'k8s', 'aws_ec2', 'aws_iam', 'dbprod', 'github', 'jenkins', 'gitlab', 'docker', 'npm', 'vault', 'scada', 'plc', 'hmi', 'sis', 'ad', 'vpn', 'rdp', 'laptops', 'laptop_ceo', 'laptop_dev', 'laptop_ex', 'laptop_finance', 'dns_c2'],
            80  => [],
            50  => [],
        ],
        'Notification CNIL' => [
            100 => [],
            80  => [],
            50  => [],
        ],
        'Patch zero-day' => [
            100 => ['apigw', 'apigw2', 'k8s', 'vpn', 'rdp', 'docker', 'npm'],
            80  => ['aws_ec2', 'github', 'jenkins', 'gitlab'],
            50  => [],
        ],
        'Honeypot' => [
            100 => [],
            80  => [],
            50  => [],
        ],
        'Threat hunting proactif' => [
            100 => ['apigw', 'apigw2', 'k8s', 'aws_ec2', 'aws_iam', 'dbprod', 'github', 'jenkins', 'gitlab', 'docker', 'npm', 'vault', 'scada', 'plc', 'hmi', 'sis', 'ad', 'vpn', 'rdp', 'laptops', 'dns_c2'],
            80  => [],
            50  => [],
        ],
        'Micro-segmentation' => [
            100 => ['k8s', 'firewall_it_ot', 'vpn'],
            80  => ['aws_ec2', 'ad'],
            50  => ['dbprod'],
        ],
        // v2 — BEC Defense
        'DMARC/SPF enforcement' => [
            100 => ['o365', 'slack'],
            80  => ['jira'],
            50  => ['apigw', 'apigw2'],
        ],
        'Vérification paiement' => [
            100 => ['bank', 'laptop_finance'],
            80  => ['o365', 'slack'],
            50  => [],
        ],
        'Simulation phishing' => [
            100 => ['o365', 'slack', 'jira', 'laptop_ceo', 'laptops'],
            80  => ['github'],
            50  => [],
        ],
        // v2 — Ransomware Defense
        'Backup offline vérifié' => [
            100 => ['backup', 'dbprod', 'ad', 'laptops'],
            80  => ['github', 'dbdev', 'aws_ec2'],
            50  => ['docker'],
        ],
        'Confinement réseau' => [
            100 => ['ad', 'k8s', 'docker', 'rdp'],
            80  => ['aws_ec2', 'dbprod'],
            50  => ['jenkins', 'gitlab', 'laptops'],
        ],
        'Analyse cryptographique' => [
            100 => ['dbprod', 'ad', 'backup'],
            80  => ['github', 'laptops'],
            50  => ['aws_ec2'],
        ],
        // v2 — APT Defense
        'Forensique mémoire' => [
            100 => ['k8s', 'aws_ec2', 'dns_c2', 'github', 'ad', 'vpn'],
            80  => ['jenkins', 'gitlab', 'docker'],
            50  => ['vault', 'dbprod'],
        ],
        'Corrélation threat intel' => [
            100 => ['dns_c2', 'apigw', 'k8s', 'aws_ec2', 'dbprod', 'ad', 'vpn', 'rdp'],
            80  => [],
            50  => [],
        ],
        'Baseline réseau' => [
            100 => ['dns_c2', 'k8s', 'aws_ec2', 'apigw', 'apigw2', 'vpn', 'rdp', 'firewall_it_ot'],
            80  => ['jenkins', 'gitlab', 'docker'],
            50  => ['dbprod'],
        ],
        // v2 — Industrial Defense
        'Isolation réseau OT' => [
            100 => ['firewall_it_ot', 'scada'],
            80  => ['plc', 'sis'],
            50  => ['apigw', 'apigw2'],
        ],
        'Vérification firmware' => [
            100 => ['plc', 'sis'],
            80  => ['scada', 'hmi'],
            50  => [],
        ],
        'Test processus physique' => [
            100 => ['sis', 'plc'],
            80  => ['hmi', 'scada'],
            50  => [],
        ],
    ];

    /**
     * Calculate effectiveness:
     * returns ['score' => int, 'log' => string, 'effectiveness' => int]
     */
    public static function calculate(string $cardName, string $targetNodeName, ?int $scenario = null): array
    {
        $targetId = array_search($targetNodeName, self::NODE_MAP) ?: $targetNodeName;

        $isRed = array_key_exists($cardName, self::RED_MATRIX);
        $isBlue = array_key_exists($cardName, self::BLUE_MATRIX);

        // Calculate base points
        $effectiveness = 0;
        if ($isRed) {
            $effectiveness = self::getEffectiveness(self::RED_MATRIX[$cardName], $targetId);
        } elseif ($isBlue) {
            // Check if card is universal (empty lists = 100% everywhere)
            $matrix = self::BLUE_MATRIX[$cardName];
            if (empty($matrix[100]) && empty($matrix[80]) && empty($matrix[50])) {
                $effectiveness = 100;
            } else {
                $effectiveness = self::getEffectiveness($matrix, $targetId);
            }
        }

        // Action not applicable = 0 pts
        if ($effectiveness === 0) {
            return [
                'score'         => 0,
                'log'           => "Action inefficace sur ce système (0%).",
                'effectiveness' => 0,
            ];
        }

        // Base score mapped from DB is usually handled upstream, here we return multipliers.
        // Actually, we return the *percentage* to multiply the card's base score by, 
        // AND handle critical path bonus (+20%).

        // Critical path bonus logic based on scenario
        $criticalPaths = [
            1 => ['apigw', 'jenkins', 'github', 'vault', 'aws_ec2'],
            2 => ['apigw', 'npm', 'docker', 'gitlab', 'k8s', 'aws_ec2'],
            3 => ['vpn', 'laptop_ex', 'vault', 'github', 'aws_iam', 'aws_ec2'],
            4 => ['apigw2', 'k8s', 'dbprod', 'aws_ec2', 'docker', 'vault'],
            5 => ['o365', 'laptop_ceo', 'laptop_finance', 'bank'],
            6 => ['rdp', 'ad', 'dbprod', 'backup', 'laptops'],
            7 => ['dns_c2', 'apigw', 'k8s', 'aws_ec2', 'vault', 'dbprod'],
            8 => ['apigw', 'firewall_it_ot', 'scada', 'plc', 'sis'],
        ];

        $bonusMsg = "";
        $scoreMultiplier = $effectiveness / 100; // 1.0, 0.8, 0.5

        if ($scenario && isset($criticalPaths[$scenario]) && in_array($targetId, $criticalPaths[$scenario])) {
            $scoreMultiplier += 0.20; // +20%
            $bonusMsg = " (+20% bonus cible critique)";
        }

        return [
            'scoreMultiplier' => $scoreMultiplier,
            'effectiveness'   => $effectiveness,
            'log'             => "Efficacité : {$effectiveness}%" . $bonusMsg,
        ];
    }

    private static function getEffectiveness(array $cardMatrix, string $targetId): int
    {
        if (in_array($targetId, $cardMatrix[100] ?? [])) return 100;
        if (in_array($targetId, $cardMatrix[80]  ?? [])) return 80;
        if (in_array($targetId, $cardMatrix[50]  ?? [])) return 50;
        return 0;
    }
}
