<?php
declare(strict_types=1);

class FrontController
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

    public function monProfil(): void
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            redirect(buildRoute('module2.front.connexion'));
        }

        $profile = $this->profilModel->findByUserId((int) $user['id']);
        
        // Préparer les données pour la vue
        $utilisateur = $user;
        
        // Transformer les données du profil pour l'affichage
        $profilNutritionnel = [
            'poids_kg' => $profile['poids_kg'] ?? null,
            'taille_cm' => $profile['taille_cm'] ?? null,
            'objectif' => $profile['objectif'] ?? null,
            'allergies' => !empty($profile['allergies']) ? array_map('trim', explode(',', $profile['allergies'])) : [],
            'regimes' => !empty($profile['regimes']) ? array_map('trim', explode(',', $profile['regimes'])) : [],
            'intolerances' => !empty($profile['intolerances']) ? array_map('trim', explode(',', $profile['intolerances'])) : [],
            'scoreCompletion' => $profile['score_completion'] ?? 0,
        ];
        
        $photoProfil = $utilisateur['photo_profil'] ?? null;
        
        require __DIR__ . '/../views/module2/front/mon_profil.php';
    }

    public function profilEdit(): void
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            redirect(buildRoute('module2.front.connexion'));
        }

        $errors = [];
        $old = [];
        $profile = $this->profilModel->findByUserId((int) $user['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array_map('trim', $_POST);
            $errors = $this->validateProfileForm($old);

            if (empty($errors)) {
                $userId = (int) $user['id'];
                
                // Traiter l'upload de photo si fourni (supprimer l'ancienne si nouvelle photo)
                $photoPath = $this->handlePhotoUpload($userId, $_FILES['photo_profil'] ?? null, $user['photo_profil'] ?? null);
                if ($photoPath !== null) {
                    $this->userModel->updateProfilePhoto($userId, $photoPath);
                }
                
                $this->profilModel->save($userId, [
                    'poids_kg' => (float) $old['poids_kg'],
                    'taille_cm' => (int) $old['taille_cm'],
                    'objectif' => $old['objectif'],
                    'allergies' => $old['allergies'] ?? '',
                    'regimes' => $old['regimes'] ?? '',
                    'intolerances' => $old['intolerances'] ?? '',
                ]);
                redirect(buildRoute('module2.front.mon_profil'));
            }
        } else {
            $old = [
                'poids_kg' => $profile['poids_kg'] ?? '',
                'taille_cm' => $profile['taille_cm'] ?? '',
                'objectif' => $profile['objectif'] ?? '',
                'allergies' => $profile['allergies'] ?? '',
                'regimes' => $profile['regimes'] ?? '',
                'intolerances' => $profile['intolerances'] ?? '',
            ];
        }

        $utilisateur = $user;
        $profilNutritionnel = $old;
        require __DIR__ . '/../views/module2/front/profil_edit.php';
    }

    public function connexion(): void
    {
        $errors = [];
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array_map('trim', $_POST);
            if ($old['email'] === '') {
                $errors['email'] = 'Veuillez saisir votre adresse e-mail.';
            } elseif (!validateEmail($old['email'])) {
                $errors['email'] = 'Le format de l’adresse est invalide.';
            }

            if ($old['password'] === '') {
                $errors['password'] = 'Veuillez saisir votre mot de passe.';
            }

            if (empty($errors)) {
                $user = $this->userModel->authenticate($old['email'], $old['password']);
                if ($user === null) {
                    $errors['email'] = 'Identifiants incorrects.';
                } else {
                    $_SESSION['user_id'] = (int) $user['id'];
                    
                    // Si c'est un admin, le rediriger vers le back-office
                    if (($user['role'] ?? 'user') === 'admin') {
                        $_SESSION['admin_id'] = (int) $user['id'];
                        redirect(buildRoute('module2.back.dashboard.profils'));
                    }
                    
                    redirect(buildRoute('module2.front.mon_profil'));
                }
            }
        }

        require __DIR__ . '/../views/module2/front/connexion.php';
    }

    public function inscription(): void
    {
        $errors = [];
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array_map('trim', $_POST);
            if ($old['nom'] === '') {
                $errors['nom'] = 'Le nom est requis.';
            }
            if ($old['email'] === '') {
                $errors['email'] = 'Le courriel est requis.';
            } elseif (!validateEmail($old['email'])) {
                $errors['email'] = 'Le courriel est invalide.';
            } elseif ($this->userModel->existsByEmail($old['email'])) {
                $errors['email'] = 'Cette adresse est déjà utilisée.';
            }
            if ($old['password'] === '') {
                $errors['password'] = 'Le mot de passe est requis.';
            } elseif (strlen($old['password']) < 8) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }
            if ($old['password_confirm'] !== $old['password']) {
                $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
            }

            // Valider les champs du profil nutritionnel
            $profileErrors = $this->validateProfileForm($old);
            $errors = array_merge($errors, $profileErrors);

            if (empty($errors)) {
                $userId = $this->userModel->create(
                    $old['nom'],
                    $old['prenom'] ?? '',
                    $old['email'],
                    $old['password']
                );
                
                // Traiter l'upload de photo si fourni
                $photoPath = $this->handlePhotoUpload($userId, $_FILES['photo_profil'] ?? null);
                if ($photoPath !== null) {
                    $this->userModel->updateProfilePhoto($userId, $photoPath);
                }
                
                // Créer le profil nutritionnel lors de l'inscription
                $this->profilModel->save($userId, [
                    'poids_kg' => (float) $old['poids_kg'],
                    'taille_cm' => (int) $old['taille_cm'],
                    'objectif' => $old['objectif'],
                    'allergies' => $old['allergies'] ?? '',
                    'regimes' => $old['regimes'] ?? '',
                    'intolerances' => $old['intolerances'] ?? '',
                ]);
                
                $_SESSION['user_id'] = $userId;
                redirect(buildRoute('module2.front.mon_profil'));
            }
        }

        require __DIR__ . '/../views/module2/front/inscription.php';
    }

    public function passwordReset(): void
    {
        $errors = [];
        $old = [];
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array_map('trim', $_POST);
            if ($old['email'] === '') {
                $errors['email'] = 'Veuillez saisir votre adresse courriel.';
            } elseif (!validateEmail($old['email'])) {
                $errors['email'] = 'Le format de l\'adresse est invalide.';
            } else {
                $user = $this->userModel->findByEmail($old['email']);
                if ($user === null) {
                    // Pour la sécurité, on affiche le même message même si l'email n'existe pas
                    $successMessage = 'Si ce courriel existe, vous recevrez un lien pour réinitialiser votre mot de passe.';
                } else {
                    // À implémenter : envoyer un email avec un lien de réinitialisation
                    // Pour maintenant, afficher un message de succès
                    $successMessage = 'Un email a été envoyé à ' . htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') . ' avec les instructions de réinitialisation.';
                }
            }
        }

        require __DIR__ . '/../views/module2/front/mot_de_passe_oublie.php';
    }

    public function allergiesRegimes(): void
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            redirect(buildRoute('module2.front.connexion'));
        }

        $errors = [];
        $old = [];
        $profile = $this->profilModel->findByUserId((int) $user['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array_map('trim', $_POST);
            if ($old['allergies'] === '' && $old['regimes'] === '' && $old['intolerances'] === '') {
                $errors['global'] = 'Au moins un champ doit être renseigné.';
            }
            if (empty($errors)) {
                $this->profilModel->save((int) $user['id'], [
                    'poids_kg' => (float) ($profile['poids_kg'] ?? 0),
                    'taille_cm' => (int) ($profile['taille_cm'] ?? 0),
                    'objectif' => $profile['objectif'] ?? 'maintien',
                    'allergies' => $old['allergies'] ?? '',
                    'regimes' => $old['regimes'] ?? '',
                    'intolerances' => $old['intolerances'] ?? '',
                ]);
                redirect(buildRoute('module2.front.mon_profil'));
            }
        } else {
            $old = [
                'allergies' => $profile['allergies'] ?? '',
                'regimes' => $profile['regimes'] ?? '',
                'intolerances' => $profile['intolerances'] ?? '',
            ];
        }

        $profilNutritionnel = $old;
        require __DIR__ . '/../views/module2/front/allergies_regimes.php';
    }

    public function logout(): void
    {
        session_destroy();
        redirect(buildRoute('module2.front.connexion'));
    }

    private function getCurrentUser(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        return $this->userModel->findById((int) $_SESSION['user_id']);
    }

    private function validateProfileForm(array $data): array
    {
        $errors = [];
        if ($data['poids_kg'] === '') {
            $errors['poids_kg'] = 'Le poids est requis.';
        } elseif (!is_numeric($data['poids_kg']) || (float) $data['poids_kg'] <= 0) {
            $errors['poids_kg'] = 'Le poids doit être un nombre positif.';
        }

        if ($data['taille_cm'] === '') {
            $errors['taille_cm'] = 'La taille est requise.';
        } elseif (!ctype_digit($data['taille_cm']) || (int) $data['taille_cm'] <= 0) {
            $errors['taille_cm'] = 'La taille doit être un entier positif.';
        }

        $allowed = ['perte', 'maintien', 'prise', 'performance'];
        if ($data['objectif'] === '' || !in_array($data['objectif'], $allowed, true)) {
            $errors['objectif'] = 'Veuillez choisir un objectif valide.';
        }

        return $errors;
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

