<?php
// app/Services/AlertNotificationService.php

require_once BASE_PATH . 'Services/JustApiService.php';
require_once BASE_PATH . 'Services/MailService.php';

class AlertNotificationService
{
    private PDO $db;
    private JustApiService $justApi;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->justApi = new JustApiService();
    }

    /**
     * Rulează pentru toate alertele active.
     * Returnează un rezumat util pentru log/debug.
     */
    public function run(): array
    {
        $summary = [
            'alerts_total' => 0,
            'alerts_processed' => 0,
            'alerts_with_notifications' => 0,
            'notifications_sent' => 0,
            'errors' => 0,
        ];

        foreach ($this->getActiveAlerts() as $alert) {
            $summary['alerts_total']++;

            try {
                $result = $this->processAlert($alert);
                $summary['alerts_processed']++;

                if ($result['notified_count'] > 0) {
                    $summary['alerts_with_notifications']++;
                    $summary['notifications_sent'] += $result['notified_count'];
                }
            } catch (Throwable $e) {
                $summary['errors']++;
                error_log('AlertNotificationService::run alert_id=' . (int)$alert['id'] . ' error=' . $e->getMessage());
            }
        }

        return $summary;
    }

    /**
     * Rulează pentru o singură alertă.
     * @param int $alertId ID-ul alertei
     * @param bool $isInitial Flag pentru a trimite emailul de bun venit (scanarea de bază)
     */
    public function runForAlertId(int $alertId, bool $isInitial = false): array
    {
        $alert = $this->getAlertById($alertId);

        if (!$alert) {
            return [
                'alert_id' => $alertId,
                'processed' => false,
                'notified_count' => 0,
                'reason' => 'alert_not_found_or_not_active',
            ];
        }

        return $this->processAlert($alert, $isInitial);
    }

    private function getActiveAlerts(): array
    {
        $sql = "
            SELECT
                a.id,
                a.user_id,
                a.nume_familie,
                a.prenume,
                a.target_institutions,
                a.target_objects,
                a.last_checked_date,
                u.email,
                u.lang
            FROM alerts a
            INNER JOIN users u ON u.id = a.user_id
            WHERE a.status = 'active'
            ORDER BY a.id ASC
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function getAlertById(int $alertId): ?array
    {
        $sql = "
            SELECT
                a.id,
                a.user_id,
                a.nume_familie,
                a.prenume,
                a.target_institutions,
                a.target_objects,
                a.last_checked_date,
                u.email,
                u.lang
            FROM alerts a
            INNER JOIN users u ON u.id = a.user_id
            WHERE a.id = ? AND a.status = 'active'
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$alertId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function processAlert(array $alert, bool $isInitial = false): array
    {
        $alertId = (int)$alert['id'];
        $email = (string)$alert['email'];
        $lang = (string)($alert['lang'] ?? 'ro');

        if (class_exists('Translator')) {
            Translator::init($lang ?: 'ro');
        }

        $fullName = trim((string)$alert['nume_familie'] . ' ' . (string)$alert['prenume']);
        if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->touchAlertCheckDate($alertId);
            return [
                'alert_id' => $alertId,
                'processed' => false,
                'notified_count' => 0,
                'reason' => 'invalid_alert_data',
            ];
        }

        $dosare = $this->fetchCasesForAlert($alert);
        $dosareDeNotificat = [];
        $seenCaseKeys = [];
        $now = date('Y-m-d H:i:s');

        $this->db->beginTransaction();

        try {
            foreach ($dosare as $dosar) {
                $numarDosar = $this->normalizeString((string)$this->getCaseField($dosar, 'numar'));
                if ($numarDosar === '') {
                    continue;
                }

                $caseKey = $this->buildCaseKey($alertId, $dosar);
                if ($caseKey === '') {
                    continue;
                }

                if (isset($seenCaseKeys[$caseKey])) {
                    continue;
                }
                $seenCaseKeys[$caseKey] = true;

                $stateHash = $this->buildStateHash($alertId, $dosar);
                $existing = $this->findFingerprint($alertId, $caseKey);

                if (!$existing) {
                    $this->insertFingerprint($alertId, $caseKey, $stateHash, $now);
                    $dosareDeNotificat[] = $dosar;
                    continue;
                }

                $this->touchFingerprintSeen((int)$existing['id'], $now);

                if (!hash_equals((string)$existing['state_hash'], $stateHash)) {
                    $this->updateFingerprintState((int)$existing['id'], $stateHash, $now);
                    $dosareDeNotificat[] = $dosar;
                }
            }

            if (!empty($dosareDeNotificat)) {
                // Aici se face diferența între Mailul de Bun Venit și Alerta Zilnică
                if ($isInitial) {
                    $sent = MailService::sendWelcomeNotification($email, $fullName, $dosareDeNotificat);
                } else {
                    $sent = MailService::sendAlertNotification($email, $fullName, $dosareDeNotificat);
                }

                if ($sent) {
                    foreach ($dosareDeNotificat as $dosarNotificat) {
                        $caseKey = $this->buildCaseKey($alertId, $dosarNotificat);
                        $this->markFingerprintNotified($alertId, $caseKey, $now);
                    }
                } else {
                    throw new RuntimeException('Trimiterea emailului a eșuat.');
                }
            }

            $this->touchAlertCheckDate($alertId, $now);
            $this->db->commit();

            return [
                'alert_id' => $alertId,
                'processed' => true,
                'notified_count' => count($dosareDeNotificat),
                'reason' => 'ok',
            ];
        } catch (Throwable $e) {
            $this->db->rollBack();
            // NU setăm touchAlertCheckDate aici, pentru ca sistemul să reîncerce la următoarea rulare în caz de eroare de API
            throw $e;
        }
    }

    private function fetchCasesForAlert(array $alert): array
    {
        $fullName = trim((string)$alert['nume_familie'] . ' ' . (string)$alert['prenume']);
        $institutionCodes = $this->decodeJsonArray($alert['target_institutions'] ?? null);
        $objectCodes = $this->decodeJsonArray($alert['target_objects'] ?? null);

        if (empty($institutionCodes)) {
            $institutionCodes = [null];
        }

        if (empty($objectCodes)) {
            $objectCodes = [null];
        }

        $results = [];
        $seenNumbers = [];

        foreach ($institutionCodes as $institutionCode) {
            foreach ($objectCodes as $objectCode) {
                $dosare = $this->justApi->searchCases(
                    $fullName,
                    $institutionCode ?: null,
                    $objectCode ?: null
                );

                // Dacă primim null, înseamnă că a picat API-ul Ministerului (SOAP Fault)
                if ($dosare === null) {
                    throw new RuntimeException("Eroare de comunicare cu API-ul MJ pentru {$fullName}.");
                }

                foreach ($dosare as $dosar) {
                    $numarDosar = $this->normalizeString((string)$this->getCaseField($dosar, 'numar'));
                    if ($numarDosar === '') {
                        continue;
                    }

                    if (isset($seenNumbers[$numarDosar])) {
                        continue;
                    }

                    $seenNumbers[$numarDosar] = true;
                    $results[] = $dosar;
                }
                
                // Pauză de 0.5 secunde între request-uri pentru a nu fi blocați de just.ro
                usleep(500000); 
            }
        }

        return $results;
    }

    private function findFingerprint(int $alertId, string $caseKey): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, state_hash
            FROM alert_case_fingerprints
            WHERE alert_id = ? AND case_key = ?
            LIMIT 1
        ");
        $stmt->execute([$alertId, $caseKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function insertFingerprint(int $alertId, string $caseKey, string $stateHash, string $now): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO alert_case_fingerprints
                (alert_id, case_key, state_hash, first_seen_at, last_seen_at)
            VALUES
                (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$alertId, $caseKey, $stateHash, $now, $now]);
    }

    private function touchFingerprintSeen(int $fingerprintId, string $now): void
    {
        $stmt = $this->db->prepare("
            UPDATE alert_case_fingerprints
            SET last_seen_at = ?
            WHERE id = ?
        ");
        $stmt->execute([$now, $fingerprintId]);
    }

    private function updateFingerprintState(int $fingerprintId, string $stateHash, string $now): void
    {
        $stmt = $this->db->prepare("
            UPDATE alert_case_fingerprints
            SET state_hash = ?, last_seen_at = ?
            WHERE id = ?
        ");
        $stmt->execute([$stateHash, $now, $fingerprintId]);
    }

    private function markFingerprintNotified(int $alertId, string $caseKey, string $now): void
    {
        $stmt = $this->db->prepare("
            UPDATE alert_case_fingerprints
            SET last_notified_at = ?, last_seen_at = ?
            WHERE alert_id = ? AND case_key = ?
        ");
        $stmt->execute([$now, $now, $alertId, $caseKey]);
    }

    private function touchAlertCheckDate(int $alertId, ?string $now = null): void
    {
        $now = $now ?: date('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            UPDATE alerts
            SET last_checked_date = ?
            WHERE id = ?
        ");
        $stmt->execute([$now, $alertId]);
    }

    private function buildCaseKey(int $alertId, object $dosar): string
    {
        $numar = $this->normalizeString((string)$this->getCaseField($dosar, 'numar'));
        if ($numar === '') {
            return '';
        }

        $institutie = $this->normalizeString((string)$this->getCaseField($dosar, 'institutie'));
        $identity = $numar . '|' . $institutie;

        return $this->hmacFingerprint($alertId . '|' . $identity);
    }

    private function buildStateHash(int $alertId, object $dosar): string
    {
        $numar = $this->normalizeString((string)$this->getCaseField($dosar, 'numar'));
        $numarVechi = $this->normalizeString((string)$this->getCaseField($dosar, 'numarVechi'));
        $data = $this->normalizeDateValue($this->getCaseField($dosar, 'data'));
        $institutie = $this->normalizeString((string)$this->getCaseField($dosar, 'institutie'));
        $departament = $this->normalizeString((string)$this->getCaseField($dosar, 'departament'));
        $categorie = $this->normalizeString((string)$this->getCaseField($dosar, 'categorieCazNume'));
        $categorieFallback = $this->normalizeString((string)$this->getCaseField($dosar, 'categorieCaz'));
        $stadiu = $this->normalizeString((string)$this->getCaseField($dosar, 'stadiuProcesualNume'));
        $stadiuFallback = $this->normalizeString((string)$this->getCaseField($dosar, 'stadiuProcesual'));
        $obiect = $this->normalizeString((string)$this->getCaseField($dosar, 'obiect'));

        $sedinteFingerprint = $this->buildSedinteFingerprint($dosar);
        $partiFingerprint = $this->buildPartiFingerprint($dosar);
        $caiAtacFingerprint = $this->buildCaiAtacFingerprint($dosar);

        $state = implode('|', [
            $numar,
            $numarVechi,
            $data,
            $institutie,
            $departament,
            $categorie,
            $categorieFallback,
            $stadiu,
            $stadiuFallback,
            $obiect,
            $sedinteFingerprint,
            $partiFingerprint,
            $caiAtacFingerprint,
        ]);

        return $this->hmacFingerprint($alertId . '|' . $state);
    }

    private function buildSedinteFingerprint(object $dosar): string
    {
        $sedinte = $this->toArray($this->getCaseField($dosar, 'sedinte'));
        if (empty($sedinte)) {
            return '';
        }

        $rows = [];

        foreach ($sedinte as $sedinta) {
            $rows[] = implode('|', [
                $this->normalizeString((string)$this->getCaseField($sedinta, 'complet')),
                $this->normalizeDateValue($this->getCaseField($sedinta, 'data')),
                $this->normalizeString((string)$this->getCaseField($sedinta, 'ora')),
                $this->normalizeString((string)$this->getCaseField($sedinta, 'solutie')),
                $this->normalizeString((string)$this->getCaseField($sedinta, 'solutieSumar')),
                $this->normalizeDateValue($this->getCaseField($sedinta, 'dataPronuntare')),
                $this->normalizeString((string)$this->getCaseField($sedinta, 'documentSedinta')),
                $this->normalizeString((string)$this->getCaseField($sedinta, 'numarDocument')),
                $this->normalizeDateValue($this->getCaseField($sedinta, 'dataDocument')),
            ]);
        }

        sort($rows, SORT_STRING);

        return implode('||', $rows);
    }

    private function buildPartiFingerprint(object $dosar): string
    {
        $parti = $this->toArray($this->getCaseField($dosar, 'parti'));
        if (empty($parti)) {
            return '';
        }

        $rows = [];

        foreach ($parti as $parte) {
            $rows[] = implode('|', [
                $this->normalizeString((string)$this->getCaseField($parte, 'nume')),
                $this->normalizeString((string)$this->getCaseField($parte, 'calitateParte')),
            ]);
        }

        sort($rows, SORT_STRING);

        return implode('||', $rows);
    }

    private function buildCaiAtacFingerprint(object $dosar): string
    {
        $caiAtac = $this->toArray($this->getCaseField($dosar, 'caiAtac'));
        if (empty($caiAtac)) {
            return '';
        }

        $rows = [];

        foreach ($caiAtac as $cale) {
            $rows[] = implode('|', [
                $this->normalizeDateValue($this->getCaseField($cale, 'dataDeclarare')),
                $this->normalizeString((string)$this->getCaseField($cale, 'parteDeclaratoare')),
                $this->normalizeString((string)$this->getCaseField($cale, 'tipCaleAtac')),
            ]);
        }

        sort($rows, SORT_STRING);

        return implode('||', $rows);
    }

    private function getCaseField($source, string $field)
    {
        if (is_array($source)) {
            return $source[$field] ?? null;
        }

        if (is_object($source)) {
            return $source->{$field} ?? null;
        }

        return null;
    }

    private function toArray($value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return [$value];
        }

        return [];
    }

    private function decodeJsonArray(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $clean = [];
        foreach ($decoded as $item) {
            if (!is_scalar($item)) {
                continue;
            }

            $item = trim((string)$item);
            if ($item !== '') {
                $clean[] = $item;
            }
        }

        return array_values(array_unique($clean));
    }

    private function normalizeString(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value);

        return $value ?? '';
    }

    private function normalizeDateValue($value): string
    {
        if ($value === null) {
            return '';
        }

        $stringValue = trim((string)$value);
        if ($stringValue === '') {
            return '';
        }

        $timestamp = strtotime($stringValue);
        if ($timestamp === false) {
            return $this->normalizeString($stringValue);
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function hmacFingerprint(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->getHmacSecret());
    }

    private function getHmacSecret(): string
    {
        $secret = '';

        if (defined('JUSTALERT_HMAC_SECRET') && JUSTALERT_HMAC_SECRET) {
            $secret = (string)JUSTALERT_HMAC_SECRET;
        }

        if ($secret === '' && getenv('JUSTALERT_HMAC_SECRET')) {
            $secret = (string)getenv('JUSTALERT_HMAC_SECRET');
        }

        if ($secret === '' && isset($_ENV['JUSTALERT_HMAC_SECRET'])) {
            $secret = (string)$_ENV['JUSTALERT_HMAC_SECRET'];
        }

        if ($secret === '') {
            throw new RuntimeException('Lipsește secretul HMAC. Definește JUSTALERT_HMAC_SECRET.');
        }

        return $secret;
    }
}
