-- =====================================================
-- RÔLES ET UTILISATEURS
-- =====================================================

CREATE TABLE roles (
    id_role SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,        -- 'ADMIN', 'CAISSIER', 'COMMERCIAL', 'PROGRAMMES', 'ANIMATEUR'
    libelle_fr VARCHAR(100) NOT NULL,
    libelle_en VARCHAR(100),
    libelle_rn VARCHAR(100),                 -- Kirundi
    niveau_acces INT NOT NULL DEFAULT 1
);

CREATE TABLE utilisateurs (
    id_utilisateur SERIAL PRIMARY KEY,
    nom_complet VARCHAR(150) NOT NULL,
    login VARCHAR(80) UNIQUE NOT NULL,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150),
    telephone VARCHAR(30),
    id_role INT NOT NULL REFERENCES roles(id_role),
    langue_preferee VARCHAR(5) DEFAULT 'fr',  -- fr, en, rn
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP
);

CREATE TABLE permissions (
    id_permission SERIAL PRIMARY KEY,
    code VARCHAR(80) UNIQUE NOT NULL,        -- ex: 'CAISSE_VALIDER_ANNULATION'
    description TEXT
);

CREATE TABLE role_permissions (
    id_role INT REFERENCES roles(id_role),
    id_permission INT REFERENCES permissions(id_permission),
    PRIMARY KEY (id_role, id_permission)
);

CREATE TABLE journal_activites (
    id_log SERIAL PRIMARY KEY,
    id_utilisateur INT REFERENCES utilisateurs(id_utilisateur),
    action VARCHAR(150) NOT NULL,
    table_concernee VARCHAR(80),
    id_enregistrement INT,
    ancienne_valeur TEXT,
    nouvelle_valeur TEXT,
    adresse_ip VARCHAR(45),
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- ÉMISSIONS & GRILLE ANTENNE
-- =====================================================

CREATE TABLE types_emission (
    id_type SERIAL PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL              -- Direct, Rediffusion, Enregistré
);

CREATE TABLE emissions (
    id_emission SERIAL PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    id_type INT REFERENCES types_emission(id_type),
    duree_minutes INT,
    conducteur TEXT,                          -- conducteur de l'émission
    sujets TEXT,
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE emission_animateurs (
    id_emission INT REFERENCES emissions(id_emission),
    id_utilisateur INT REFERENCES utilisateurs(id_utilisateur),
    role_participation VARCHAR(50),           -- 'Animateur', 'Technicien', 'Invité'
    PRIMARY KEY (id_emission, id_utilisateur, role_participation)
);

CREATE TABLE invites (
    id_invite SERIAL PRIMARY KEY,
    nom_complet VARCHAR(150) NOT NULL,
    organisation VARCHAR(150),
    telephone VARCHAR(30),
    email VARCHAR(150)
);

CREATE TABLE emission_invites (
    id_emission INT REFERENCES emissions(id_emission),
    id_invite INT REFERENCES invites(id_invite),
    PRIMARY KEY (id_emission, id_invite)
);

CREATE TABLE grille_antenne (
    id_creneau SERIAL PRIMARY KEY,
    id_emission INT REFERENCES emissions(id_emission),
    date_diffusion DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    statut VARCHAR(30) DEFAULT 'En attente',  -- En attente, Enregistrée, En cours, Diffusée, Annulée
    id_utilisateur_creation INT REFERENCES utilisateurs(id_utilisateur),
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_heures CHECK (heure_fin > heure_debut)
);

CREATE INDEX idx_grille_date ON grille_antenne(date_diffusion);

-- =====================================================
-- CLIENTS, CONTRATS & CAMPAGNES
-- =====================================================

CREATE TABLE clients (
    id_client SERIAL PRIMARY KEY,
    raison_sociale VARCHAR(200) NOT NULL,
    type_client VARCHAR(20) DEFAULT 'Direct',  -- Direct, Agence
    contact_nom VARCHAR(150),
    telephone VARCHAR(30),
    email VARCHAR(150),
    adresse TEXT,
    numero_contribuable VARCHAR(50),
    conditions_financieres TEXT,
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE secteurs_activite (
    id_secteur SERIAL PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL             -- ex: Télécom, Boisson, Banque
);

CREATE TABLE clients_secteurs (
    id_client INT REFERENCES clients(id_client),
    id_secteur INT REFERENCES secteurs_activite(id_secteur),
    PRIMARY KEY (id_client, id_secteur)
);

CREATE TABLE spots_audio (
    id_spot SERIAL PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    id_client INT REFERENCES clients(id_client),
    fichier_chemin VARCHAR(500) NOT NULL,     -- MP3/WAV
    duree_secondes INT NOT NULL,
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE
);

CREATE TABLE contrats (
    id_contrat SERIAL PRIMARY KEY,
    reference VARCHAR(50) UNIQUE NOT NULL,
    id_client INT NOT NULL REFERENCES clients(id_client),
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    montant_total DECIMAL(15,2) NOT NULL,
    nombre_passages_requis INT,
    type_facturation VARCHAR(20) DEFAULT 'Forfait',  -- Forfait, Au passage
    niveau_priorite INT DEFAULT 1,
    statut VARCHAR(30) DEFAULT 'Actif',       -- Actif, Terminé, Suspendu, Annulé
    id_utilisateur_creation INT REFERENCES utilisateurs(id_utilisateur),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE campagnes (
    id_campagne SERIAL PRIMARY KEY,
    id_contrat INT NOT NULL REFERENCES contrats(id_contrat),
    nom VARCHAR(200) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut VARCHAR(30) DEFAULT 'En cours'
);

CREATE TABLE campagne_spots (
    id_campagne INT REFERENCES campagnes(id_campagne),
    id_spot INT REFERENCES spots_audio(id_spot),
    nombre_passages_prevus INT DEFAULT 1,
    PRIMARY KEY (id_campagne, id_spot)
);

-- Règle de concurrence : empêcher 2 spots de marques concurrentes dans la même pause
CREATE TABLE regles_concurrence (
    id_regle SERIAL PRIMARY KEY,
    id_secteur INT REFERENCES secteurs_activite(id_secteur),
    description TEXT,
    actif BOOLEAN DEFAULT TRUE
);

-- =====================================================
-- PLANIFICATION PUBLICITAIRE
-- =====================================================

CREATE TABLE pauses_commerciales (
    id_pause SERIAL PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    duree_max_secondes INT NOT NULL,
    quota_max_spots INT,                      -- quota max de spots dans cette pause
    actif BOOLEAN DEFAULT TRUE
);

CREATE TABLE planification_spots (
    id_planification SERIAL PRIMARY KEY,
    id_pause INT NOT NULL REFERENCES pauses_commerciales(id_pause),
    id_spot INT NOT NULL REFERENCES spots_audio(id_spot),
    id_campagne INT REFERENCES campagnes(id_campagne),
    date_diffusion DATE NOT NULL,
    heure_prevue TIME NOT NULL,
    ordre_passage INT,
    statut VARCHAR(30) DEFAULT 'Planifié',    -- Planifié, Diffusé, Manqué
    id_utilisateur INT REFERENCES utilisateurs(id_utilisateur),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_planif_date ON planification_spots(date_diffusion);

CREATE TABLE alertes_planification (
    id_alerte SERIAL PRIMARY KEY,
    id_planification INT REFERENCES planification_spots(id_planification),
    type_alerte VARCHAR(50),                  -- 'Pause saturée', 'Conflit concurrence'
    message TEXT,
    resolue BOOLEAN DEFAULT FALSE,
    date_alerte TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- STUDIO, CONDUCTEUR & AS-PLAYED
-- =====================================================

CREATE TABLE elements_playlist (
    id_element SERIAL PRIMARY KEY,
    type_element VARCHAR(30) NOT NULL,        -- 'Jingle', 'Générique', 'Titre musical', 'Spot', 'Émission'
    libelle VARCHAR(200) NOT NULL,
    fichier_chemin VARCHAR(500),
    duree_secondes INT,
    id_spot INT REFERENCES spots_audio(id_spot),
    actif BOOLEAN DEFAULT TRUE
);

CREATE TABLE conducteurs_antenne (
    id_conducteur SERIAL PRIMARY KEY,
    id_emission INT REFERENCES emissions(id_emission),
    date_diffusion DATE NOT NULL,
    statut VARCHAR(30) DEFAULT 'Brouillon',   -- Brouillon, Validé, Diffusé
    id_utilisateur_validation INT REFERENCES utilisateurs(id_utilisateur),
    date_validation TIMESTAMP
);

CREATE TABLE conducteur_elements (
    id_conducteur INT REFERENCES conducteurs_antenne(id_conducteur),
    id_element INT REFERENCES elements_playlist(id_element),
    ordre INT NOT NULL,
    heure_prevue TIME,
    PRIMARY KEY (id_conducteur, ordre)
);

CREATE TABLE as_played_log (
    id_log SERIAL PRIMARY KEY,
    id_element INT REFERENCES elements_playlist(id_element),
    id_conducteur INT REFERENCES conducteurs_antenne(id_conducteur),
    id_planification INT REFERENCES planification_spots(id_planification),
    date_diffusion DATE NOT NULL,
    heure_debut_reelle TIMESTAMP NOT NULL,
    heure_fin_reelle TIMESTAMP,
    duree_reelle_secondes INT,
    statut VARCHAR(20) DEFAULT 'Diffusé',     -- Diffusé, Interrompu, Erreur
    valide_facturation BOOLEAN DEFAULT FALSE
);

CREATE INDEX idx_asplayed_date ON as_played_log(date_diffusion);

-- =====================================================
-- CONCOURS, GAGNANTS & INTERACTIONS
-- =====================================================

CREATE TABLE concours (
    id_concours SERIAL PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    id_client_sponsor INT REFERENCES clients(id_client),
    statut VARCHAR(30) DEFAULT 'Actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lots (
    id_lot SERIAL PRIMARY KEY,
    id_concours INT REFERENCES concours(id_concours),
    libelle VARCHAR(200) NOT NULL,
    valeur_estimee DECIMAL(12,2),
    quantite INT DEFAULT 1
);

CREATE TABLE gagnants (
    id_gagnant SERIAL PRIMARY KEY,
    id_concours INT REFERENCES concours(id_concours),
    id_lot INT REFERENCES lots(id_lot),
    nom_complet VARCHAR(150) NOT NULL,
    numero_piece_identite VARCHAR(50),
    telephone VARCHAR(30),
    date_tirage DATE NOT NULL,
    remise_confirmee BOOLEAN DEFAULT FALSE,
    date_remise DATE,
    id_utilisateur_remise INT REFERENCES utilisateurs(id_utilisateur)
);

CREATE TABLE interactions_auditeurs (
    id_interaction SERIAL PRIMARY KEY,
    type_canal VARCHAR(30) NOT NULL,          -- SMS, WhatsApp, Réseau social
    expediteur VARCHAR(150),
    telephone VARCHAR(30),
    message TEXT NOT NULL,
    id_emission INT REFERENCES emissions(id_emission),
    date_reception TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    traite BOOLEAN DEFAULT FALSE,
    id_utilisateur_traitement INT REFERENCES utilisateurs(id_utilisateur)
);

-- =====================================================
-- FACTURATION & CAISSE TEMPS RÉEL
-- =====================================================

CREATE TABLE modes_paiement (
    id_mode SERIAL PRIMARY KEY,
    code VARCHAR(30) UNIQUE NOT NULL,         -- 'ESPECES', 'VIREMENT', 'CHEQUE', 'LUMICASH', 'ECOCASH'
    libelle VARCHAR(100) NOT NULL,
    actif BOOLEAN DEFAULT TRUE
);

CREATE TABLE documents (
    id_document SERIAL PRIMARY KEY,
    reference VARCHAR(50) UNIQUE NOT NULL,
    type_document VARCHAR(20) NOT NULL,       -- 'Devis', 'Proforma', 'Facture', 'Reçu'
    id_client INT REFERENCES clients(id_client),
    id_contrat INT REFERENCES contrats(id_contrat),
    date_emission DATE NOT NULL,
    date_echeance DATE,
    montant_ht DECIMAL(15,2) NOT NULL DEFAULT 0,
    taux_tva DECIMAL(5,2) DEFAULT 0,
    montant_ttc DECIMAL(15,2) NOT NULL DEFAULT 0,
    statut VARCHAR(30) DEFAULT 'Brouillon',   -- Brouillon, Validé, Payé, Partiel, Annulé
    id_utilisateur_creation INT REFERENCES utilisateurs(id_utilisateur),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lignes_document (
    id_ligne SERIAL PRIMARY KEY,
    id_document INT NOT NULL REFERENCES documents(id_document) ON DELETE CASCADE,
    designation VARCHAR(255) NOT NULL,
    id_spot INT REFERENCES spots_audio(id_spot),
    quantite INT DEFAULT 1,
    prix_unitaire DECIMAL(15,2) NOT NULL,
    montant_ligne DECIMAL(15,2) NOT NULL
);

-- =====================================================
-- CAISSE
-- =====================================================

CREATE TABLE sessions_caisse (
    id_session SERIAL PRIMARY KEY,
    id_utilisateur INT NOT NULL REFERENCES utilisateurs(id_utilisateur),
    date_ouverture TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_cloture TIMESTAMP,
    solde_ouverture DECIMAL(15,2) DEFAULT 0,
    solde_theorique DECIMAL(15,2),
    solde_physique DECIMAL(15,2),
    ecart DECIMAL(15,2),
    statut VARCHAR(20) DEFAULT 'Ouverte',     -- Ouverte, Clôturée
    observations TEXT
);

CREATE TABLE transactions_caisse (
    id_transaction SERIAL PRIMARY KEY,
    id_session INT NOT NULL REFERENCES sessions_caisse(id_session),
    id_document INT REFERENCES documents(id_document),
    id_client INT REFERENCES clients(id_client),
    id_mode_paiement INT NOT NULL REFERENCES modes_paiement(id_mode),
    type_operation VARCHAR(20) NOT NULL,      -- 'Encaissement', 'Décaissement', 'Avance'
    montant DECIMAL(15,2) NOT NULL,
    reference_paiement VARCHAR(100),
    id_utilisateur INT NOT NULL REFERENCES utilisateurs(id_utilisateur),
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    annulee BOOLEAN DEFAULT FALSE,
    id_transaction_annulee INT REFERENCES transactions_caisse(id_transaction),
    motif_annulation TEXT
);

CREATE INDEX idx_trans_date ON transactions_caisse(date_transaction);

-- Paramétrage des plafonds & alertes
CREATE TABLE parametres_caisse (
    id_parametre SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,         -- 'PLAFOND_ENCAISSEMENT', 'ALERTE_OUVERTURE', etc.
    valeur VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE alertes_caisse (
    id_alerte SERIAL PRIMARY KEY,
    type_alerte VARCHAR(50) NOT NULL,         -- 'Plafond dépassé', 'Ouverture', 'Clôture', 'Annulation'
    id_transaction INT REFERENCES transactions_caisse(id_transaction),
    id_session INT REFERENCES sessions_caisse(id_session),
    message TEXT NOT NULL,
    montant_concerne DECIMAL(15,2),
    lue BOOLEAN DEFAULT FALSE,
    id_admin_destinataire INT REFERENCES utilisateurs(id_utilisateur),
    date_alerte TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Validation d'annulation par l'Admin
CREATE TABLE validations_annulation (
    id_validation SERIAL PRIMARY KEY,
    id_transaction INT NOT NULL REFERENCES transactions_caisse(id_transaction),
    id_admin INT NOT NULL REFERENCES utilisateurs(id_utilisateur),
    code_validation VARCHAR(50),
    valide BOOLEAN DEFAULT FALSE,
    motif TEXT,
    date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_validation TIMESTAMP
);

-- Rapprochement de caisse
CREATE TABLE rapprochements_caisse (
    id_rapprochement SERIAL PRIMARY KEY,
    id_session INT NOT NULL REFERENCES sessions_caisse(id_session),
    solde_theorique DECIMAL(15,2) NOT NULL,
    solde_physique DECIMAL(15,2) NOT NULL,
    ecart DECIMAL(15,2),
    details_ecart TEXT,
    id_utilisateur INT REFERENCES utilisateurs(id_utilisateur),
    date_rapprochement TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- INDICATEURS & ANALYTICS
-- =====================================================

CREATE TABLE kpi_financiers (
    id_kpi SERIAL PRIMARY KEY,
    periode DATE NOT NULL,                    -- mois ou jour
    chiffre_affaires DECIMAL(15,2) DEFAULT 0,
    recettes_jour DECIMAL(15,2) DEFAULT 0,
    creances_clients DECIMAL(15,2) DEFAULT 0,
    factures_impayees INT DEFAULT 0,
    reliquats DECIMAL(15,2) DEFAULT 0,
    date_calcul TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kpi_antenne (
    id_kpi SERIAL PRIMARY KEY,
    periode DATE NOT NULL,
    tranche_horaire VARCHAR(20),              -- '06h-09h', '12h-14h', etc.
    taux_remplissage_pub DECIMAL(5,2),        -- pourcentage
    nb_spots_diffuses INT DEFAULT 0,
    nb_spots_vendus INT DEFAULT 0,
    emission_top VARCHAR(200),
    date_calcul TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE rapports_generes (
    id_rapport SERIAL PRIMARY KEY,
    type_rapport VARCHAR(50) NOT NULL,
    periode_debut DATE,
    periode_fin DATE,
    chemin_fichier VARCHAR(500),
    format VARCHAR(10),                       -- PDF, Excel, CSV
    id_utilisateur INT REFERENCES utilisateurs(id_utilisateur),
    date_generation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vue : Journal de caisse instantané (pour l'Admin)
CREATE VIEW v_journal_caisse_temps_reel AS
SELECT 
    t.id_transaction,
    t.date_transaction,
    u.nom_complet AS operateur,
    t.type_operation,
    t.montant,
    mp.libelle AS mode_paiement,
    d.reference AS facture_associee,
    c.raison_sociale AS client,
    t.annulee
FROM transactions_caisse t
JOIN utilisateurs u ON t.id_utilisateur = u.id_utilisateur
JOIN modes_paiement mp ON t.id_mode_paiement = mp.id_mode
LEFT JOIN documents d ON t.id_document = d.id_document
LEFT JOIN clients c ON t.id_client = c.id_client
ORDER BY t.date_transaction DESC;

-- Vue : Chiffre d'affaires mensuel
CREATE VIEW v_ca_mensuel AS
SELECT 
    DATE_TRUNC('month', date_transaction) AS mois,
    SUM(CASE WHEN type_operation = 'Encaissement' THEN montant ELSE 0 END) AS total_encaisse,
    COUNT(*) AS nb_transactions
FROM transactions_caisse
WHERE annulee = FALSE
GROUP BY DATE_TRUNC('month', date_transaction);

-- Vue : Taux de remplissage publicitaire par tranche
CREATE VIEW v_remplissage_pub AS
SELECT 
    p.date_diffusion,
    pc.libelle AS pause,
    COUNT(ps.id_planification) AS spots_planifies,
    pc.quota_max_spots,
    ROUND((COUNT(ps.id_planification)::DECIMAL / NULLIF(pc.quota_max_spots,0)) * 100, 2) AS taux_remplissage
FROM planification_spots ps
JOIN pauses_commerciales pc ON ps.id_pause = pc.id_pause
JOIN planification_spots p ON p.id_planification = ps.id_planification
GROUP BY p.date_diffusion, pc.libelle, pc.quota_max_spots;

-- Rôles
INSERT INTO roles (code, libelle_fr, libelle_en, libelle_rn, niveau_acces) VALUES
('ADMIN', 'Administrateur / DG', 'Administrator / GM', 'Umuyobozi', 5),
('CAISSIER', 'Caissier / Comptable', 'Cashier / Accountant', 'Umubitsi', 3),
('COMMERCIAL', 'Responsable Commercial', 'Sales Manager', 'Umucuruzi', 3),
('PROGRAMMES', 'Directeur des Programmes', 'Program Director', 'Umuyobozi w Ibiganiro', 4),
('ANIMATEUR', 'Animateur / Technicien', 'Host / Technician', 'Umutangaza', 1);

-- Modes de paiement
INSERT INTO modes_paiement (code, libelle) VALUES
('ESPECES', 'Espèces'),
('VIREMENT', 'Virement bancaire'),
('CHEQUE', 'Chèque'),
('LUMICASH', 'Lumicash'),
('ECOCASH', 'EcoCash');

-- Types d'émission
INSERT INTO types_emission (libelle) VALUES
('Direct'), ('Rediffusion'), ('Enregistré');

-- Paramètres caisse
INSERT INTO parametres_caisse (code, valeur, description) VALUES
('PLAFOND_ENCAISSEMENT', '500000', 'Montant max avant alerte Admin'),
('ALERTE_OUVERTURE', 'true', 'Notifier Admin à l''ouverture de caisse'),
('ALERTE_CLOTURE', 'true', 'Notifier Admin à la clôture de caisse');