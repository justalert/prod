<?php
// app/Controllers/AlertController.php

require_once BASE_PATH . 'Services/MailService.php';

class AlertController {
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /");
            exit;
        }

        // 1. Verificare CSRF Token (Securitate)
        if (!isset($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['error'] = translate('error.system', 'Eroare de securitate. Te rugăm să reîncarci pagina și să încerci din nou.');
            header("Location: /");
            exit;
        }

        // Preluăm câmpurile standard
        $nume = trim($_POST['nume'] ?? '');
        $prenume = trim($_POST['prenume'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $countryId = (int)($_POST['country_id'] ?? 1);

        // 2. PRELUĂM ȘI VALIDĂM NOILE ARRAYS JSON
        $targetInstitutions = null;
        if (!empty($_POST['target_institutions']) && $_POST['target_institutions'] !== '[]') {
            // Verificăm dacă este un JSON valid
            if (is_array(json_decode($_POST['target_institutions'], true))) {
                $targetInstitutions = $_POST['target_institutions'];
            }
        }

        $targetObjects = null;
        if (!empty($_POST['target_objects']) && $_POST['target_objects'] !== '[]') {
            // Verificăm dacă este un JSON valid
            if (is_array(json_decode($_POST['target_objects'], true))) {
                $targetObjects = $_POST['target_objects'];
            }
        }
        
        // Preluăm bifările obligatorii
        $legalConsent = isset($_POST['legal_consent']);
        $selfMonitoringOnly = isset($_POST['self_monitoring_only']);

        // 3. Validăm bifa legală obligatorie
        if (!$legalConsent) {
            $_SESSION['error'] = translate('error.legal_required', 'Trebuie să accepți Termenii și Condițiile și Politica de Confidențialitate pentru a continua.');
            header("Location: /");
            exit;
        }

        // 4. Validăm bifa de auto-monitorizare
        if (!$selfMonitoringOnly) {
            $_SESSION['error'] = translate('error.self_monitoring_required', 'Trebuie să confirmi că soliciți monitorizarea exclusiv pentru propria persoană.');
            header("Location: /");
            exit;
        }

        // 5. Validăm datele de bază
        if (empty($nume) || empty($prenume) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = translate('error.invalid_data', 'Date invalide. Te rugăm să verifici formularul.');
            header("Location: /");
            exit;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $userId = $stmt->fetchColumn();

            $isVerified = false;

            // Dacă utilizatorul nu există, îl creăm
            if (!$userId) {
                $stmt = $db->prepare("INSERT INTO users (email) VALUES (?)");
                $stmt->execute([$email]);
                $userId = $db->lastInsertId();
            } else {
                // Dacă utilizatorul există, blocăm configurarea altui nume pe același cont
                $stmt = $db->prepare("
                    SELECT nume_familie, prenume
                    FROM alerts
                    WHERE user_id = ?
                    ORDER BY id ASC
                    LIMIT 1
                ");
                $stmt->execute([$userId]);
                $existingAlert = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingAlert) {
                    $existingNume = $this->normalizeName($existingAlert['nume_familie'] ?? '');
                    $existingPrenume = $this->normalizeName($existingAlert['prenume'] ?? '');
                    $incomingNume = $this->normalizeName($nume);
                    $incomingPrenume = $this->normalizeName($prenume);

                    if ($existingNume !== $incomingNume || $existingPrenume !== $incomingPrenume) {
                        $db->rollBack();
                        $_SESSION['error'] = translate('error.account_name_locked', 'Acest cont poate gestiona alerte doar pentru numele folosit deja la prima configurare.');
                        header("Location: /");
                        exit;
                    }
                }

                // Dacă utilizatorul există, verificăm dacă este deja validat (are alerte active)
                $stmt = $db->prepare("SELECT COUNT(*) FROM alerts WHERE user_id = ? AND status = 'active'");
                $stmt->execute([$userId]);
                $isVerified = $stmt->fetchColumn() > 0;
            }

            // Salvăm noua alertă cu noile coloane (JSON) pentru instanțe și obiecte
            $stmt = $db->prepare("
                INSERT INTO alerts (user_id, country_id, target_institutions, target_objects, nume_familie, prenume) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $countryId, $targetInstitutions, $targetObjects, $nume, $prenume]);
            
            // Generăm token-ul de siguranță (Magic Link)
            $token = bin2hex(random_bytes(32)); 
            $tokenHash = hash('sha256', $token); 
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $stmt = $db->prepare("INSERT INTO magic_links (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $tokenHash, $expiresAt]);

            // LOGICA DE EMAIL:
            if ($isVerified) {
                // Utilizator vechi -> Trimitem template-ul de Login
                MailService::sendAccessLink($email, $token);
                $_SESSION['success'] = translate('success.alert_saved_login');
            } else {
                // Utilizator nou -> Trimitem template-ul de Confirmare Inițială
                MailService::sendMagicLink($email, $token);
                $_SESSION['success'] = translate('success.alert_saved');
            }

            $db->commit();
            header("Location: /");
            exit;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            error_log($e->getMessage()); 
            $_SESSION['error'] = translate('error.system', 'A apărut o eroare de sistem. Te rugăm să încerci din nou mai târziu.');
            header("Location: /");
            exit;
        }
    }

    private function normalizeName($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value);

        return $value ?? '';
    }
}
