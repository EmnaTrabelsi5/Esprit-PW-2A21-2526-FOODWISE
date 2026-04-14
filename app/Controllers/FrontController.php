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
        $score = $profile['score_completion'] ?? 0;
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
                $this->profilModel->save((int) $user['id'], [
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

            if (empty($errors)) {
                $userId = $this->userModel->create(
                    $old['nom'],
                    $old['prenom'] ?? '',
                    $old['email'],
                    $old['password']
                );
                $_SESSION['user_id'] = $userId;
                redirect(buildRoute('module2.front.mon_profil'));
            }
        }

        require __DIR__ . '/../views/module2/front/inscription.php';
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
}
