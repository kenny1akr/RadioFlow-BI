<?php
/**
 * dash/template/sidebar_config.php
 * Configuration de la sidebar — RadioFlow BI
 * URLs propres compatibles avec router.php
 */

$SIDEBAR_CONFIG = [

    // ============================================================
    // TABLEAU DE BORD
    // ============================================================
    "main" => [
        "label" => null,
        "items" => [
            [
                "type"       => "link",
                "label_key"  => "dashboard",
                "label"      => "Tableau de bord",
                "icon"       => "home",
                "href"       => "/accueil",
                "permission" => "dashboard.view",
                "badge"      => ["text" => "Live", "color" => "muco"],
            ],
        ],
    ],

    // ============================================================
    // MODULE 1 : ÉMISSIONS & GRILLE ANTENNE
    // ============================================================
    "emissions" => [
        "label" => "Antenne",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Émissions & Grille",
                "icon"       => "radio",
                "permission" => "emissions.view",
                "items"      => [
                    ["label" => "Liste des émissions", "href" => "/emissions/liste",  "permission" => "emissions.view"],
                    ["label" => "Créer une émission",  "href" => "/emissions/creer",  "permission" => "emissions.create"],
                    ["label" => "Grille antenne",      "href" => "/grille",           "permission" => "grille.view"],
                    ["label" => "Invités",             "href" => "/invites",          "permission" => "invites.view"],
                    ["label" => "Animateurs",          "href" => "/animateurs",       "permission" => "animateurs.view"],
                ],
            ],
        ],
    ],

    // ============================================================
    // MODULE 2 : COMMERCIAL & PUBLICITÉ
    // ============================================================
    "commercial" => [
        "label" => "Commercial",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Clients & Agences",
                "icon"       => "users",
                "permission" => "clients.view",
                "items"      => [
                    ["label" => "Liste clients",  "href" => "/clients/liste",   "permission" => "clients.view"],
                    ["label" => "Ajouter client", "href" => "/clients/ajouter", "permission" => "clients.create"],
                ],
            ],
            [
                "type"       => "dropdown",
                "label"      => "Contrats & Campagnes",
                "icon"       => "briefcase",
                "permission" => "contrats.view",
                "items"      => [
                    ["label" => "Contrats",     "href" => "/contrats",         "permission" => "contrats.view"],
                    ["label" => "Nouveau contrat", "href" => "/contrats/creer", "permission" => "contrats.create"],
                    ["label" => "Campagnes",    "href" => "/campagnes",        "permission" => "campagnes.view"],
                    ["label" => "Spots audio",  "href" => "/spots",            "permission" => "spots.view"],
                    ["label" => "Uploader un spot", "href" => "/spots/upload", "permission" => "spots.upload"],
                ],
            ],
        ],
    ],

    // ============================================================
    // MODULE 3 : PLANIFICATION (TRAFFIC)
    // ============================================================
    "planification" => [
        "label" => "Planification",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Traffic publicitaire",
                "icon"       => "calendar",
                "permission" => "planification.view",
                "items"      => [
                    ["label" => "Planning des spots",   "href" => "/planification",         "permission" => "planification.view"],
                    ["label" => "Pauses commerciales",  "href" => "/planification/pauses",  "permission" => "planification.pauses"],
                    ["label" => "Alertes planning",     "href" => "/planification/alertes", "permission" => "planification.alertes"],
                ],
            ],
        ],
    ],

    // ============================================================
    // MODULE 4 : STUDIO & LOGS DE DIFFUSION
    // ============================================================
    "studio" => [
        "label" => "Studio",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Conducteur & Logs",
                "icon"       => "list",
                "permission" => "conducteurs.view",
                "items"      => [
                    ["label" => "Conducteurs",       "href" => "/conducteurs",        "permission" => "conducteurs.view"],
                    ["label" => "Playlist / Éléments","href" => "/elements",          "permission" => "elements.view"],
                    ["label" => "As-Played Log",     "href" => "/as-played",          "permission" => "as_played.view"],
                ],
            ],
        ],
    ],

    // ============================================================
    // MODULE 5 : PROMOTIONS & AUDITEURS
    // ============================================================
    "promotions" => [
        "label" => "Promotions & Auditeurs",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Concours & Gagnants",
                "icon"       => "gift",
                "permission" => "concours.view",
                "items"      => [
                    ["label" => "Concours",  "href" => "/concours",         "permission" => "concours.view"],
                    ["label" => "Gagnants",  "href" => "/gagnants",         "permission" => "gagnants.view"],
                    ["label" => "Lots",      "href" => "/lots",             "permission" => "concours.view"],
                ],
            ],
            [
                "type"       => "link",
                "label"      => "Messages auditeurs",
                "icon"       => "message-circle",
                "href"       => "/interactions",
                "permission" => "interactions.view",
                "badge"      => ["text" => "SMS", "color" => "red"],
            ],
        ],
    ],

    // ============================================================
    // MODULE 6 : FACTURATION & CAISSE (PRIORITAIRE)
    // ============================================================
    "caisse" => [
        "label" => "Facturation & Caisse",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Caisse",
                "icon"       => "dollar-sign",
                "permission" => "caisse.view",
                "items"      => [
                    ["label" => "Journal de caisse",     "href" => "/caisse/journal",       "permission" => "caisse.view"],
                    ["label" => "Session de caisse",     "href" => "/caisse/session",       "permission" => "caisse.view"],
                    ["label" => "Encaissement",          "href" => "/caisse/encaissement",  "permission" => "caisse.saisie"],
                    ["label" => "Rapprochement",         "href" => "/caisse/rapprochement", "permission" => "caisse.cloture"],
                    ["label" => "Alertes caisse",        "href" => "/caisse/alertes",       "permission" => "caisse.view"],
                ],
            ],
            [
                "type"       => "dropdown",
                "label"      => "Facturation",
                "icon"       => "file-text",
                "permission" => "factures.view",
                "items"      => [
                    ["label" => "Devis",         "href" => "/factures/devis",    "permission" => "documents.devis"],
                    ["label" => "Pro-forma",     "href" => "/factures/proforma", "permission" => "documents.proforma"],
                    ["label" => "Factures",      "href" => "/factures/liste",    "permission" => "factures.view"],
                    ["label" => "Reçus",         "href" => "/factures/recus",    "permission" => "documents.recu"],
                    ["label" => "Créer facture", "href" => "/factures/creer",    "permission" => "factures.creer"],
                ],
            ],
            [
                "type"       => "dropdown",
                "label"      => "Modes de paiement",
                "icon"       => "credit-card",
                "permission" => "paiements.enregistrer",
                "items"      => [
                    ["label" => "Espèces",       "href" => "/paiements/especes",   "permission" => "paiements.enregistrer"],
                    ["label" => "Virement",      "href" => "/paiements/virement",  "permission" => "paiements.enregistrer"],
                    ["label" => "Chèque",        "href" => "/paiements/cheque",    "permission" => "paiements.enregistrer"],
                    ["label" => "Mobile Money",  "href" => "/paiements/mobile",    "permission" => "paiements.enregistrer"],
                ],
            ],
        ],
    ],

    // ============================================================
    // MODULE 7 : TABLEAU DE BORD & BI
    // ============================================================
    "bi" => [
        "label" => "Business Intelligence",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Rapports & KPIs",
                "icon"       => "bar-chart-2",
                "permission" => "rapports.view",
                "items"      => [
                    ["label" => "KPIs financiers",   "href" => "/rapports/financiers", "permission" => "rapports.financier"],
                    ["label" => "KPIs antenne",      "href" => "/rapports/antenne",    "permission" => "rapports.antenne"],
                    ["label" => "Rapports caisse",   "href" => "/rapports/caisse",     "permission" => "rapports.caisse"],
                    ["label" => "Rapports commercial","href" => "/rapports/commercial","permission" => "rapports.commercial"],
                    ["label" => "Exporter (PDF/Excel)","href" => "/rapports/export",   "permission" => "rapports.view"],
                ],
            ],
        ],
    ],

    // ============================================================
    // COMMUNICATION
    // ============================================================
    "communication" => [
        "label" => "Communication",
        "items" => [
            [
                "type"       => "link",
                "label"      => "Messagerie interne",
                "icon"       => "message-circle",
                "href"       => "/messagerie",
                "permission" => "messagerie.view",
            ],
            [
                "type"       => "link",
                "label"      => "Rappels & Alertes",
                "icon"       => "bell",
                "href"       => "/rappels",
                "permission" => "rappels.view",
            ],
        ],
    ],

    // ============================================================
    // ADMINISTRATION
    // ============================================================
    "admin" => [
        "label" => "Administration",
        "items" => [
            [
                "type"       => "dropdown",
                "label"      => "Utilisateurs & Rôles",
                "icon"       => "shield",
                "permission" => "utilisateurs.view",
                "items"      => [
                    ["label" => "Liste des utilisateurs", "href" => "/utilisateurs/liste",   "permission" => "utilisateurs.view"],
                    ["label" => "Ajouter un utilisateur", "href" => "/utilisateurs/ajouter", "permission" => "utilisateurs.create"],
                    ["label" => "Rôles & Permissions",    "href" => "/roles",                "permission" => "utilisateurs.roles"],
                ],
            ],
            [
                "type"       => "link",
                "label"      => "Journal d'activités",
                "icon"       => "activity",
                "href"       => "/journal",
                "permission" => "utilisateurs.view",
            ],
            [
                "type"       => "link",
                "label"      => "Paramètres",
                "icon"       => "settings",
                "href"       => "/parametres",
                "permission" => "utilisateurs.roles",
            ],
            [
                "type"       => "link",
                "label"      => "Mon profil",
                "icon"       => "user",
                "href"       => "/profile",
                "permission" => "profile.view",
            ],
        ],
    ],
];