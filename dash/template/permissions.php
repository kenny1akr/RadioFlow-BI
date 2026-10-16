<?php
/**
 * dash/template/permissions.php
 * Définition centralisée des rôles et permissions — RadioFlow BI
 * Basé sur la table `roles` et `role_permissions` de la BDD
 */

$ROLES = [
    // ============================================================
    // ADMINISTRATEUR / DG — Accès total
    // ============================================================
    "admin" => [
        "id_role"     => 1,
        "label_fr"    => "Administrateur / DG",
        "label_en"    => "Administrator / GM",
        "label_rn"    => "Umuyobozi",
        "description" => "Accès total, contrôle temps réel de la caisse, validation des remises/annulations, rapports BI",
        "color"       => "red",
        "niveau_acces"=> 5,
        "permissions" => ["*"],
    ],

    // ============================================================
    // CAISSIER / COMPTABLE — Caisse & facturation
    // ============================================================
    "caissier" => [
        "id_role"     => 2,
        "label_fr"    => "Caissier / Comptable",
        "label_en"    => "Cashier / Accountant",
        "label_rn"    => "Umubitsi",
        "description" => "Saisie des encaissements, émission de reçus, demande d'annulation, clôture de caisse",
        "color"       => "yellow",
        "niveau_acces"=> 3,
        "permissions" => [
            "dashboard.view",
            "caisse.view", "caisse.saisie", "caisse.cloture", "caisse.demande_annulation",
            "factures.view", "factures.creer", "factures.imprimer",
            "documents.devis", "documents.proforma", "documents.facture", "documents.recu",
            "paiements.enregistrer",
            "rapports.caisse", "rapports.quotidien",
            "clients.view", "clients.create",
            "contrats.view",
            "messagerie.view", "messagerie.send",
            "rappels.view",
            "profile.view",
        ],
    ],

    // ============================================================
    // RESPONSABLE COMMERCIAL — Clients, contrats, campagnes
    // ============================================================
    "commercial" => [
        "id_role"     => 3,
        "label_fr"    => "Responsable Commercial",
        "label_en"    => "Sales Manager",
        "label_rn"    => "Umucuruzi",
        "description" => "Saisie des prospects, contrats, campagnes publicitaires, accès aux factures",
        "color"       => "green",
        "niveau_acces"=> 3,
        "permissions" => [
            "dashboard.view",
            "clients.view", "clients.create", "clients.edit",
            "contrats.view", "contrats.create", "contrats.edit",
            "campagnes.view", "campagnes.create", "campagnes.edit",
            "spots.view", "spots.upload",
            "factures.view",
            "planification.view",
            "rapports.commercial",
            "messagerie.view", "messagerie.send",
            "rappels.view",
            "profile.view",
        ],
    ],

    // ============================================================
    // DIRECTEUR DES PROGRAMMES — Émissions, grilles, conducteurs
    // ============================================================
    "programmes" => [
        "id_role"     => 4,
        "label_fr"    => "Directeur des Programmes",
        "label_en"    => "Program Director",
        "label_rn"    => "Umuyobozi w'Ibiganiro",
        "description" => "Gestion des émissions, validation des conducteurs et des grilles",
        "color"       => "purple",
        "niveau_acces"=> 4,
        "permissions" => [
            "dashboard.view",
            "emissions.view", "emissions.create", "emissions.edit", "emissions.valider",
            "grille.view", "grille.create", "grille.edit", "grille.valider",
            "conducteurs.view", "conducteurs.valider",
            "animateurs.view", "animateurs.assigner",
            "invites.view", "invites.create",
            "planification.view",
            "concours.view", "concours.create",
            "gagnants.view",
            "interactions.view",
            "rapports.antenne",
            "messagerie.view", "messagerie.send",
            "rappels.view",
            "profile.view",
        ],
    ],

    // ============================================================
    // ANIMATEUR / TECHNICIEN — Conducteur uniquement
    // ============================================================
    "animateur" => [
        "id_role"     => 5,
        "label_fr"    => "Animateur / Technicien",
        "label_en"    => "Host / Technician",
        "label_rn"    => "Umutangaza",
        "description" => "Consultation du conducteur de l'émission, enregistrement des listes de lecture",
        "color"       => "teal",
        "niveau_acces"=> 1,
        "permissions" => [
            "dashboard.view",
            "conducteurs.view", "conducteurs.editer_playlist",
            "grille.view",
            "emissions.view",
            "elements.view",
            "as_played.view", "as_played.marquer",
            "profile.view",
        ],
    ],
];

// ============================================================
// FONCTIONS DE VÉRIFICATION
// ============================================================

if (!function_exists('userCan')) {
    function userCan(array $user, string $permission): bool
    {
        global $ROLES;
        $role = $user["role_system"] ?? "";
        if (!isset($ROLES[$role])) return false;

        $permissions = $ROLES[$role]["permissions"] ?? [];
        if (in_array("*", $permissions, true)) return true;
        if (in_array($permission, $permissions, true)) return true;

        $module = explode(".", $permission)[0] ?? "";
        if ($module && in_array("$module.*", $permissions, true)) return true;

        return false;
    }
}

if (!function_exists('userHasAny')) {
    function userHasAny(array $user, array $permissions): bool
    {
        foreach ($permissions as $p) {
            if (userCan($user, $p)) return true;
        }
        return false;
    }
}

if (!function_exists('getRoleLabel')) {
    function getRoleLabel(string $role, string $lang = 'fr'): string
    {
        global $ROLES;
        if (!isset($ROLES[$role])) {
            return ucfirst(str_replace("_", " ", $role));
        }
        $key = "label_" . (in_array($lang, ['fr','en','rn']) ? $lang : 'fr');
        return $ROLES[$role][$key] ?? $ROLES[$role]["label_fr"] ?? $role;
    }
}

if (!function_exists('getRoleColor')) {
    function getRoleColor(string $role): string
    {
        global $ROLES;
        return $ROLES[$role]["color"] ?? "slate";
    }
}

if (!function_exists('getRoleNiveau')) {
    function getRoleNiveau(string $role): int
    {
        global $ROLES;
        return (int) ($ROLES[$role]["niveau_acces"] ?? 0);
    }
}

/**
 * Détecte la langue courante (session > query > défaut)
 */
if (!function_exists('currentLang')) {
    function currentLang(): string
    {
        if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], ['fr','rn','en'])) {
            return $_SESSION['lang'];
        }
        if (!empty($_GET['lang']) && in_array($_GET['lang'], ['fr','rn','en'])) {
            $_SESSION['lang'] = $_GET['lang'];
            return $_GET['lang'];
        }
        return defined('DEFAULT_LANG') ? DEFAULT_LANG : 'fr';
    }
}