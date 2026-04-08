<?php
// app/Controllers/DashboardController.php

class DashboardController {
    
    private function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }
    }

    // Metodă centralizată pentru verificarea CSRF pe rutele de tip POST
    private function verifyCsrf() {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['error'] = translate('error.system', 'Eroare de securitate. Te rugăm să reîncarci pagina și să încerci din nou.');
            header("Location: /dashboard");
            exit;
        }
    }

    public function index() {
        $this->requireLogin();
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];

        // Extragem setarea AI Opt-in
        $stmtUser = $db->prepare("SELECT ai_opt_in FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $aiOptIn = $stmtUser->fetchColumn();

        // Extragem alertele utilizatorului
        $stmtAlerts = $db->prepare("SELECT * FROM alerts WHERE user_id = ? ORDER BY created_at DESC");
        $stmtAlerts->execute([$userId]);
        $alerts = $stmtAlerts->fetchAll();

        // Extragem instanțele și obiectele pentru a le afișa în modalul de Editare
        $stmtInst = $db->query("SELECT * FROM institutions ORDER BY name ASC");
        $institutions = $stmtInst->fetchAll();

        $stmtObj = $db->query("SELECT * FROM case_objects ORDER BY name ASC");
        $caseObjects = $stmtObj->fetchAll();

        // Randăm pagina (View-ul va folosi automat layout.php, deci va avea Footer!)
        View::render('pages/dashboard/index', [
            'pageTitle' => translate('nav.dashboard', 'Panoul meu'),
            'alerts' => $alerts,
            'aiOptIn' => $aiOptIn,
            'institutions' => $institutions,
            'caseObjects' => $caseObjects
        ]);
    }

    public function toggleAi() {
        $this->requireLogin();
        $this->verifyCsrf(); // Validare CSRF
        
        $aiOptIn = isset($_POST['ai_opt_in']) ? 1 : 0;
        
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET ai_opt_in = ? WHERE id = ?");
        $stmt->execute([$aiOptIn, $_SESSION['user_id']]);
        
        $_SESSION['success'] = translate('success.settings_updated', 'Setările au fost actualizate.');
        header("Location: /dashboard");
        exit;
    }

    public function toggleStatus() {
        $this->requireLogin();
        $this->verifyCsrf(); // Validare CSRF
        
        $alertId = (int)($_POST['alert_id'] ?? 0);
        $newStatus = ($_POST['status'] ?? '') === 'active' ? 'active' : 'paused';

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE alerts SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$newStatus, $alertId, $_SESSION['user_id']]);

        $_SESSION['success'] = translate('success.status_updated', 'Statusul alertei a fost modificat.');
        header("Location: /dashboard");
        exit;
    }

    // Alias minim pentru routerul actual: /dashboard/alert/toggle-status -> toggleAlertStatus
    public function toggleAlertStatus() {
        $this->toggleStatus();
    }

    public function deleteAlert() {
        $this->requireLogin();
        $this->verifyCsrf(); // Validare CSRF
        
        $alertId = (int)($_POST['alert_id'] ?? 0);

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM alerts WHERE id = ? AND user_id = ?");
        $stmt->execute([$alertId, $_SESSION['user_id']]);

        $_SESSION['success'] = translate('success.alert_deleted', 'Alerta a fost ștearsă.');
        header("Location: /dashboard");
        exit;
    }

    public function editAlert() {
        $this->requireLogin();
        $this->verifyCsrf(); // Validare CSRF
        
        $alertId = (int)($_POST['alert_id'] ?? 0);

        // În dashboard hidden input-urile trimit JSON string ("[]", "["COD"]"), nu array PHP.
        $targetInstitutions = $this->normalizeSelectionJson($_POST['target_institutions'] ?? null);
        $targetObjects = $this->normalizeSelectionJson($_POST['target_objects'] ?? null);
        
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE alerts SET target_institutions = ?, target_objects = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$targetInstitutions, $targetObjects, $alertId, $_SESSION['user_id']]);
        
        $_SESSION['success'] = translate('success.alert_updated', 'Alerta a fost actualizată cu succes.');
        header("Location: /dashboard");
        exit;
    }
    
    public function createAlert() {
        $this->requireLogin();
        $this->verifyCsrf(); // Validare CSRF

        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];

        // Preluăm automat numele și țara de la o alertă existentă a acestui user
        $stmt = $db->prepare("SELECT nume_familie, prenume, country_id FROM alerts WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $existing = $stmt->fetch();

        if (!$existing) {
            // Un fallback în caz că a reușit să apese pe buton deși nu are alerte setate
            $_SESSION['error'] = translate('error.system', 'Trebuie să setezi prima alertă de pe pagina principală.');
            header("Location: /");
            exit;
        }

        $targetInstitutions = $this->normalizeSelectionJson($_POST['target_institutions'] ?? null);
        $targetObjects = $this->normalizeSelectionJson($_POST['target_objects'] ?? null);

        $stmt = $db->prepare("
            INSERT INTO alerts (user_id, country_id, target_institutions, target_objects, nume_familie, prenume, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([
            $userId, 
            $existing['country_id'], 
            $targetInstitutions, 
            $targetObjects, 
            $existing['nume_familie'], 
            $existing['prenume']
        ]);

        $_SESSION['success'] = translate('success.alert_added', 'Alerta nouă a fost adăugată cu succes.');
        header("Location: /dashboard");
        exit;
    }

    private function normalizeSelectionJson($value) {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $clean = [];
            foreach ($value as $item) {
                if (!is_scalar($item)) {
                    continue;
                }

                $item = trim((string)$item);
                if ($item !== '') {
                    $clean[] = $item;
                }
            }

            $clean = array_values(array_unique($clean));
            return empty($clean) ? null : json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $value = trim((string)$value);
        if ($value === '' || $value === '[]') {
            return null;
        }

        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return null;
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

        $clean = array_values(array_unique($clean));
        return empty($clean) ? null : json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    // --- FUNCȚIONALITĂȚI GDPR --- //

    public function exportData() {
        $this->requireLogin();
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];

        // 1. Datele contului
        $stmt = $db->prepare("SELECT email, lang, ai_opt_in, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Alertele setate
        $stmt = $db->prepare("SELECT id, nume_familie, prenume, tara, judet, dosar_specific, status, target_institutions, target_objects, created_at FROM alerts WHERE user_id = ?");
        $stmt->execute([$userId]);
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Istoricul notificărilor (folosind JOIN pentru a lua doar log-urile acestui utilizator)
        $stmt = $db->prepare("
            SELECT f.case_key, f.first_seen_at, f.last_seen_at, f.last_notified_at 
            FROM alert_case_fingerprints f
            INNER JOIN alerts a ON f.alert_id = a.id
            WHERE a.user_id = ?
        ");
        $stmt->execute([$userId]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Asamblăm pachetul complet
        $exportData = [
            'generated_at' => date('Y-m-d H:i:s'),
            'platform' => 'JustAlert.eu',
            'user' => $userData,
            'alerts' => $alerts,
            'notification_history' => $history
        ];

        // Forțăm browserul să descarce fișierul JSON
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="justalert_date_personale.json"');
        
        echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function clearHistory() {
        $this->requireLogin();
        $this->verifyCsrf(); // Protecție CSRF
        
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];

        try {
            $db->beginTransaction();

            // 1. Ștergem logurile de notificări DOAR pentru alertele care aparțin acestui utilizator
            $stmt = $db->prepare("
                DELETE f FROM alert_case_fingerprints f
                INNER JOIN alerts a ON f.alert_id = a.id
                WHERE a.user_id = ?
            ");
            $stmt->execute([$userId]);

            // 2. Punem pe pauză alertele active pentru a preveni spam-ul automat imediat după ștergere
            $stmt = $db->prepare("UPDATE alerts SET status = 'paused' WHERE user_id = ? AND status = 'active'");
            $stmt->execute([$userId]);

            $db->commit();

            $_SESSION['success'] = translate('success.history_cleared', 'Istoricul notificărilor a fost șters, iar alertele sunt pe pauză.');
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = translate('error.system');
        }

        header("Location: /dashboard");
        exit;
    }
    public function deleteAccount() {
        $this->requireLogin();
        $this->verifyCsrf(); // Protecție CSRF
        
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];

        try {
            // Ștergerea din tabela `users` va declanșa `ON DELETE CASCADE` la nivelul bazei de date.
            // Acest lucru va șterge automat, sigur și eficient toate alertele, amprentele dosarelor (istoricul) și linkurile magice.
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);

            // Distrugem sesiunea pentru a deconecta utilizatorul, dar îi păstrăm preferința de limbă
            $savedLang = $_SESSION['lang'] ?? 'ro';
            session_destroy();
            session_start();
            $_SESSION['lang'] = $savedLang;

            $_SESSION['success'] = translate('success.account_deleted', 'Contul tău și toate datele asociate au fost șterse definitiv.');
            
        } catch (Exception $e) {
            error_log("Eroare la ștergerea contului pentru user_id {$userId}: " . $e->getMessage());
            $_SESSION['error'] = translate('error.system');
        }

        // După ștergere, îl trimitem pe prima pagină
        header("Location: /");
        exit;
    }
}
