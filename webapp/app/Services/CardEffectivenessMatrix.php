<?php

namespace App\Services;

/**
 * Card → Infrastructure Node effectiveness matrix.
 *
 * For each card, defines which nodes give 100%, 80%, 50%, or 0% of the card's base points.
 * Scaled for Enterprise Architecture with 75 distinct nodes.
 */
class CardEffectivenessMatrix
{
    private const NODE_MAP = [
        'internet' => 'Internet',
        'dns_ext' => 'DNS External',
        'github' => 'GitHub Cloud',
        'o365' => 'Office 365 Cloud',
        'bank' => 'Bank Portal API',
        'supplier' => 'Supplier Portal',
        'dns_c2' => 'Malicious C2 (DNS)',
        
        'apigw' => 'API Gateway',
        'apigw2' => 'API Gateway v2',
        'apigw_legacy' => 'Legacy API Gateway',
        'vpn' => 'VPN Gateway',
        'waf' => 'WAF',
        'seg' => 'Secure Email Gateway',
        'rdp_jump' => 'RDP Jump Host',
        'web_front' => 'Public Website',
        'fw_it_ot' => 'IT/OT Firewall',
        'linux_jump' => 'Linux Jump Host',
        'ot_jump' => 'OT Jump Host',

        'ad_primary' => 'Primary Active Directory',
        'ad_sec' => 'Secondary AD',
        'file_svr' => 'File Server',
        'nas' => 'NAS Storage',
        'erp' => 'ERP System',
        'intranet' => 'Intranet Portal',
        'wsus' => 'WSUS Server',
        'siem' => 'Splunk SIEM',
        'edr' => 'EDR Console',
        'pam' => 'PAM Solution',
        'vcenter' => 'vCenter Server',
        'admin_portal' => 'Admin Portal',
        'email_svr' => 'Email Server',

        'laptop_dev_lead' => 'Lead Dev Laptop',
        'laptops_dev' => 'Dev Laptops',
        'laptop_pm' => 'PM Laptop',
        'laptops_ops' => 'Ops Laptops',
        'laptop_admin' => 'Admin Laptop',
        'laptop_ex' => 'Ex-Employee Laptop',
        'laptop_ceo' => 'CEO Laptop',
        'mobile_ceo' => 'CEO Mobile Device',
        'laptop_cfo' => 'CFO Laptop',
        'laptops_finance' => 'Finance Laptops',
        'laptops_hr' => 'HR Laptops',
        'laptops_sales' => 'Sales Laptops',
        'laptops_staff' => 'Staff Laptops',
        'laptops_eng' => 'Engineering Laptops',
        'ot_eng_ws' => 'OT Engineering WorkStation',

        'jenkins_master' => 'Jenkins Master',
        'jenkins_workers' => 'Jenkins Workers',
        'gitlab' => 'GitLab CI',
        'github_ent' => 'GitHub Enterprise',
        'npm' => 'npm Registry',
        'docker_hub' => 'Docker Hub',
        'docker_reg' => 'Internal Docker Registry',
        'vault' => 'HashiCorp Vault',
        'nexus' => 'Nexus Repo',
        'sonar' => 'SonarQube',

        'k8s_control' => 'K8s Control Plane',
        'k8s_workers' => 'K8s Worker Nodes',
        'aws_ec2' => 'AWS EC2 Fleet',
        'aws_iam' => 'AWS IAM',
        'aws_s3' => 'AWS S3 Buckets',
        'aws_glacier' => 'AWS Glacier Backups',

        'db_prod' => 'Production DB',
        'db_dev' => 'Dev/Test DB',
        'elastic' => 'Elasticsearch Cluster',
        'data_lake' => 'Data Lake',
        'redis' => 'Redis Cache',
        'veeam' => 'Veeam Backup Server',
        'offline_backup' => 'Offline Backup Infrastructure',

        'slack' => 'Slack',
        'jira' => 'Jira',

        'historian' => 'Data Historian',
        'scada_master' => 'SCADA Master',
        'scada_standby' => 'SCADA Standby',
        'plc_assembly' => 'Assembly PLC',
        'plc_cooling' => 'Cooling PLC',
        'hmi_main' => 'Main HMI Panel',
        'sis' => 'Safety Instrumented System (SIS)'
    ];

    private const RED_MATRIX = [
        // v1 Base
        'GitHub secret scan' => [
            100 => ['github', 'github_ent'],
            80  => ['vault', 'pam'],
            50  => ['jenkins_master', 'gitlab'],
        ],
        'Supply chain attack' => [
            100 => ['npm', 'docker_hub'],
            80  => ['jenkins_master', 'nexus', 'docker_reg'],
            50  => ['aws_ec2'],
        ],
        'Phishing développeur' => [
            100 => ['slack', 'jira', 'laptop_dev_lead', 'laptops_dev'],
            80  => ['github_ent', 'email_svr'],
            50  => ['jenkins_master', 'gitlab'],
        ],
        'Pivot via CI/CD' => [
            100 => ['jenkins_workers', 'jenkins_master', 'gitlab'],
            80  => ['docker_reg', 'k8s_control'],
            50  => ['aws_ec2'],
        ],
        'Exfiltration S3' => [
            100 => ['aws_s3', 'aws_iam'],
            80  => ['db_prod'],
            50  => ['aws_ec2', 'data_lake'],
        ],
        'Crypto-miner K8s' => [
            100 => ['k8s_workers', 'k8s_control'],
            80  => ['aws_ec2'],
            50  => ['docker_reg'],
        ],
        'Ransomware partiel' => [
            100 => ['laptops_staff', 'file_svr', 'nas', 'laptops_sales'],
            80  => ['db_prod', 'vcenter'],
            50  => ['jenkins_master'],
        ],
        'Defacement' => [
            100 => ['apigw', 'apigw2', 'web_front', 'apigw_legacy'],
            80  => [],
            50  => [],
        ],
        'CRON persistance' => [
            100 => ['k8s_workers', 'aws_ec2', 'ad_primary', 'linux_jump'],
            80  => ['jenkins_master', 'db_prod'],
            50  => [],
        ],
        'IAM backdoor' => [
            100 => ['aws_iam', 'aws_ec2', 'vault', 'pam'],
            80  => ['aws_s3'],
            50  => ['k8s_control'],
        ],
        'Credential stuffing' => [
            100 => ['apigw', 'apigw2', 'o365', 'slack', 'vpn'],
            80  => ['github_ent', 'rdp_jump'],
            50  => ['intranet', 'jira'],
        ],
        'Lateral movement' => [
            100 => ['ad_primary', 'vcenter', 'k8s_control', 'aws_ec2', 'db_prod'],
            80  => ['vault', 'jenkins_master', 'file_svr', 'erp'],
            50  => ['db_dev', 'laptops_dev'],
        ],
        // v2 BEC
        'CEO Impersonation' => [
            100 => ['o365', 'laptops_finance', 'laptop_cfo', 'bank'],
            80  => ['slack', 'laptop_ceo', 'mobile_ceo'],
            50  => ['jira'],
        ],
        'Invoice fraud' => [
            100 => ['bank', 'laptops_finance', 'laptop_cfo', 'supplier'],
            80  => ['o365', 'slack'],
            50  => ['erp'],
        ],
        'Account takeover email' => [
            100 => ['o365', 'email_svr'],
            80  => ['slack', 'jira', 'github_ent'],
            50  => ['vault', 'vpn'],
        ],
        // v2 Ransomware
        'Déploiement ransomware' => [
            100 => ['db_prod', 'ad_primary', 'laptops_hr', 'laptops_finance', 'vcenter'],
            80  => ['k8s_control', 'k8s_workers'],
            50  => ['aws_ec2', 'jenkins_master'],
        ],
        'Double extorsion' => [
            100 => ['db_prod', 'file_svr', 'nas', 'aws_s3'],
            80  => ['aws_ec2', 'laptops_sales'],
            50  => ['github_ent'],
        ],
        'Destruction backups' => [
            100 => ['veeam', 'aws_s3', 'aws_glacier'],
            80  => ['ad_primary', 'vcenter'],
            50  => ['offline_backup', 'db_prod'],
        ],
        // v2 APT
        'Custom malware (APT)' => [
            100 => ['dns_c2', 'k8s_control', 'aws_ec2', 'github_ent', 'nexus'],
            80  => ['jenkins_master', 'docker_reg'],
            50  => ['vault', 'apigw'],
        ],
        'Living off the land' => [
            100 => ['k8s_workers', 'aws_ec2', 'linux_jump', 'ad_primary'],
            80  => ['jenkins_master', 'gitlab'],
            50  => ['vault', 'db_prod'],
        ],
        'Data staging' => [
            100 => ['db_prod', 'github_ent', 'aws_s3', 'aws_ec2', 'data_lake'],
            80  => ['vault', 'nas'],
            50  => ['jira', 'elastic'],
        ],
        // v2 Industrial
        'Exploitation SCADA' => [
            100 => ['scada_master', 'fw_it_ot'],
            80  => ['scada_standby', 'historian'],
            50  => ['plc_assembly', 'plc_cooling'],
        ],
        'Reprogrammation PLC' => [
            100 => ['plc_assembly', 'plc_cooling'],
            80  => ['scada_master', 'ot_eng_ws'],
            50  => ['hmi_main', 'sis'],
        ],
        'Bypass Safety Systems' => [
            100 => ['sis'],
            80  => ['plc_cooling', 'plc_assembly'],
            50  => ['hmi_main'],
        ],
    ];

    private const BLUE_MATRIX = [
        // v1 Base
        'Audit de code' => [
            100 => ['github_ent', 'github', 'sonar'],
            80  => ['jenkins_master', 'gitlab'],
            50  => ['npm', 'docker_hub', 'laptops_dev', 'laptop_dev_lead'],
        ],
        'Rotation des secrets' => [
            100 => ['vault', 'aws_iam', 'pam'],
            80  => ['github_ent', 'aws_ec2', 'k8s_control'],
            50  => ['jenkins_master', 'gitlab'],
        ],
        'Alerte SIEM' => [
            100 => ['apigw', 'apigw2', 'apigw_legacy', 'k8s_control', 'aws_ec2', 'db_prod', 'github_ent', 'jenkins_master', 'vault', 'slack', 'jira', 'scada_master', 'plc_assembly', 'plc_cooling', 'hmi_main', 'sis', 'ad_primary', 'vpn', 'rdp_jump', 'fw_it_ot', 'siem', 'edr'],
            80  => [],
            50  => [],
        ],
        'WAF renforcé' => [
            100 => ['apigw', 'apigw2', 'apigw_legacy', 'waf', 'web_front'],
            80  => [],
            50  => [],
        ],
        'Isolation CI/CD' => [
            100 => ['jenkins_master', 'gitlab', 'nexus'],
            80  => ['docker_reg', 'github_ent', 'jenkins_workers'],
            50  => ['aws_ec2'],
        ],
        'Restauration snapshot' => [
            100 => ['db_prod', 'vcenter', 'aws_ec2', 'k8s_control'],
            80  => ['db_dev', 'aws_s3', 'nas'],
            50  => [],
        ],
        'Investigation forensique' => [
            100 => ['apigw', 'apigw2', 'k8s_control', 'aws_ec2', 'aws_iam', 'db_prod', 'github_ent', 'jenkins_master', 'vault', 'scada_master', 'ad_primary', 'vpn', 'rdp_jump', 'laptops_dev', 'laptop_ceo', 'laptop_ex', 'laptop_cfo', 'dns_c2', 'siem', 'edr', 'pam', 'vcenter'],
            80  => [],
            50  => [],
        ],
        'Notification CNIL' => [ 100 => [], 80 => [], 50 => [], ],
        'Patch zero-day' => [
            100 => ['apigw', 'apigw2', 'k8s_control', 'vpn', 'rdp_jump', 'docker_reg', 'wsus'],
            80  => ['aws_ec2', 'github_ent', 'jenkins_master', 'vcenter'],
            50  => [],
        ],
        'Honeypot' => [ 100 => [], 80 => [], 50 => [], ],
        'Threat hunting proactif' => [
            100 => ['apigw', 'apigw2', 'k8s_control', 'aws_iam', 'aws_ec2', 'db_prod', 'vault', 'ad_primary', 'vpn', 'rdp_jump', 'dns_c2', 'siem', 'edr', 'pam', 'linux_jump', 'ot_jump'],
            80  => [],
            50  => [],
        ],
        'Micro-segmentation' => [
            100 => ['k8s_control', 'fw_it_ot', 'vpn', 'waf'],
            80  => ['aws_ec2', 'ad_primary', 'vcenter'],
            50  => ['db_prod'],
        ],
        // v2 BEC Def
        'DMARC/SPF enforcement' => [
            100 => ['o365', 'email_svr', 'seg'],
            80  => ['slack', 'jira'],
            50  => ['apigw'],
        ],
        'Vérification paiement' => [
            100 => ['bank', 'laptop_cfo', 'laptops_finance', 'supplier'],
            80  => ['o365', 'slack', 'erp'],
            50  => [],
        ],
        'Simulation phishing' => [
            100 => ['o365', 'email_svr', 'slack', 'laptop_ceo', 'laptops_staff', 'laptops_sales'],
            80  => ['github_ent', 'jira'],
            50  => [],
        ],
        // v2 Ransomware Def
        'Backup offline vérifié' => [
            100 => ['offline_backup', 'aws_glacier', 'veeam'],
            80  => ['db_prod', 'ad_primary', 'file_svr'],
            50  => ['aws_s3', 'erp'],
        ],
        'Confinement réseau' => [
            100 => ['ad_primary', 'vcenter', 'rdp_jump', 'fw_it_ot', 'linux_jump'],
            80  => ['aws_ec2', 'db_prod', 'k8s_control'],
            50  => ['jenkins_master', 'laptops_staff'],
        ],
        'Analyse cryptographique' => [
            100 => ['db_prod', 'ad_primary', 'veeam'],
            80  => ['file_svr', 'nas', 'aws_ec2'],
            50  => [],
        ],
        // v2 APT Def
        'Forensique mémoire' => [
            100 => ['k8s_control', 'aws_ec2', 'dns_c2', 'ad_primary', 'vpn', 'linux_jump'],
            80  => ['jenkins_master', 'gitlab', 'docker_reg'],
            50  => ['vault', 'db_prod'],
        ],
        'Corrélation threat intel' => [
            100 => ['dns_c2', 'siem', 'edr', 'apigw', 'aws_ec2', 'ad_primary', 'vpn'],
            80  => [],
            50  => [],
        ],
        'Baseline réseau' => [
            100 => ['dns_c2', 'k8s_control', 'aws_ec2', 'apigw', 'vpn', 'rdp_jump', 'fw_it_ot', 'siem'],
            80  => ['jenkins_master', 'docker_reg'],
            50  => ['db_prod'],
        ],
        // v2 Industrial Def
        'Isolation réseau OT' => [
            100 => ['fw_it_ot', 'ot_jump', 'scada_master'],
            80  => ['historian', 'plc_assembly', 'plc_cooling'],
            50  => ['apigw'],
        ],
        'Vérification firmware' => [
            100 => ['plc_assembly', 'plc_cooling', 'sis'],
            80  => ['scada_master', 'hmi_main'],
            50  => [],
        ],
        'Test processus physique' => [
            100 => ['sis', 'plc_assembly', 'plc_cooling'],
            80  => ['hmi_main', 'scada_master', 'ot_eng_ws'],
            50  => [],
        ],
    ];

    public static function nodeId(string $targetNodeName): ?string
    {
        $id = array_search($targetNodeName, self::NODE_MAP);
        return $id !== false ? $id : null;
    }

    public static function calculate(string $cardName, string $targetNodeName, ?int $scenario = null): array
    {
        $targetId = self::nodeId($targetNodeName) ?: $targetNodeName;

        $isRed = array_key_exists($cardName, self::RED_MATRIX);
        $isBlue = array_key_exists($cardName, self::BLUE_MATRIX);

        $effectiveness = 0;
        if ($isRed) {
            $effectiveness = self::getEffectiveness(self::RED_MATRIX[$cardName], $targetId);
        } elseif ($isBlue) {
            $matrix = self::BLUE_MATRIX[$cardName];
            if (empty($matrix[100]) && empty($matrix[80]) && empty($matrix[50])) {
                $effectiveness = 100;
            } else {
                $effectiveness = self::getEffectiveness($matrix, $targetId);
            }
        }

        if ($effectiveness === 0) {
            return [
                'scoreMultiplier' => 0,
                'effectiveness'   => 0,
                'log'             => "Target not vulnerable (0%).",
            ];
        }

        // Critical path bonus logic based on new scenarios
        $criticalPaths = [
            1 => ['apigw', 'jenkins_master', 'vault', 'aws_ec2', 'db_prod'],
            2 => ['apigw', 'npm', 'k8s_control', 'aws_ec2'],
            3 => ['vpn', 'ad_primary', 'laptop_ex', 'aws_iam', 'aws_ec2'],
            4 => ['apigw2', 'vpn', 'k8s_control', 'elastic', 'db_prod'],
            5 => ['o365', 'laptop_ceo', 'laptop_cfo', 'erp', 'bank'],
            6 => ['rdp_jump', 'ad_primary', 'vcenter', 'db_prod', 'veeam'],
            7 => ['dns_c2', 'vpn', 'linux_jump', 'aws_iam', 'k8s_control'],
            8 => ['vpn', 'fw_it_ot', 'historian', 'scada_master', 'sis'],
        ];

        $bonusMsg = "";
        $scoreMultiplier = $effectiveness / 100;

        if ($scenario && isset($criticalPaths[$scenario]) && in_array($targetId, $criticalPaths[$scenario])) {
            $scoreMultiplier += 0.20; 
            $bonusMsg = " (+20% Critical Path Bonus)";
        }

        return [
            'scoreMultiplier' => $scoreMultiplier,
            'effectiveness'   => $effectiveness,
            'log'             => "Effectiveness: {$effectiveness}%" . $bonusMsg,
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
