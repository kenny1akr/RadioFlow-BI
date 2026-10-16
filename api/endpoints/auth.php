<?php
/**
 * api/endpoints/auth.php
 * Endpoint d'authentification - RadioFlow BI
 *
 * Routes :
 *   POST /api/auth.php?action=login    → Connexion
 *   POST /api/auth.php?action=logout   → Déconnexion
 *   GET  /api/auth.php?action=me       → Infos utilisateur connecté
 *   POST /api/auth.php?action=forgot   → Mot de passe oublié
 */

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Jwt.php';

// ============================================================
// CORS
// ============================================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept-Language');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============================================================
// ROUTING
// ============================================================
$action = $_GET['action'] ?? 'login';

try {
    switch ($action) {
        case 'login':  login();  break;
        case 'logout': logout(); break;
        case 'me':     me();     break;
        case 'forgot': forgot(); break;
        default:
            Response::error('Action inconnue', 404);
    }
} catch (Exception $e) {
    Response::error(
        APP_DEBUG ? $e->getMessage() : 'Erreur serveur',
        500
    );
}

// ============================================================
// LOGIN
// ============================================================
function login()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Response::error('Méthode non autorisée', 405);
    }

    $input = getInput();
    $identifier = trim($input['identifier'] ?? '');
    $password   = $input['password'] ?? '';

    // Validation
    $errors = [];
    if (empty($identifier)) {
        $errors['identifier'] = 'L\'identifiant est requis';
    }
    if (empty($password)) {
        $errors['password'] = 'Le mot de passe est requis';
    }
    if (!empty($errors)) {
        Response::validation($errors);
    }

    $db = Database::getInstance();

    // ============================================================
    // RECHERCHE DE L'UTILISATEUR (RadioFlow BI)
    // Table : utilisateurs
    // Colonnes : id_utilisateur, login, mot_de_passe_hash, nom_complet,
    //            email, telephone, id_role, langue_preferee, actif
    // ============================================================
    $user = $db->fetch(
        "SELECT u.id_utilisateur, u.login, u.mot_de_passe_hash, u.nom_complet,
                u.email, u.telephone, u.langue_preferee, u.actif,
                u.derniere_connexion,
                r.id_role, r.code AS role_code, r.libelle_fr, r.libelle_en, r.libelle_rn,
                r.niveau_acces
         FROM utilisateurs u
         JOIN roles r ON u.id_role = r.id_role
         WHERE (u.login = ? OR u.email = ?)
         LIMIT 1",
        [$identifier, $identifier]
    );

    if (!$user) {
        logActivity($db, null, 'LOGIN_ECHEC', 'Utilisateur inconnu: ' . $identifier);
        Response::unauthorized('Identifiant ou mot de passe incorrect');
    }

    // ============================================================
    // COMPTE ACTIF ?
    // ============================================================
    if ((int)$user['actif'] !== 1) {
        logActivity($db, $user['id_utilisateur'], 'LOGIN_ECHEC', 'Compte désactivé');
        Response::forbidden('Votre compte est désactivé. Contactez l\'administrateur.');
    }

    // ============================================================
    // VÉRIFICATION DU MOT DE PASSE
    // ============================================================
    if (!password_verify($password, $user['mot_de_passe_hash'])) {
        logActivity($db, $user['id_utilisateur'], 'LOGIN_ECHEC', 'Mot de passe incorrect');
        Response::unauthorized('Identifiant ou mot de passe incorrect.');
    }

    // ============================================================
    // CONNEXION RÉUSSIE
    // ============================================================
    $db->execute(
        "UPDATE utilisateurs 
         SET derniere_connexion = NOW()
         WHERE id_utilisateur = ?",
        [$user['id_utilisateur']]
    );

    $roleCode = strtolower($user['role_code']);

    // Générer le token JWT
    $tokenPayload = [
        'user_id'  => (int) $user['id_utilisateur'],
        'username' => $user['login'],
        'role'     => $roleCode,
        'email'    => $user['email'],
        'lang'     => $user['langue_preferee'] ?? DEFAULT_LANG,
    ];
    $token = Jwt::encode($tokenPayload);

    logActivity($db, $user['id_utilisateur'], 'LOGIN_SUCCES', 'Connexion au système RadioFlow');

    // Session PHP
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id']  = $user['id_utilisateur'];
    $_SESSION['username'] = $user['login'];
    $_SESSION['role']     = $roleCode;
    $_SESSION['lang']     = $user['langue_preferee'] ?? DEFAULT_LANG;
    $_SESSION['logged_in'] = true;

    // ============================================================
    // RÉPONSE
    // ============================================================
    Response::success([
        'token' => $token,
        'user'  => [
            'id'         => (int) $user['id_utilisateur'],
            'username'   => $user['login'],
            'full_name'  => $user['nom_complet'],
            'email'      => $user['email'],
            'phone'      => $user['telephone'],
            'role'       => $roleCode,
            'role_label' => getRoleLabel($roleCode, $user['langue_preferee'] ?? DEFAULT_LANG),
            'lang'       => $user['langue_preferee'] ?? DEFAULT_LANG,
            'niveau_acces' => (int) $user['niveau_acces'],
        ],
        'expires_in' => JWT_EXPIRATION,
    ], 'Connexion réussie');
}

// ============================================================
// LOGOUT
// ============================================================
function logout()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userId = $_SESSION['user_id'] ?? null;

    if ($userId) {
        $db = Database::getInstance();
        logActivity($db, $userId, 'LOGOUT', 'Déconnexion');
    }

    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    Response::success(null, 'Déconnexion réussie');
}

// ============================================================
// ME
// ============================================================
function me()
{
    // Via JWT
    $token = Jwt::getBearerToken();
    if ($token) {
        $payload = Jwt::decode($token);
        if ($payload) {
            $db = Database::getInstance();
            $user = $db->fetch(
                "SELECT u.id_utilisateur, u.login, u.nom_complet, u.email,
                        u.telephone, u.langue_preferee, u.actif,
                        r.code AS role_code, r.libelle_fr, r.libelle_en, r.libelle_rn,
                        r.niveau_acces
                 FROM utilisateurs u
                 JOIN roles r ON u.id_role = r.id_role
                 WHERE u.id_utilisateur = ? AND u.actif = 1",
                [$payload['user_id']]
            );
            if ($user) {
                $roleCode = strtolower($user['role_code']);
                Response::success([
                    'id'         => (int) $user['id_utilisateur'],
                    'username'   => $user['login'],
                    'full_name'  => $user['nom_complet'],
                    'email'      => $user['email'],
                    'phone'      => $user['telephone'],
                    'role'       => $roleCode,
                    'role_label' => getRoleLabel($roleCode, $user['langue_preferee'] ?? DEFAULT_LANG),
                    'lang'       => $user['langue_preferee'] ?? DEFAULT_LANG,
                    'niveau_acces' => (int) $user['niveau_acces'],
                ]);
            }
        }
    }

    // Fallback session PHP
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!empty($_SESSION['user_id'])) {
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT u.id_utilisateur, u.login, u.nom_complet, u.email,
                    u.telephone, u.langue_preferee,
                    r.code AS role_code
             FROM utilisateurs u
             JOIN roles r ON u.id_role = r.id_role
             WHERE u.id_utilisateur = ? AND u.actif = 1",
            [$_SESSION['user_id']]
        );
        if ($user) {
            Response::success($user);
        }
    }

    Response::unauthorized('Non authentifié');
}

// ============================================================
// FORGOT PASSWORD
// ============================================================
function forgot()
{
    $input = getInput();
    $email = trim($input['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Response::validation(['email' => 'Email invalide']);
    }

    $db = Database::getInstance();
    $user = $db->fetch(
        "SELECT id_utilisateur, login, nom_complet FROM utilisateurs WHERE email = ?",
        [$email]
    );

    // Toujours succès (sécurité)
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $db->execute(
            "INSERT INTO password_resets (id_utilisateur, token, expires_at)
             VALUES (?, ?, ?)",
            [$user['id_utilisateur'], $token, $expiresAt]
        );

        logActivity(
            $db,
            $user['id_utilisateur'],
            'PASSWORD_RESET_DEMANDE',
            'Demande de réinitialisation'
        );

        // TODO : Envoyer email
        // $resetLink = APP_URL . "/reset-password.php?token=$token";
    }

    Response::success(null, 'Si cet email existe, un lien de réinitialisation a été envoyé.');
}

// ============================================================
// HELPERS
// ============================================================

function getInput()
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?: [];
    }
    return $_POST;
}

/**
 * Journalise une action dans journal_activites (RadioFlow BI)
 */
function logActivity($db, $userId, $action, $description = '')
{
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '-';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '-';

        $db->execute(
            "INSERT INTO journal_activites
                (id_utilisateur, action, table_concernee, ancienne_valeur, nouvelle_valeur, adresse_ip)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $action,
                'auth',
                null,
                $description . ' | UA: ' . substr($ua, 0, 200),
                $ip,
            ]
        );
    } catch (Exception $e) {
        // Silencieux
    }
}

/**
 * Retourne le libellé du rôle selon la langue (fr, rn, en)
 */
function getRoleLabel($roleCode, $lang = 'fr')
{
    $labels = [
        'admin' => [
            'fr' => 'Administrateur / DG',
            'en' => 'Administrator / GM',
            'rn' => 'Umuyobozi',
        ],
        'caissier' => [
            'fr' => 'Caissier / Comptable',
            'en' => 'Cashier / Accountant',
            'rn' => 'Umubitsi',
        ],
        'commercial' => [
            'fr' => 'Responsable Commercial',
            'en' => 'Sales Manager',
            'rn' => 'Umucuruzi',
        ],
        'programmes' => [
            'fr' => 'Directeur des Programmes',
            'en' => 'Program Director',
            'rn' => 'Umuyobozi w\'Ibiganiro',
        ],
        'animateur' => [
            'fr' => 'Animateur / Technicien',
            'en' => 'Host / Technician',
            'rn' => 'Umutangaza',
        ],
    ];

    $code = strtolower($roleCode);
    $lang = in_array($lang, SUPPORTED_LANGS) ? $lang : 'fr';

    return $labels[$code][$lang]
        ?? ucfirst(str_replace('_', ' ', $roleCode));
}

/**
 * Normalise un rôle (compatibilité ascendante)
 */
function normalizeUserRole($role)
{
    $aliases = [
        'Super Admin'         => 'admin',
        'Administrateur / DG' => 'admin',
        'Admin'               => 'admin',
        'Caissier'            => 'caissier',
        'Comptable'           => 'caissier',
        'Commercial'          => 'commercial',
        'Programmes'          => 'programmes',
        'Animateur'           => 'animateur',
    ];
    return $aliases[$role] ?? strtolower($role);
}