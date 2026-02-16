<?php
namespace app\models;

use PDO;

class DatabaseModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /* ===================== VÉHICULES ===================== */

    public function getVehicules()
    {
        $stmt = $this->db->query("SELECT * FROM vehicules");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVehiculeById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM vehicules WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ===================== CHAUFFEURS ===================== */

    public function getChauffeurs()
    {
        $stmt = $this->db->query("SELECT * FROM chauffeurs");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChauffeurById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM chauffeurs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ===================== HISTO JOUR ===================== */

    public function getTourJours()
    {
        $stmt = $this->db->query("SELECT * FROM histo_jour");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTourJourById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM histo_jour WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ===================== TRAJETS FIXES ===================== */

    public function getTrajetsFixes()
    {
        $stmt = $this->db->query("SELECT * FROM trajets_fixes");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTrajetFixeById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM trajets_fixes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ===================== TRAJETS TOURS ===================== */

    public function getTrajetsTours()
    {
        $stmt = $this->db->query("SELECT * FROM trajets_tours");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTrajetTourById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM trajets_tours WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ===================== JOINTURES ===================== */

    public function getTrajetToursWithDetails()
    {
        $sql = "
            SELECT tt.*, tf.nom_trajet, tf.point_depart, tf.point_arriver,
                   tf.kilometre, c.nom AS chauffeur_nom
            FROM trajets_tours tt
            JOIN trajets_fixes tf ON tt.id_trajet_fixe = tf.id
            JOIN chauffeurs c ON tt.id_chauffeur = c.id
            ORDER BY tt.date_debut DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistoJourWithDetails()
    {
        $sql = "
            SELECT hj.*, c.nom AS chauffeur_nom, v.nom AS vehicule_nom,
                   tf.nom_trajet, tf.point_depart, tf.point_arriver
            FROM histo_jour hj
            JOIN chauffeurs c ON hj.id_chauffeur = c.id
            JOIN vehicules v ON hj.id_vehicule = v.id
            JOIN trajets_fixes tf ON hj.id_trajet_fixe = tf.id
            ORDER BY hj.date_jour DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}