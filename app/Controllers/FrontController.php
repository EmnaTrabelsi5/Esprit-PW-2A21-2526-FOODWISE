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
            'show_weight' => $profile['show_weight'] ?? 0,
            'show_height' => $profile['show_height'] ?? 0,
            'show_diet' => $profile['show_diet'] ?? 1,
            'show_allergies' => $profile['show_allergies'] ?? 1,
            'show_goal' => $profile['show_goal'] ?? 1,
            'show_intolerances' => $profile['show_intolerances'] ?? 1,
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
                
                // Standardiser les allergies, régimes et intolérances
                $allergies = AllergenMappings::standardizeAllergenList($old['allergies'] ?? '');
                $regimes = AllergenMappings::standardizeRegimeList($old['regimes'] ?? '');
                $intolerances = AllergenMappings::standardizeIntoleranceList($old['intolerances'] ?? '');
                
                $this->profilModel->save($userId, [
                    'poids_kg' => (float) $old['poids_kg'],
                    'taille_cm' => (int) $old['taille_cm'],
                    'objectif' => $old['objectif'],
                    'allergies' => $allergies,
                    'regimes' => $regimes,
                    'intolerances' => $intolerances,
                    'show_weight' => isset($old['show_weight']) ? 1 : 0,
                    'show_height' => isset($old['show_height']) ? 1 : 0,
                    'show_diet' => isset($old['show_diet']) ? 1 : 0,
                    'show_allergies' => isset($old['show_allergies']) ? 1 : 0,
                    'show_goal' => isset($old['show_goal']) ? 1 : 0,
                    'show_intolerances' => isset($old['show_intolerances']) ? 1 : 0,
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
                'show_weight' => $profile['show_weight'] ?? 0,
                'show_height' => $profile['show_height'] ?? 0,
                'show_diet' => $profile['show_diet'] ?? 1,
                'show_allergies' => $profile['show_allergies'] ?? 1,
                'show_goal' => $profile['show_goal'] ?? 1,
                'show_intolerances' => $profile['show_intolerances'] ?? 1,
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
                // Vérifier d'abord le statut du compte
                $userByEmail = $this->userModel->findByEmail($old['email']);
                if ($userByEmail !== null) {
                    $status = $userByEmail['status'] ?? 'active';
                    if ($status === 'banned') {
                        $errors['global'] = 'Votre compte a été banni. Veuillez contacter le support.';
                    } elseif ($status === 'suspended') {
                        $suspendedUntil = $userByEmail['suspended_until'] ?? null;
                        if ($suspendedUntil !== null && strtotime($suspendedUntil) > time()) {
                            $daysLeft = ceil((strtotime($suspendedUntil) - time()) / (24 * 60 * 60));
                            $errors['global'] = 'Votre compte est suspendu jusqu\'au ' . date('d/m/Y', strtotime($suspendedUntil)) . ' (environ ' . $daysLeft . ' jours).';
                        }
                    }
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
            } elseif (!preg_match("/^[a-zA-ZÀ-ÿ][a-zA-ZÀ-ÿ\s\-]*$/", $old['nom'])) {
                $errors['nom'] = 'Le nom ne doit contenir que des lettres, espaces ou tirets, et ne pas commencer par un chiffre.';
            }
            if ($old['prenom'] === '') {
                $errors['prenom'] = 'Le prénom est requis.';
            } elseif (!preg_match("/^[a-zA-ZÀ-ÿ][a-zA-ZÀ-ÿ\s\-]*$/", $old['prenom'])) {
                $errors['prenom'] = 'Le prénom ne doit contenir que des lettres, espaces ou tirets, et ne pas commencer par un chiffre.';
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
            } elseif (!preg_match('/[A-Z]/', $old['password'])) {
                $errors['password'] = 'Le mot de passe doit contenir au moins une majuscule.';
            } elseif (!preg_match('/[0-9]/', $old['password'])) {
                $errors['password'] = 'Le mot de passe doit contenir au moins un chiffre.';
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
                
                // Standardiser les allergies, régimes et intolérances
                $allergies = AllergenMappings::standardizeAllergenList($old['allergies'] ?? '');
                $regimes = AllergenMappings::standardizeRegimeList($old['regimes'] ?? '');
                $intolerances = AllergenMappings::standardizeIntoleranceList($old['intolerances'] ?? '');
                
                // Créer le profil nutritionnel lors de l'inscription
                $this->profilModel->save($userId, [
                    'poids_kg' => (float) $old['poids_kg'],
                    'taille_cm' => (int) $old['taille_cm'],
                    'objectif' => $old['objectif'],
                    'allergies' => $allergies,
                    'regimes' => $regimes,
                    'intolerances' => $intolerances,
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
                    $successMessage = 'Si ce courriel existe, vous recevrez un code pour réinitialiser votre mot de passe.';
                } else {
                    // Générer un code de réinitialisation aléatoire (6 chiffres)
                    $resetCode = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
                    
                    // Sauvegarder le code en BDD
                    $this->userModel->saveResetCode((int) $user['id'], $resetCode);
                    
                    // Envoyer l'email
                    $mailer = new MailerService();
                    $fullName = trim($user['prenom'] . ' ' . $user['nom']);
                    $mailSent = $mailer->sendResetCodeEmail(
                        $old['email'],
                        $fullName ?: 'Utilisateur',
                        $resetCode
                    );
                    
                    if ($mailSent) {
                        $successMessage = 'Un email a été envoyé à ' . htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') . ' avec votre code de réinitialisation.';
                    } else {
                        // En cas d'erreur d'envoi, on affiche quand même le message pour la sécurité
                        // mais on peut logger l'erreur
                        error_log('Failed to send reset code email to: ' . $old['email']);
                        $successMessage = 'Si ce courriel existe, vous recevrez un code pour réinitialiser votre mot de passe.';
                    }
                }
            }
        }

        require __DIR__ . '/../views/module2/front/mot_de_passe_oublie.php';
    }

    public function verifyResetCode(): void
    {
        $errors = [];
        $old = [];
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array_map('trim', $_POST);
            
            // DEBUG : logger les données reçues
            error_log('DEBUG verifyResetCode - Email: ' . $old['email']);
            error_log('DEBUG verifyResetCode - Code reçu: ' . $old['code']);
            error_log('DEBUG verifyResetCode - Code brut (var_dump): ' . var_export($old['code'], true));
            
            if ($old['email'] === '') {
                $errors['email'] = 'Veuillez saisir votre adresse courriel.';
            } elseif (!validateEmail($old['email'])) {
                $errors['email'] = 'Le format de l\'adresse est invalide.';
            }
            
            if ($old['code'] === '') {
                $errors['code'] = 'Veuillez saisir votre code de réinitialisation.';
            }
            
            if ($old['password'] === '') {
                $errors['password'] = 'Veuillez saisir votre nouveau mot de passe.';
            } elseif (strlen($old['password']) < 8) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
            } elseif (!preg_match('/[A-Z]/', $old['password'])) {
                $errors['password'] = 'Le mot de passe doit contenir au moins une majuscule.';
            } elseif (!preg_match('/[0-9]/', $old['password'])) {
                $errors['password'] = 'Le mot de passe doit contenir au moins un chiffre.';
            }
            
            if ($old['password_confirm'] !== $old['password']) {
                $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
            }

            if (empty($errors)) {
                // Nettoyer le code (supprimer espaces et tirets)
                $cleanedCode = trim(str_replace(['-', ' '], '', $old['code']));
                error_log('DEBUG verifyResetCode - Code nettoyé: ' . $cleanedCode);
                
                // Vérifier le code
                $user = $this->userModel->verifyResetCode($old['email'], $cleanedCode);
                if ($user === null) {
                    error_log('DEBUG verifyResetCode - Vérification échouée pour email: ' . $old['email']);
                    $errors['code'] = 'Le code de réinitialisation est invalide ou a expiré.';
                } else {
                    error_log('DEBUG verifyResetCode - Vérification réussie!');
                    // Mettre à jour le mot de passe
                    $this->userModel->updatePasswordByResetCode((int) $user['id'], $old['password']);
                    $successMessage = 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez vous connecter.';
                    
                    // Rediriger vers la connexion après 3 secondes
                    $redirectUrl = buildRoute('module2.front.connexion');
                    echo "<script>setTimeout(function() { window.location.href = '{$redirectUrl}'; }, 2000);</script>";
                }
            }
        }

        require __DIR__ . '/../views/module2/front/verify_reset_code.php';
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
            if (empty($errors)) {
                // Standardiser les allergies, régimes et intolérances
                $allergies = AllergenMappings::standardizeAllergenList($old['allergies'] ?? '');
                $regimes = AllergenMappings::standardizeRegimeList($old['regimes'] ?? '');
                $intolerances = AllergenMappings::standardizeIntoleranceList($old['intolerances'] ?? '');
                
                $this->profilModel->save((int) $user['id'], [
                    'poids_kg' => (float) ($profile['poids_kg'] ?? 0),
                    'taille_cm' => (int) ($profile['taille_cm'] ?? 0),
                    'objectif' => $profile['objectif'] ?? 'maintien',
                    'allergies' => $allergies,
                    'regimes' => $regimes,
                    'intolerances' => $intolerances,
                    'show_weight' => $profile['show_weight'] ?? 0,
                    'show_height' => $profile['show_height'] ?? 0,
                    'show_diet' => $profile['show_diet'] ?? 1,
                    'show_allergies' => $profile['show_allergies'] ?? 1,
                    'show_goal' => $profile['show_goal'] ?? 1,
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

    /**
     * Affiche le profil public d'un utilisateur en respectant ses paramètres de confidentialité
     */
    public function viewPublicProfile(): void
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            redirect(buildRoute('module2.front.connexion'));
        }

        $userId = isset($_GET['id']) ? (int) $_GET['id'] : null;
        if ($userId === null || $userId === (int) $user['id']) {
            redirect(buildRoute('module2.front.users_list'));
        }

        // Récupérer l'utilisateur à afficher
        $viewedUser = $this->userModel->findById($userId);
        if ($viewedUser === null) {
            redirect(buildRoute('module2.front.users_list'));
        }

        // Récupérer le profil public (en respectant la confidentialité)
        $publicProfile = $this->profilModel->getPublicProfile($userId);

        $pageTitle = htmlspecialchars($viewedUser['prenom'] . ' ' . $viewedUser['nom'], ENT_QUOTES, 'UTF-8');
        $activeNav = 'messages';
        require __DIR__ . '/../views/module2/front/public_view.php';
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
        } elseif ((float) $data['poids_kg'] < 25 || (float) $data['poids_kg'] > 250) {
            $errors['poids_kg'] = 'Veuillez entrer une valeur de poids réaliste.';
        }

        if ($data['taille_cm'] === '') {
            $errors['taille_cm'] = 'La taille est requise.';
        } elseif (!ctype_digit($data['taille_cm']) || (int) $data['taille_cm'] <= 0) {
            $errors['taille_cm'] = 'La taille doit être un entier positif.';
        } elseif ((int) $data['taille_cm'] < 100 || (int) $data['taille_cm'] > 250) {
            $errors['taille_cm'] = 'Veuillez entrer une valeur de taille réaliste.';
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

    /**
     * API : Basculer la visibilité d'un champ du profil
     */
    public function toggleVisibility(): void
    {
        header('Content-Type: application/json');
        
        $user = $this->getCurrentUser();
        if ($user === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $field = $data['field'] ?? null;

        $allowed = ['show_weight', 'show_height', 'show_diet', 'show_allergies', 'show_goal', 'show_intolerances'];
        if (!in_array($field, $allowed, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Champ invalide']);
            return;
        }

        try {
            $profile = $this->profilModel->findByUserId((int) $user['id']);
            if ($profile === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Profil non trouvé']);
                return;
            }

            // Basculer la valeur
            $currentValue = (int) ($profile[$field] ?? 0);
            $newValue = $currentValue ? 0 : 1;

            // Mettre à jour en base de données
            $stmt = $this->pdo->prepare("UPDATE profils_nutritionnels SET $field = :value WHERE utilisateur_id = :userId");
            $stmt->execute([
                ':value' => $newValue,
                ':userId' => (int) $user['id']
            ]);

            echo json_encode(['isPublic' => (bool) $newValue]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
    }
}

