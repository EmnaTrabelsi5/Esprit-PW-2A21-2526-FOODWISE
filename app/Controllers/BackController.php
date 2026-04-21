<?php
declare(strict_types=1);

class BackController
{
    private PDO $pdo;
    private UtilisateurModel $userModel;
    private ProfilNutritionnelModel $profilModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new UtilisateurModel($pdo);
        $this->profilModel = new ProfilNutritionnelModel($pdo);
    }

    public function connexion(): void
    {
        $errors = [];
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array_map('trim', $_POST);
            if ($old['email'] === '') {
                $errors['email'] = 'Veuillez saisir votre email.';
            } elseif (!validateEmail($old['email'])) {
                $errors['email'] = 'Le format de l\'email est invalide.';
            }

            if ($old['password'] === '') {
                $errors['password'] = 'Veuillez saisir votre mot de passe.';
            }

            if (empty($errors)) {
                $user = $this->userModel->authenticateAsAdmin($old['email'], $old['password']);
                if ($user === null) {
                    $errors['global'] = 'Identifiants incorrects ou vous n\'êtes pas administrateur.';
                } else {
                    $_SESSION['admin_id'] = (int) $user['id'];
                    redirect(buildRoute('module2.back.dashboard.profils'));
                }
            }
        }

        require __DIR__ . '/../views/module2/back/connexion.php';
    }

    public function logoutAdmin(): void
    {
        session_destroy();
        redirect(buildRoute('module2.back.login'));
    }

    public function dashboardProfils(): void
    {
        if (!$this->isAdminAuthenticated()) {
            redirect(buildRoute('module2.back.login'));
        }
        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
            $userId = (int) $_GET['id'];
            $this->userModel->deleteProfile($userId);
            $this->profilModel->deleteByUserId($userId);
            redirect(buildRoute('module2.back.dashboard.profils'));
        }

        $lignesProfils = $this->profilModel->findAll();
        
        // Appliquer la recherche si un terme est fourni
        $searchTerm = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        if (!empty($searchTerm)) {
            $lignesProfils = $this->filterProfiles($lignesProfils, $searchTerm);
        }
        
        $stats = $this->buildStats($lignesProfils);
        require __DIR__ . '/../views/module2/back/dashboard_profils.php';
    }

    public function profilForm(): void
    {
        if (!$this->isAdminAuthenticated()) {
            redirect(buildRoute('module2.back.login'));
        }

        $errors = [];
        $old = [];
        $user = null;
        $profile = null;
        $isNew = true;

        if (isset($_GET['id'])) {
            $userId = (int) $_GET['id'];
            $user = $this->userModel->findById($userId);
            if ($user !== null) {
                $profile = $this->profilModel->findByUserId($userId);
                $isNew = false;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array_map('trim', $_POST);
            $errors = $this->validateAdminProfileForm($old);

            if (empty($errors)) {
                $adminId = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
                
                if (empty($old['id'])) {
                    if ($this->userModel->existsByEmail($old['email'])) {
                        $errors['email'] = 'Cette adresse est déjà utilisée par un autre utilisateur.';
                    } else {
                        $userId = $this->userModel->create(
                            $old['nom'],
                            $old['prenom'] ?? '',
                            $old['email'],
                            $old['password'] ?? bin2hex(random_bytes(8))
                        );
                        
                        // Enregistrer les modifications initiales
                        logModification($this->pdo, $userId, $adminId, 'utilisateur', $userId, 'nom', null, $old['nom']);
                        logModification($this->pdo, $userId, $adminId, 'utilisateur', $userId, 'prenom', null, $old['prenom'] ?? '');
                        logModification($this->pdo, $userId, $adminId, 'utilisateur', $userId, 'email', null, $old['email']);
                    }
                } else {
                    $userId = (int) $old['id'];
                    $oldUser = $this->userModel->findById($userId);
                    
                    if ($this->userModel->existsByEmailExceptId($old['email'], $userId)) {
                        $errors['email'] = 'Cette adresse est déjà utilisée par un autre utilisateur.';
                    } else {
                        $this->userModel->update(
                            $userId,
                            $old['nom'],
                            $old['prenom'] ?? '',
                            $old['email'],
                            $old['password'] ?? null
                        );
                        
                        // Enregistrer les modifications
                        if ($oldUser !== null) {
                            if ($oldUser['nom'] !== $old['nom']) {
                                logModification($this->pdo, $userId, $adminId, 'utilisateur', $userId, 'nom', $oldUser['nom'], $old['nom']);
                            }
                            if ($oldUser['prenom'] !== ($old['prenom'] ?? '')) {
                                logModification($this->pdo, $userId, $adminId, 'utilisateur', $userId, 'prenom', $oldUser['prenom'], $old['prenom'] ?? '');
                            }
                            if ($oldUser['email'] !== $old['email']) {
                                logModification($this->pdo, $userId, $adminId, 'utilisateur', $userId, 'email', $oldUser['email'], $old['email']);
                            }
                        }
                    }
                }

                if (empty($errors)) {
                    // Traiter l'upload de photo si fourni (supprimer l'ancienne si nouvelle photo)
                    $oldPhotoPath = null;
                    if (!$isNew && $user !== null) {
                        $oldPhotoPath = $user['photo_profil'] ?? null;
                    }
                    $photoPath = $this->handlePhotoUpload($userId, $_FILES['photo_profil'] ?? null, $oldPhotoPath);
                    if ($photoPath !== null) {
                        $this->userModel->updateProfilePhoto($userId, $photoPath);
                        logModification($this->pdo, $userId, $adminId, 'utilisateur', $userId, 'photo_profil', $oldPhotoPath, $photoPath);
                    }

                    // Récupérer l'ANCIEN profil AVANT de le modifier
                    $oldProfile = $this->profilModel->findByUserId($userId);
                    
                    error_log("DEBUG profilForm: userId=$userId, oldProfile=" . json_encode($oldProfile) . ", newPoids=" . $old['poids_kg']);
                    
                    // Maintenant on modifie
                    $this->profilModel->save($userId, [
                        'poids_kg' => (float) $old['poids_kg'],
                        'taille_cm' => (int) $old['taille_cm'],
                        'objectif' => $old['objectif'],
                        'allergies' => $old['allergies'] ?? '',
                        'regimes' => $old['regimes'] ?? '',
                        'intolerances' => $old['intolerances'] ?? '',
                    ]);
                    
                    // Enregistrer les modifications du profil nutritionnel
                    if ($oldProfile !== null) {
                        error_log("DEBUG: Comparing profil oldPoids=" . $oldProfile['poids_kg'] . " vs newPoids=" . $old['poids_kg']);
                        if ($oldProfile['poids_kg'] != $old['poids_kg']) {
                            error_log("DEBUG: Poids changed! Recording modification...");
                            logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'poids_kg', $oldProfile['poids_kg'], $old['poids_kg']);
                        }
                        if ($oldProfile['taille_cm'] != $old['taille_cm']) {
                            logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'taille_cm', $oldProfile['taille_cm'], $old['taille_cm']);
                        }
                        if ($oldProfile['objectif'] !== $old['objectif']) {
                            logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'objectif', $oldProfile['objectif'], $old['objectif']);
                        }
                        if ($oldProfile['allergies'] !== ($old['allergies'] ?? '')) {
                            logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'allergies', $oldProfile['allergies'], $old['allergies'] ?? '');
                        }
                        if ($oldProfile['regimes'] !== ($old['regimes'] ?? '')) {
                            logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'regimes', $oldProfile['regimes'], $old['regimes'] ?? '');
                        }
                        if ($oldProfile['intolerances'] !== ($old['intolerances'] ?? '')) {
                            logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'intolerances', $oldProfile['intolerances'], $old['intolerances'] ?? '');
                        }
                    } else {
                        // Nouvelle création
                        logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'poids_kg', null, $old['poids_kg']);
                        logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'taille_cm', null, $old['taille_cm']);
                        logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'objectif', null, $old['objectif']);
                        logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'allergies', null, $old['allergies'] ?? '');
                        logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'regimes', null, $old['regimes'] ?? '');
                        logModification($this->pdo, $userId, $adminId, 'profil_nutritionnel', $userId, 'intolerances', null, $old['intolerances'] ?? '');
                    }
                    
                    redirect(buildRoute('module2.back.dashboard.profils'));
                }
            }
        } elseif ($user !== null) {
            $old = [
                'id' => (string) $user['id'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'email' => $user['email'],
                'poids_kg' => $profile['poids_kg'] ?? '',
                'taille_cm' => $profile['taille_cm'] ?? '',
                'objectif' => $profile['objectif'] ?? 'maintien',
                'allergies' => $profile['allergies'] ?? '',
                'regimes' => $profile['regimes'] ?? '',
                'intolerances' => $profile['intolerances'] ?? '',
            ];
            $isNew = false;
        }

        require __DIR__ . '/../views/module2/back/profil_form.php';
    }

    private function validateAdminProfileForm(array $data): array
    {
        $errors = [];

        if ($data['nom'] === '') {
            $errors['nom'] = 'Le nom du client est requis.';
        }
        if ($data['email'] === '') {
            $errors['email'] = 'Le courriel du client est requis.';
        } elseif (!validateEmail($data['email'])) {
            $errors['email'] = 'Le courriel du client est invalide.';
        } elseif (!empty($data['id']) && $this->userModel->existsByEmailExceptId($data['email'], (int) $data['id'])) {
            $errors['email'] = 'Ce courriel est déjà utilisé par un autre compte.';
        } elseif (empty($data['id']) && $this->userModel->existsByEmail($data['email'])) {
            $errors['email'] = 'Ce courriel est déjà utilisé par un autre compte.';
        }
        if ($data['poids_kg'] === '' || !is_numeric($data['poids_kg']) || (float) $data['poids_kg'] <= 0) {
            $errors['poids_kg'] = 'Le poids doit être un nombre positif.';
        }
        if ($data['taille_cm'] === '' || !ctype_digit($data['taille_cm']) || (int) $data['taille_cm'] <= 0) {
            $errors['taille_cm'] = 'La taille doit être un entier positif.';
        }

        $allowed = ['perte', 'maintien', 'prise', 'performance'];
        if ($data['objectif'] === '' || !in_array($data['objectif'], $allowed, true)) {
            $errors['objectif'] = 'Choisissez un objectif valide.';
        }

        return $errors;
    }

    private function buildStats(array $profiles): array
    {
        $total = count($profiles);
        $completed = 0;
        $scoreTotal = 0;
        foreach ($profiles as $profile) {
            $scoreTotal += (int) ($profile['score_completion'] ?? 0);
            if ((int) ($profile['score_completion'] ?? 0) === 100) {
                $completed += 1;
            }
        }

        return [
            'scoreCorrespondanceGlobal' => $total > 0 ? round($scoreTotal / $total, 1) : 0,
            'utilisateursProfilComplet' => $completed,
            'utilisateursTotal' => $total,
            'totalRecettesMatchees' => $total * 4,
        ];
    }

    private function filterProfiles(array $profiles, string $searchTerm): array
    {
        $searchTerm = mb_strtolower(trim($searchTerm));
        // Découper la recherche en mots individuels pour gérer "Jean Dupont"
        $searchWords = array_filter(explode(' ', $searchTerm));
        
        if (empty($searchWords)) {
            return $profiles;
        }
        
        return array_filter($profiles, function ($profile) use ($searchWords) {
            $nom = mb_strtolower($profile['nom'] ?? '');
            $prenom = mb_strtolower($profile['prenom'] ?? '');
            $fullName = $prenom . ' ' . $nom; // Permet de chercher "Jean Dupont"
            $email = mb_strtolower($profile['email'] ?? '');
            $objectif = mb_strtolower($profile['objectif'] ?? '');
            $allergies = mb_strtolower($profile['allergies'] ?? '');
            $regimes = mb_strtolower($profile['regimes'] ?? '');
            $intolerances = mb_strtolower($profile['intolerances'] ?? '');
            
            // Chercher si AU MOINS UN mot clé est trouvé dans les données
            foreach ($searchWords as $word) {
                $found = (
                    strpos($nom, $word) !== false ||
                    strpos($prenom, $word) !== false ||
                    strpos($fullName, $word) !== false ||
                    strpos($email, $word) !== false ||
                    strpos($objectif, $word) !== false ||
                    strpos($allergies, $word) !== false ||
                    strpos($regimes, $word) !== false ||
                    strpos($intolerances, $word) !== false
                );
                if ($found) {
                    return true; // Au moins un mot trouvé = afficher le profil
                }
            }
            return false;
        });
    }

    private function isAdminAuthenticated(): bool
    {
        return !empty($_SESSION['admin_id']);
    }

    public function modificationHistory(): void
    {
        if (!$this->isAdminAuthenticated()) {
            redirect(buildRoute('module2.back.login'));
        }

        $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;
        $modifications = [];
        $user = null;

        if ($userId !== null) {
            $user = $this->userModel->findById($userId);
            if ($user === null) {
                redirect(buildRoute('module2.back.dashboard.profils'));
            }

            try {
                $stmt = $this->pdo->prepare('
                    SELECT 
                        m.*,
                        admin.nom as admin_nom,
                        admin.prenom as admin_prenom
                    FROM modifications_log m
                    LEFT JOIN utilisateurs admin ON m.admin_id = admin.id
                    WHERE m.utilisateur_id = ?
                    ORDER BY m.modified_at DESC
                    LIMIT 100
                ');
                $stmt->execute([$userId]);
                $modifications = $stmt->fetchAll();
            } catch (PDOException $e) {
                $modifications = [];
            }
        }

        $utilisateur = $user;
        $pageTitle = $pageTitle ?? 'Historique des modifications - FoodWise Admin';
        $activeNav = 'suivi_nutritionnel';

        require dirname(__DIR__) . '/views/module2/routes_defaults.php';
        require __DIR__ . '/../views/module2/back/layouts/header.php';
        ?>
        <main id="fw-main-content" class="fw-content">
          <div class="fw-grid fw-grid--admin">
            <section class="fw-card" aria-labelledby="fw-history-title">
              <h2 id="fw-history-title" class="fw-card__head"><span aria-hidden="true">📋</span> Historique des modifications</h2>
              <?php if ($user !== null): ?>
                <div class="fw-card__body">
                  <p style="margin-top:0;font-size:0.9rem">Utilisateur : <strong><?= htmlspecialchars((string) ($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></p>
                  
                  <?php if (empty($modifications)): ?>
                    <div class="fw-alert-box fw-alert-box--orange" role="status">
                      <span aria-hidden="true">ℹ</span>
                      <span>Aucune modification enregistrée pour cet utilisateur.</span>
                    </div>
                  <?php else: ?>
                    <div class="fw-table-wrap">
                      <table class="fw-table">
                        <thead>
                          <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Type</th>
                            <th scope="col">Camp</th>
                            <th scope="col">Ancienne valeur</th>
                            <th scope="col">Nouvelle valeur</th>
                            <th scope="col">Modifié par</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($modifications as $mod): ?>
                            <tr>
                              <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string) $mod['modified_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                              <td><?= htmlspecialchars((string) $mod['entity_type'], ENT_QUOTES, 'UTF-8') ?></td>
                              <td><?= htmlspecialchars((string) $mod['field_name'], ENT_QUOTES, 'UTF-8') ?></td>
                              <td style="max-width:200px;overflow:auto;font-size:0.85rem"><?= $mod['old_value'] ? htmlspecialchars(substr((string) $mod['old_value'], 0, 50), ENT_QUOTES, 'UTF-8') : '—' ?></td>
                              <td style="max-width:200px;overflow:auto;font-size:0.85rem"><?= htmlspecialchars(substr((string) $mod['new_value'], 0, 50), ENT_QUOTES, 'UTF-8') ?></td>
                              <td><?= $mod['admin_id'] ? htmlspecialchars((string) ($mod['admin_prenom'] ?? '') . ' ' . ($mod['admin_nom'] ?? ''), ENT_QUOTES, 'UTF-8') : 'Système' ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php endif; ?>
                  
                  <div style="margin-top:1rem">
                    <a class="fw-btn fw-btn--ghost" href="<?= htmlspecialchars($routesModule2['back_dashboard_profils'] ?? '', ENT_QUOTES, 'UTF-8') ?>">Retour au dashboard</a>
                  </div>
                </div>
              <?php else: ?>
                <div class="fw-card__body">
                  <div class="fw-alert-box fw-alert-box--red" role="alert">
                    <span aria-hidden="true">⚠</span>
                    <span>Utilisateur non trouvé.</span>
                  </div>
                </div>
              <?php endif; ?>
            </section>
          </div>
        </main>
        <?php
        require __DIR__ . '/../views/module2/back/layouts/footer.php';
    }

    private function handlePhotoUpload(int $userId, ?array $file = null, ?string $oldPhotoPath = null): ?string
    {
        // Si pas de fichier ou pas d'upload, retourner null sans erreur
        if ($file === null || empty($file['tmp_name'])) {
            return null;
        }

        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            // Silencieusement ignorer les erreurs pour les uploads optionnels
            return null;
        }

        // Valider la taille (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        // Valider l'extension du fichier (méthode plus simple)
        $filename = $file['name'];
        $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (!in_array($fileExt, $allowedExts, true)) {
            return null;
        }

        // Créer le dossier s'il n'existe pas
        $uploadDir = __DIR__ . '/../views/module2/assets/uploads/profils/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Générer un nom de fichier unique sécurisé
        $newFilename = 'profil_' . $userId . '_' . time() . '.' . $fileExt;
        $newFilepath = $uploadDir . $newFilename;

        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $newFilepath)) {
            // Supprimer l'ancienne photo si elle existe
            if ($oldPhotoPath !== null) {
                $oldPath = __DIR__ . '/../../' . $oldPhotoPath;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            // Retourner le chemin relatif pour la base de données
            return 'app/views/module2/assets/uploads/profils/' . $newFilename;
        }

        return null;
    }
}

