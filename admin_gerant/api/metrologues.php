<?php

$uid = $_SESSION['user_id'];

switch ($action) {

    /* ══════════════════════════════
       MÉTROLOGUES — liste
    ══════════════════════════════ */
    case 'metrologues_list':

        $rows = $pdo->query("
            SELECT
                u.id,
                CONCAT(u.prenom, ' ', u.nom) AS name,
                u.email,
                u.role,
                us.classeur_number,
                us.poste,
                us.specialite,
                us.niveau,
                us.telephone,
                COALESCE(qs.total_score, 0) AS score,
                (SELECT COUNT(*) FROM projects
                    WHERE (assigned_to = u.id OR user_id = u.id)
                    AND (status='in_progress' OR statut='en_cours')
                ) AS projets,
                (SELECT COUNT(*) FROM daily_scores ds
                    WHERE ds.user_id = u.id
                    AND ds.is_absent = 1
                    AND QUARTER(ds.date_score) = QUARTER(NOW())
                    AND YEAR(ds.date_score) = YEAR(NOW())
                ) AS absences
            FROM users u
            LEFT JOIN user_settings us ON us.user_id = u.id
            LEFT JOIN quarter_scores qs ON qs.user_id = u.id
                AND qs.quarter = QUARTER(NOW())
                AND qs.year = YEAR(NOW())
            WHERE u.role = 'metrologue' AND u.is_active = 1
            ORDER BY score DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        jsonResponse($rows);
        break;


    /* ══════════════════════════════
       MÉTROLOGUE — détail
    ══════════════════════════════ */
    case 'metro_detail':

        $id = (int)($_GET['id'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT
                u.id,
                CONCAT(u.prenom, ' ', u.nom) AS name,
                u.email,
                us.classeur_number,
                us.poste,
                us.specialite,
                us.niveau,
                us.telephone,
                COALESCE(qs.total_score, 0) AS score
            FROM users u
            LEFT JOIN user_settings us ON us.user_id = u.id
            LEFT JOIN quarter_scores qs ON qs.user_id = u.id
                AND qs.quarter = QUARTER(NOW())
                AND qs.year = YEAR(NOW())
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);

        $metro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$metro) {
            jsonResponse(['error' => 'Introuvable'], 404);
        }

        // projets
        $pstmt = $pdo->prepare("
            SELECT
                p.id,
                p.nom_projet AS title,
                COALESCE(p.status, p.statut) AS status,
                p.date_fin AS deadline,
                (SELECT COUNT(*) FROM steps WHERE project_id=p.id AND status='done') AS done_steps,
                (SELECT COUNT(*) FROM steps WHERE project_id=p.id) AS total_steps
            FROM projects p
            WHERE p.assigned_to = ? OR p.user_id = ?
            ORDER BY p.date_creation DESC LIMIT 10
        ");
        $pstmt->execute([$id, $id]);

        $metro['projects'] = $pstmt->fetchAll(PDO::FETCH_ASSOC);

        // absences
        $astmt = $pdo->prepare("
            SELECT date_score AS date FROM daily_scores
            WHERE user_id=? AND is_absent=1
            AND QUARTER(date_score)=QUARTER(NOW())
            AND YEAR(date_score)=YEAR(NOW())
            ORDER BY date_score DESC LIMIT 20
        ");
        $astmt->execute([$id]);

        $metro['absences_list'] = array_column(
            $astmt->fetchAll(PDO::FETCH_ASSOC),
            'date'
        );

        jsonResponse($metro);
        break;


    /* ══════════════════════════════
       MÉTROLOGUE — ajouter
    ══════════════════════════════ */
    case 'metro_add':

        $data     = json_decode(file_get_contents('php://input'), true);
        $fullname = trim($data['name'] ?? '');

        $parts  = explode(' ', $fullname, 2);
        $prenom = $parts[0] ?? '';
        $nom    = $parts[1] ?? '';

        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $classeur = (int)($data['classeur_number'] ?? 0);

        if (!$prenom || !$email || !$password) {
            jsonResponse(['error' => 'Champs obligatoires manquants'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['error' => 'Email invalide'], 400);
        }

        try {

            $pdo->beginTransaction();

            // email unique
            $chk = $pdo->prepare("SELECT id FROM users WHERE email=?");
            $chk->execute([$email]);
            if ($chk->fetch()) {
                jsonResponse(['error' => 'Email déjà utilisé'], 409);
            }

            // classeur unique
            if ($classeur > 0) {
                $chkC = $pdo->prepare("SELECT user_id FROM user_settings WHERE classeur_number=?");
                $chkC->execute([$classeur]);
                if ($chkC->fetch()) {
                    jsonResponse(['error' => "Classeur déjà utilisé"], 409);
                }
            }

            $ins = $pdo->prepare("
                INSERT INTO users (nom, prenom, email, mot_de_passe, role, is_active, created_at)
                VALUES (?, ?, ?, ?, 'metrologue', 1, NOW())
            ");
            $ins->execute([$nom, $prenom, $email, password_hash($password, PASSWORD_DEFAULT)]);

            $newId = $pdo->lastInsertId();

            $pdo->prepare("
                INSERT INTO user_settings (user_id, classeur_number, poste, specialite, niveau, telephone)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    classeur_number = VALUES(classeur_number),
                    poste = VALUES(poste),
                    specialite = VALUES(specialite),
                    niveau = VALUES(niveau),
                    telephone = VALUES(telephone)
            ")->execute([
                $newId,
                $classeur,
                $data['poste'] ?? '',
                $data['specialite'] ?? '',
                $data['niveau'] ?? 'Junior',
                $data['telephone'] ?? ''
            ]);

            $pdo->commit();

            logAction($uid, '➕ Métrologue ajouté', $fullname);

            jsonResponse(['success' => true, 'id' => $newId]);

        } catch (Exception $e) {
            $pdo->rollBack();
            jsonResponse(['error' => $e->getMessage()], 500);
        }

        break;


    /* ══════════════════════════════
       MÉTROLOGUE — modifier
    ══════════════════════════════ */
    case 'metro_edit':

        $data = json_decode(file_get_contents('php://input'), true);
        $id   = (int)($data['id'] ?? 0);

        if (!$id) {
            jsonResponse(['error' => 'ID manquant'], 400);
        }

        $fullname = trim($data['name'] ?? '');
        $parts    = explode(' ', $fullname, 2);

        $prenom = $parts[0] ?? '';
        $nom    = $parts[1] ?? '';

        $pdo->prepare("UPDATE users SET nom=?, prenom=?, email=? WHERE id=?")
            ->execute([$nom, $prenom, $data['email'] ?? '', $id]);

        $pdo->prepare("
            INSERT INTO user_settings (user_id, classeur_number, poste, specialite, niveau, telephone)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                classeur_number = VALUES(classeur_number),
                poste = VALUES(poste),
                specialite = VALUES(specialite),
                niveau = VALUES(niveau),
                telephone = VALUES(telephone)
        ")->execute([
            $id,
            (int)($data['classeur_number'] ?? 0),
            $data['poste'] ?? '',
            $data['specialite'] ?? '',
            $data['niveau'] ?? '',
            $data['telephone'] ?? ''
        ]);

        logAction($uid, '✏️ Métrologue modifié', $fullname);

        jsonResponse(['success' => true]);
        break;


    /* ══════════════════════════════
       MÉTROLOGUE — supprimer
    ══════════════════════════════ */
    case 'metro_delete':

        $data = json_decode(file_get_contents('php://input'), true);
        $id   = (int)($data['id'] ?? 0);

        if (!$id) {
            jsonResponse(['error' => 'ID manquant'], 400);
        }

        $stmt = $pdo->prepare("SELECT CONCAT(prenom,' ',nom) FROM users WHERE id=?");
        $stmt->execute([$id]);

        $nm = $stmt->fetchColumn();

        $pdo->prepare("UPDATE users SET is_active=0 WHERE id=?")->execute([$id]);

        logAction($uid, '🗑️ Métrologue supprimé', $nm);

        jsonResponse(['success' => true]);
        break;


    default:
        jsonResponse(['error' => 'Action inconnue'], 404);
        break;
}