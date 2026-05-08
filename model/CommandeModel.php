<?php
/* model : CommandeModel.php */ 
require_once __DIR__ . '/../config/config.php';

class CommandeModel {

    public static function create($id_offre, $id_client, $quantite, $adresse, $telephone, $mode_paiement, $note) {
        try {
            $db = config::getConnexion();

            // Vérifier la validité des paramètres
            if (!$id_offre || !$id_client || $quantite <= 0) {
                error_log("Paramètres invalides: id_offre=$id_offre, id_client=$id_client, quantite=$quantite");
                return false;
            }

            // Vérifier que l'offre existe et est disponible (plus flexible)
            $sql = "SELECT id, stock, statut FROM `Offre` WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id_offre]);
            $offre = $stmt->fetch();

            if (!$offre) {
                error_log("Offre non trouvée: id_offre=$id_offre");
                return false; // Offre n'existe pas
            }

            error_log("Offre trouvée - Stock: {$offre->stock}, Statut: {$offre->statut}, Quantité demandée: $quantite");

            // Vérifier statut et stock
            if ($offre->statut === 'epuise' || $offre->statut === 'expire') {
                error_log("Offre indisponible (statut={$offre->statut}): id_offre=$id_offre");
                return false;
            }

            if ($offre->stock < $quantite) {
                error_log("Stock insuffisant: disponible={$offre->stock}, demandé=$quantite");
                return false;
            }

            // Créer la commande
            $sql = "INSERT INTO `commande` (id_offre, id_client, quantite, statut, paiement_status, adresse_livraison, telephone, mode_paiement, note, date_commande)
                    VALUES (?, ?, ?, 'en_attente', 'non_paye', ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$id_offre, $id_client, $quantite, $adresse, $telephone, $mode_paiement, $note]);

            if ($result) {
                // Mettre à jour le stock de l'offre
                $sql = "UPDATE `Offre` SET stock = stock - ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$quantite, $id_offre]);

                // Vérifier si l'offre devient épuisée
                $sql = "UPDATE `Offre` SET statut = 'epuise' WHERE id = ? AND stock <= 0";
                $stmt = $db->prepare($sql);
                $stmt->execute([$id_offre]);
                
                error_log("Commande créée avec succès: id_offre=$id_offre, quantite=$quantite");
            }

            return $result;
        } catch (Exception $e) {
            error_log("Erreur lors de la création de commande: " . $e->getMessage());
            return false;
        }
    }

    public static function createMultiple(array $items, int $id_client, string $adresse, string $telephone, string $mode_paiement, string $note): bool {
        $db = config::getConnexion();

        try {
            $db->beginTransaction();

            foreach ($items as $item) {
                $id_offre = (int)($item['id_offre'] ?? 0);
                $quantite = (int)($item['quantite'] ?? 0);

                if (!$id_offre || $quantite <= 0) {
                    throw new Exception("Article de commande invalide.");
                }

                $sql = "SELECT id, stock, statut FROM `Offre` WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$id_offre]);
                $offre = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$offre) {
                    throw new Exception("Offre introuvable: id_offre=$id_offre");
                }

                if ($offre['statut'] === 'epuise' || $offre['statut'] === 'expire') {
                    throw new Exception("Offre indisponible: id_offre=$id_offre");
                }

                if ($offre['stock'] < $quantite) {
                    throw new Exception("Stock insuffisant pour l'offre id_offre=$id_offre");
                }

                $sql = "INSERT INTO `commande` (id_offre, id_client, quantite, statut, paiement_status, adresse_livraison, telephone, mode_paiement, note, date_commande)
                        VALUES (?, ?, ?, 'en_attente', 'non_paye', ?, ?, ?, ?, NOW())";
                $stmt = $db->prepare($sql);
                $stmt->execute([$id_offre, $id_client, $quantite, $adresse, $telephone, $mode_paiement, $note]);

                $sql = "UPDATE `Offre` SET stock = stock - ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$quantite, $id_offre]);

                $sql = "UPDATE `Offre` SET statut = 'epuise' WHERE id = ? AND stock <= 0";
                $stmt = $db->prepare($sql);
                $stmt->execute([$id_offre]);
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Erreur lors de la création de commande groupée: " . $e->getMessage());
            return false;
        }
    }

    public static function getByClient($id_client) {
        $db = config::getConnexion();
$sql = $sql = "SELECT c.*, o.titre AS offre_nom 
        FROM commande c
        JOIN Offre o ON c.id_offre = o.id
        WHERE c.id_client = ?
        ORDER BY c.date_commande DESC
        LIMIT 20";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id_client]);
        return $stmt->fetchAll();
    }
    public static function getById($id) {
    $db = config::getConnexion();

    $sql = "SELECT c.*, o.titre AS offre_nom 
            FROM commande c
            JOIN Offre o ON c.id_offre = o.id
            WHERE c.id_commande = ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetch();
}

    public static function cancel($id) {
        $db = config::getConnexion();
        $sql = "UPDATE commande SET statut='annule' WHERE id_commande=? AND statut='en_attente'";
        return $db->prepare($sql)->execute([$id]);
    }

/*public static function pay($id) {
    $db = config::getConnexion();

    $sql = "UPDATE commande 
            SET paiement_status='paye', statut='confirme' 
            WHERE id_commande=?";

    return $db->prepare($sql)->execute([$id]);
}*/

    public static function getAll() {
        $db = config::getConnexion();
$sql = "SELECT c.*, o.titre AS offre_nom 
        FROM commande c 
        JOIN Offre o ON c.id_offre = o.id";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateStatus($id, $status) {
        $db = config::getConnexion();
        $sql = "UPDATE commande SET statut=? WHERE id_commande=?";
        return $db->prepare($sql)->execute([$status, $id]);
    }

    // ── Récupérer tous les commercants (pour les formulaires) ──
    public static function findAll($search = '', $statut = '') {
        $db = config::getConnexion();
        $sql = "SELECT id, nom, ville FROM Commercant WHERE 1=1";
        $params = [];

        if (!empty($statut)) {
            $sql .= " AND statut = ?";
            $params[] = $statut;
        }

        if (!empty($search)) {
            $sql .= " AND nom LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $sql .= " ORDER BY nom ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Récupérer toutes les villes ────────────────────────────
    public static function getVilles() {
        $db = config::getConnexion();
        $sql = "SELECT DISTINCT ville FROM Commercant WHERE ville IS NOT NULL ORDER BY ville ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
       /* public function create($data) {
        $query = "INSERT INTO commande 
                  (id_offre, id_client, quantite, adresse_livraison, telephone, mode_paiement)
                  VALUES (:offre, :client, :qte, :adresse, :tel, :mode)";

        $stmt = $this->$db->prepare($query);

        return $stmt->execute([
            ":offre" => $data['offre'],
            ":client" => $data['client'],
            ":qte" => $data['qte'],
            ":adresse" => $data['adresse'],
            ":tel" => $data['tel'],
            ":mode" => $data['mode']
        ]);
    }*/

    // 🔥 Changer statut
    public function updateStatut($id, $statut) {
        $query = "UPDATE commande SET statut = :statut WHERE id_commande = :id";
        $stmt = $this->$db->prepare($query);

        return $stmt->execute([
            ":statut" => $statut,
            ":id" => $id
        ]);
    }

    // 🔥 Simulation paiement
public static function simulatePayment($id_commande, $mode_paiement) {
    $db = config::getConnexion();

    // 1. vérifier commande
    $sql = "SELECT * FROM commande WHERE id_commande = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id_commande]);
    $commande = $stmt->fetch();

    if (!$commande) {
        return false;
    }

    // 2. paiement espèces = direct success
    if ($mode_paiement === 'especes') {
        $sql = "UPDATE commande 
                SET paiement_status='paye', statut='confirme'
                WHERE id_commande=?";
        return $db->prepare($sql)->execute([$id_commande]);
    }

    // 3. carte = simulation
    $rand = rand(1, 100);

    if ($rand <= 90) {
        $status = 'paye';
        $order_status = 'confirme';
    } else {
        $status = 'failed';
        $order_status = 'en_attente';
    }

    $sql = "UPDATE commande 
            SET paiement_status=?, statut=?
            WHERE id_commande=?";

    return $db->prepare($sql)->execute([
        $status,
        $order_status,
        $id_commande
    ]);
}
    public static function getByIdSimple($id) {
    $db = config::getConnexion();

    $sql = "SELECT * FROM commande WHERE id_commande = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}

