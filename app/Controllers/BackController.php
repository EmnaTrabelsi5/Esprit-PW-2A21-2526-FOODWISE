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

    public function dashboardProfils(): void
    {
        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
            $userId = (int) $_GET['id'];
            $this->profilModel->deleteByUserId($userId);
            redirect(buildRoute('module2.back.dashboard.profils'));
        }

        $lignesProfils = $this->profilModel->findAll();
        $stats = $this->buildStats($lignesProfils);
        require __DIR__ . '/../views/module2/back/dashboard_profils.php';
    }

    public function profilForm(): void
    {
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
                    }
                } else {
                    $userId = (int) $old['id'];
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
                    }
                }

                if (empty($errors)) {
                    $this->profilModel->save($userId, [
                        'poids_kg' => (float) $old['poids_kg'],
                        'taille_cm' => (int) $old['taille_cm'],
                        'objectif' => $old['objectif'],
                        'allergies' => $old['allergies'] ?? '',
                        'regimes' => $old['regimes'] ?? '',
                        'intolerances' => $old['intolerances'] ?? '',
                    ]);
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
}
