<?php
require_once __DIR__ . '/../config.php';

class CommandeModel {

    public static function create($id_offre, $id_client, $quantite, $adresse, $telephone, $mode_paiement, $note) {
        try {
            $db = config::getConnexion();

            // Vérifier la validité des paramètres
            if (!$id_offre || !$id_client || $quantite <= 0) {
                return false;
            }

            // Vérifier que l'offre existe et est disponible
            $sql = "SELECT stock, statut FROM Offre WHERE id = ? AND statut IN ('disponible', 'epuise')";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id_offre]);
            $offre = $stmt->fetch();

            if (!$offre) {
                return false; // Offre n'existe pas
            }

            if ($offre['statut'] === 'epuise' || $offre['stock'] < $quantite) {
                return false; // Stock insuffisant
            }

            // Créer la commande
            $sql = "INSERT INTO commande (id_offre, id_client, quantite, statut, paiement_status, adresse_livraison, telephone, mode_paiement, note, date_commande)
                    VALUES (?, ?, ?, 'en_attente', 'non_paye', ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$id_offre, $id_client, $quantite, $adresse, $telephone, $mode_paiement, $note]);

            if ($result) {
                // Mettre à jour le stock de l'offre
                $sql = "UPDATE Offre SET stock = stock - ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$quantite, $id_offre]);

                // Vérifier si l'offre devient épuisée
                $sql = "UPDATE Offre SET statut = 'epuise' WHERE id = ? AND stock = 0";
                $stmt = $db->prepare($sql);
                $stmt->execute([$id_offre]);
            }

            return $result;
        } catch (Exception $e) {
            error_log("Erreur lors de la création de commande: " . $e->getMessage());
            return false;
        }
    }

    public static function getByClient($id_client) {
        $db = config::getConnexion();
$sql = "SELECT c.*, o.titre AS offre_nom 
        FROM commande c
        JOIN Offre o ON c.id_offre = o.id
        WHERE c.id_client = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id_client]);
        return $stmt->fetchAll();
    }

    public static function cancel($id) {
        $db = config::getConnexion();
        $sql = "UPDATE commande SET statut='annule' WHERE id_commande=? AND statut='en_attente'";
        return $db->prepare($sql)->execute([$id]);
    }

    public static function pay($id) {
        $db = config::getConnexion();
        $sql = "UPDATE commande SET paiement_status='paye', statut='confirme' WHERE id_commande=?";
        return $db->prepare($sql)->execute([$id]);
    }

    public static function getAll() {
        $db = config::getConnexion();
$sql = "SELECT c.*, o.titre AS offre_nom 
        FROM commande c 
        JOIN Offre o ON c.id_offre = o.id";
        return $db->query($sql)->fetchAll();
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
        return $stmt->fetchAll();
    }

    // ── Récupérer toutes les villes ────────────────────────────
    public static function getVilles() {
        $db = config::getConnexion();
        $sql = "SELECT DISTINCT ville FROM Commercant WHERE ville IS NOT NULL ORDER BY ville ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
