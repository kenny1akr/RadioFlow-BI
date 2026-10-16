<?php
/**
 * router.php
 * Point d'entrée unique — RadioFlow BI
 * Toutes les URLs passent ici.
 */

// ============================================================
// DÉBOGAGE
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// CONFIG & SESSION
// ============================================================
require_once __DIR__ . "/api/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================
// CHARGEMENT DES DÉPENDANCES
// ============================================================
require_once __DIR__ . "/dash/template/permissions.php";
require_once __DIR__ . "/dash/template/sidebar_config.php";
require_once __DIR__ . "/api/database.php";

if (!defined('ROUTER_LOADED')) {
    define('ROUTER_LOADED', true);
}

// ============================================================
// HELPERS D'URL
// ============================================================
if (!defined('APP_BASE_PATH')) {
    $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    define('APP_BASE_PATH', $scriptDirectory === '/' || $scriptDirectory === '.' ? '' : rtrim($scriptDirectory, '/'));
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        return APP_BASE_PATH . '/' . ltrim($path, '/');
    }
}

if (!function_exists('app_route_url')) {
    function app_route_url(string $path, ?string $role = null): string
    {
        $role = $role ?? ($_SESSION['role'] ?? '');
        $path = trim($path, '/');
        if ($role !== '' && $path !== $role && strpos($path, $role . '/') !== 0) {
            $path = $role . '/' . $path;
        }
        return app_url($path);
    }
}

// ============================================================
// CATALOGUE DES ROUTES
// Format : "route" => [fichier, permission, titre]
// ============================================================
$ROUTE_DEFINITIONS = [

    // --- Accueil / Dashboard ---
    "accueil"   => ["./dash/admin/index.php", "dashboard.view", "Tableau de bord"],
    "dashboard" => ["./dash/admin/index.php", "dashboard.view", "Tableau de bord"],

    // --- MODULE 1 : Émissions & Grille Antenne ---
    "emissions"           => ["./dash/programmes/emissions_liste.php",   "emissions.view",   "Émissions"],
    "emissions/liste"     => ["./dash/programmes/emissions_liste.php",   "emissions.view",   "Liste des émissions"],
    "emissions/creer"     => ["./dash/programmes/emission_form.php",     "emissions.create", "Créer une émission"],
    "emissions/modifier"  => ["./dash/programmes/emission_form.php",     "emissions.edit",   "Modifier l'émission"],
    "grille"              => ["./dash/programmes/grille_antenne.php",    "grille.view",      "Grille antenne"],
    "invites"             => ["./dash/programmes/invites_liste.php",     "invites.view",     "Invités"],
    "animateurs"          => ["./dash/programmes/animateurs_liste.php",  "animateurs.view",  "Animateurs"],

    // --- MODULE 2 : Commercial & Publicité ---
    "clients"             => ["./dash/commercial/clients_liste.php",   "clients.view",   "Clients"],
    "clients/liste"       => ["./dash/commercial/clients_liste.php",   "clients.view",   "Liste clients"],
    "clients/ajouter"     => ["./dash/commercial/client_form.php",     "clients.create", "Ajouter un client"],
    "clients/modifier"    => ["./dash/commercial/client_form.php",     "clients.edit",   "Modifier un client"],
    "contrats"            => ["./dash/commercial/contrats_liste.php",  "contrats.view",  "Contrats"],
    "contrats/creer"      => ["./dash/commercial/contrat_form.php",    "contrats.create","Nouveau contrat"],
    "campagnes"           => ["./dash/commercial/campagnes_liste.php", "campagnes.view", "Campagnes"],
    "spots"               => ["./dash/commercial/spots_liste.php",     "spots.view",     "Spots audio"],
    "spots/upload"        => ["./dash/commercial/spot_upload.php",     "spots.upload",   "Uploader un spot"],

    // --- MODULE 3 : Planification (Traffic) ---
    "planification"        => ["./dash/commercial/planification.php",       "planification.view",   "Planning des spots"],
    "planification/pauses" => ["./dash/commercial/pauses_commerciales.php", "planification.pauses", "Pauses commerciales"],
    "planification/alertes"=> ["./dash/commercial/alertes_planif.php",      "planification.alertes","Alertes planning"],

    // --- MODULE 4 : Studio & Logs ---
    "conducteurs"  => ["./dash/programmes/conducteurs_liste.php", "conducteurs.view", "Conducteurs"],
    "elements"     => ["./dash/programmes/elements_liste.php",    "elements.view",    "Playlist / Éléments"],
    "as-played"    => ["./dash/programmes/as_played.php",         "as_played.view",   "As-Played Log"],

    // --- MODULE 5 : Promotions & Auditeurs ---
    "concours"      => ["./dash/programmes/concours_liste.php",   "concours.view",      "Concours"],
    "gagnants"      => ["./dash/programmes/gagnants_liste.php",   "gagnants.view",      "Gagnants"],
    "lots"          => ["./dash/programmes/lots_liste.php",       "concours.view",      "Lots"],
    "interactions"  => ["./dash/programmes/interactions.php",     "interactions.view",  "Messages auditeurs"],

    // --- MODULE 6 : Caisse & Facturation (PRIORITAIRE) ---
    "caisse"                    => ["./dash/caissier/caisse_journal.php",       "caisse.view",              "Journal de caisse"],
    "caisse/journal"            => ["./dash/caissier/caisse_journal.php",       "caisse.view",              "Journal de caisse"],
    "caisse/session"            => ["./dash/caissier/caisse_session.php",       "caisse.view",              "Session de caisse"],
    "caisse/encaissement"       => ["./dash/caissier/encaissement.php",         "caisse.saisie",            "Encaissement"],
    "caisse/rapprochement"      => ["./dash/caissier/rapprochement.php",        "caisse.cloture",           "Rapprochement caisse"],
    "caisse/alertes"            => ["./dash/caissier/alertes_caisse.php",       "caisse.view",              "Alertes caisse"],

    "factures"                  => ["./dash/caissier/factures_liste.php",       "factures.view",            "Factures"],
    "factures/liste"            => ["./dash/caissier/factures_liste.php",       "factures.view",            "Liste des factures"],
    "factures/devis"            => ["./dash/caissier/devis_liste.php",          "documents.devis",          "Devis"],
    "factures/proforma"         => ["./dash/caissier/proforma_liste.php",       "documents.proforma",       "Pro-forma"],
    "factures/recus"            => ["./dash/caissier/recus_liste.php",          "documents.recu",           "Reçus"],
    "factures/creer"            => ["./dash/caissier/facture_form.php",         "factures.creer",           "Créer une facture"],

    "paiements/especes"         => ["./dash/caissier/paiement_especes.php",     "paiements.enregistrer",    "Paiement espèces"],
    "paiements/virement"        => ["./dash/caissier/paiement_virement.php",    "paiements.enregistrer",    "Paiement virement"],
    "paiements/cheque"          => ["./dash/caissier/paiement_cheque.php",      "paiements.enregistrer",    "Paiement chèque"],
    "paiements/mobile"          => ["./dash/caissier/paiement_mobile.php",      "paiements.enregistrer",    "Paiement mobile money"],

    // --- MODULE 7 : BI & Rapports ---
    "rapports"                  => ["./dash/admin/rapports.php",                "rapports.view",            "Rapports"],
    "rapports/financiers"       => ["./dash/admin/rapports_financiers.php",     "rapports.financier",       "KPIs financiers"],
    "rapports/antenne"          => ["./dash/admin/rapports_antenne.php",        "rapports.antenne",         "KPIs antenne"],
    "rapports/caisse"           => ["./dash/admin/rapports_caisse.php",         "rapports.caisse",          "Rapports caisse"],
    "rapports/commercial"       => ["./dash/admin/rapports_commercial.php",     "rapports.commercial",      "Rapports commerciaux"],
    "rapports/export"           => ["./dash/admin/rapports_export.php",         "rapports.view",            "Export rapports"],

    // --- Communication ---
    "messagerie" => ["./dash/admin/messagerie.php",  "messagerie.view", "Messagerie interne"],
    "rappels"    => ["./dash/admin/rappels.php",     "rappels.view",    "Rappels & alertes"],

    // --- Administration ---
    "utilisateurs"          => ["./dash/admin/utilisateurs_liste.php", "utilisateurs.view",   "Utilisateurs"],
    "utilisateurs/liste"    => ["./dash/admin/utilisateurs_liste.php", "utilisateurs.view",   "Liste utilisateurs"],
    "utilisateurs/ajouter"  => ["./dash/admin/utilisateur_form.php",   "utilisateurs.create", "Ajouter un utilisateur"],
    "roles"                 => ["./dash/admin/roles.php",              "utilisateurs.roles",  "Rôles & Permissions"],
    "journal"               => ["./dash/admin/journal.php",            "utilisateurs.view",   "Journal d'activités"],
    "parametres"            => ["./dash/admin/parametres.php",         "utilisateurs.roles",  "Paramètres"],
    "profile"               => ["./dash/admin/profile.php",            "profile.view",        "Mon profil"],
];

// ============================================================
// CONSTRUCTION DES ROUTES PAR RÔLE
// ============================================================
$ROUTES_BY_ROLE  = [];
$ALL_ROLE_ROUTES = [];

foreach ($ROLES as $roleName => $roleDefinition) {
    $roleUser = ["role_system" => $roleName];
    $ROUTES_BY_ROLE[$roleName] = [];

    foreach ($ROUTE_DEFINITIONS as $routeName => $routeDefinition) {
        $roleRoute = $roleName . '/' . $routeName;
        $ALL_ROLE_ROUTES[$roleRoute] = true;

        if (userCan($roleUser, $routeDefinition[1])) {
            // Dashboard spécifique par rôle
            if ($routeName === "accueil" || $routeName === "dashboard") {
                if (file_exists(__DIR__ . "/dash/{$roleName}/index.php")) {
                    $routeDefinition[0] = "./dash/{$roleName}/index.php";
                }
            }
            $ROUTES_BY_ROLE[$roleName][$roleRoute] = $routeDefinition;
        }
    }
}

// ============================================================
// ALIAS DE RÔLES (compatibilité BDD)
// ============================================================
$ROLE_ALIASES = [
    "ADMIN"      => "admin",
    "Admin"      => "admin",
    "CAISSIER"   => "caissier",
    "Caissier"   => "caissier",
    "COMMERCIAL" => "commercial",
    "Commercial" => "commercial",
    "PROGRAMMES" => "programmes",
    "Programmes" => "programmes",
    "ANIMATEUR"  => "animateur",
    "Animateur"  => "animateur",
];

// ============================================================
// AUTHENTIFICATION
// ============================================================
if (empty($_SESSION["logged_in"]) || empty($_SESSION["user_id"])) {
    header("Location: " . app_url("index.php"));
    exit;
}

$db = Database::getInstance();
$userRecord = $db->fetch(
    "SELECT u.id_utilisateur, u.login, u.nom_complet, u.email, u.telephone,
            u.langue_preferee, u.actif, u.derniere_connexion,
            r.code AS role_code, r.libelle_fr, r.libelle_en, r.libelle_rn,
            r.niveau_acces
     FROM utilisateurs u
     JOIN roles r ON u.id_role = r.id_role
     WHERE u.id_utilisateur = ?
     LIMIT 1",
    [$_SESSION["user_id"]]
);

if (!$userRecord || (int)$userRecord["actif"] !== 1) {
    $_SESSION = [];
    session_destroy();
    header("Location: " . app_url("index.php"));
    exit;
}

$role = $ROLE_ALIASES[$userRecord["role_code"] ?? ""]
     ?? strtolower($userRecord["role_code"] ?? "");

if (!isset($ROLES[$role])) {
    $_SESSION = [];
    session_destroy();
    header("Location: " . app_url("index.php"));
    exit;
}

// Langue
$lang = $userRecord["langue_preferee"] ?? DEFAULT_LANG;
if (!in_array($lang, SUPPORTED_LANGS)) $lang = DEFAULT_LANG;
$_SESSION['lang'] = $lang;

$user = [
    "utilisateur_id"  => (int) $userRecord["id_utilisateur"],
    "nom_complet"     => $userRecord["nom_complet"],
    "nom_utilisateur" => $userRecord["login"],
    "email"           => $userRecord["email"],
    "role_system"     => $role,
    "role_label"      => getRoleLabel($role, $lang),
    "niveau_acces"    => (int) $userRecord["niveau_acces"],
    "statut"          => $userRecord["actif"] ? "actif" : "désactivé",
    "lang"            => $lang,
    "avatar"          => "",
];

$_SESSION["role"] = $role;
$ROUTES = $ROUTES_BY_ROLE[$role];

// ============================================================
// AJAX : recherche client
// ============================================================
if (($_GET['ajax'] ?? '') === 'client-search') {
    header('Content-Type: application/json; charset=utf-8');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée.']);
        exit;
    }
    if (!userCan($user, 'clients.view')) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé.']);
        exit;
    }
    $q = trim((string)($_GET['q'] ?? ''));
    if ($q === '') {
        echo json_encode(['clients' => []]);
        exit;
    }
    try {
        $pattern = '%' . $q . '%';
        $clients = $db->fetchAll(
            "SELECT id_client AS id, raison_sociale AS name, telephone AS phone
             FROM clients
             WHERE actif = 1
               AND (LOWER(raison_sociale) LIKE LOWER(?) OR telephone LIKE ?)
             ORDER BY raison_sociale
             LIMIT 20",
            [$pattern, $pattern]
        );
        echo json_encode(['clients' => $clients], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log('Client search failed: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Recherche indisponible.']);
    }
    exit;
}

// ============================================================
// ROUTE ACTUELLE
// ============================================================
$route = $_GET['route'] ?? ($role . '/accueil');
$route = strtolower(trim($route, '/'));

if ($route === '' || $route === 'index.php') {
    $route = $role . '/accueil';
}

// ============================================================
// RECHERCHE DE LA ROUTE
// ============================================================
if (!isset($ALL_ROLE_ROUTES[$route])) {
    http_response_code(404);
    if (file_exists(__DIR__ . "/404.php")) {
        require __DIR__ . "/404.php";
    } else {
        echo "404 — Route introuvable : " . htmlspecialchars($route);
    }
    exit;
}

if (!isset($ROUTES[$route])) {
    http_response_code(403);
    $pageTitle = "Accès refusé";
    ob_start();
    ?>
    <div class="max-w-lg mx-auto mt-16 text-center">
        <div class="text-6xl mb-4">🔒</div>
        <h1 class="text-2xl font-bold text-red-600 mb-2">Accès refusé</h1>
        <p class="text-slate-600 mb-6">
            Le rôle <strong><?= htmlspecialchars(getRoleLabel($user["role_system"], $lang)) ?></strong>
            ne possède pas la permission nécessaire pour cette route.
        </p>
        <a href="<?= htmlspecialchars(app_route_url('accueil'), ENT_QUOTES, 'UTF-8') ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-radio-primary text-white hover:bg-radio-dark transition">
            <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>
    <?php
    $content = ob_get_clean();
    require_once __DIR__ . "/dash/template/layout.php";
    exit;
}

// ============================================================
// EXTRACTION DE LA ROUTE
// ============================================================
list($file, $requiredPermission, $pageTitle) = $ROUTES[$route];

if (!userCan($user, $requiredPermission)) {
    http_response_code(403);
    $pageTitle = "Accès refusé";
    ob_start();
    ?>
    <div class="max-w-lg mx-auto mt-16 text-center">
        <div class="text-6xl mb-4">🔒</div>
        <h1 class="text-2xl font-bold text-red-600 mb-2">Accès refusé</h1>
        <p class="text-slate-600 mb-6">
            Votre rôle <strong><?= htmlspecialchars(getRoleLabel($user["role_system"], $lang)) ?></strong>
            ne vous autorise pas à accéder à cette section.
        </p>
        <a href="<?= htmlspecialchars(app_route_url('accueil'), ENT_QUOTES, 'UTF-8') ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-radio-primary text-white hover:bg-radio-dark transition">
            <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>
    <?php
    $content = ob_get_clean();
    require_once __DIR__ . "/dash/template/layout.php";
    exit;
}

// ============================================================
// CHARGEMENT DE LA PAGE
// ============================================================
$pagePath = __DIR__ . "/" . $file;

if (!file_exists($pagePath)) {
    http_response_code(404);
    $pageTitle = "Fichier manquant";
    ob_start();
    ?>
    <div class="mx-auto max-w-2xl mt-16 rounded-md border border-amber-200 bg-amber-50 p-6 text-amber-900">
        <h1 class="text-xl font-bold mb-2">Fichier introuvable</h1>
        <p class="text-sm">La route existe mais le fichier correspondant est introuvable :</p>
        <pre class="mt-3 overflow-x-auto rounded bg-white p-3 text-xs"><?= htmlspecialchars($file) ?></pre>
        <p class="mt-3 text-xs text-amber-800">Chemin : <code><?= htmlspecialchars($pagePath) ?></code></p>
        <a href="<?= htmlspecialchars(app_route_url('accueil'), ENT_QUOTES, 'UTF-8') ?>"
           class="mt-4 inline-flex items-center gap-2 rounded-md bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">
            <i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>
    <?php
    $content = ob_get_clean();
    require_once __DIR__ . "/dash/template/layout.php";
    exit;
}

ob_start();
require $pagePath;
$content = ob_get_clean();

// ============================================================
// AFFICHAGE
// ============================================================
require_once __DIR__ . "/dash/template/layout.php";