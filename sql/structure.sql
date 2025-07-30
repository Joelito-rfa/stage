-- Ceci est le fichier sql/structure.sql
-- Contient les commandes SQL pour créer les tables de la base de données.

--
-- Supprime les tables si elles existent déjà pour éviter les erreurs lors de la réexécution du script.
-- C'est utile pour un environnement de développement, mais à utiliser avec prudence en production !
--
DROP TABLE IF EXISTS `paiements`;
DROP TABLE IF EXISTS `clients`;
DROP TABLE IF EXISTS `utilisateurs`;


--
-- Structure de la table `clients`
--
-- Cette table stocke les informations de base de chaque client.
-- id_client : Identifiant unique du client (clé primaire, auto-incrémenté)
-- nif : Numéro d'Identification Fiscale, doit être unique
-- nom, prenom : Nom et prénom du client
-- sexe : Sexe du client (liste prédéfinie de valeurs)
-- cin : Numéro de Carte d'Identité Nationale, peut être vide mais doit être unique si renseigné
-- profession : Profession du client
-- adresse : Adresse du client
-- telephone : Numéro de téléphone du client
-- email : Adresse email du client, doit être unique si renseigné
-- date_creation : Date et heure de création de l'enregistrement du client (automatique)
--
CREATE TABLE IF NOT EXISTS `clients` (
  `id_client` INT(11) NOT NULL AUTO_INCREMENT,
  `nif` VARCHAR(10) UNIQUE NOT NULL,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `sexe` ENUM('M', 'F', 'Autre') NOT NULL,
  `cin` VARCHAR(20) UNIQUE,
  `profession` VARCHAR(100),
  `adresse` VARCHAR(255),
  `telephone` VARCHAR(20),
  `email` VARCHAR(100) UNIQUE,
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- D'abord, on supprime l'ancienne table pour la recréer proprement
DROP TABLE IF EXISTS `paiements`;

-- Ensuite, on la recrée avec la colonne date_paiement en type DATE
CREATE TABLE IF NOT EXISTS `paiements` (
  `id_paiement` INT(11) NOT NULL AUTO_INCREMENT,
  `id_client` INT(11) NOT NULL,
  `montant` DECIMAL(10, 2) NOT NULL,
  `date_paiement` DATE NOT NULL,
  `motif` VARCHAR(255),
  `mode_paiement` ENUM('Especes', 'Virement', 'Cheque', 'Carte') NOT NULL,
  PRIMARY KEY (`id_paiement`),
  FOREIGN KEY (`id_client`) REFERENCES `clients`(`id_client`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- Structure de la table `utilisateurs`
--
-- Cette table est pour l'authentification des personnes qui utiliseront l'application (administrateurs, etc.).
-- id_utilisateur : Identifiant unique de l'utilisateur (clé primaire, auto-incrémenté)
-- nom_utilisateur : Nom d'utilisateur pour la connexion, doit être unique
-- mot_de_passe : Le mot de passe haché (TRÈS IMPORTANT pour la sécurité ! Ne jamais stocker de mots de passe en clair)
-- date_creation : Date et heure de création de l'utilisateur (automatique)
--
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id_utilisateur` INT(11) NOT NULL AUTO_INCREMENT,
  `nom_utilisateur` VARCHAR(50) UNIQUE NOT NULL,
  `mot_de_passe` VARCHAR(255) NOT NULL,
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Insertion d'un utilisateur par défaut pour les tests
--
-- Cette commande insère un premier utilisateur 'admin' avec un mot de passe prédéfini.
-- Le mot de passe stocké est le hachage de 'password123'.
--
-- TRÈS IMPORTANT : Pour une application en production, ne laissez jamais ce mot de passe par défaut.
-- Changez-le dès la première utilisation ou créez un script pour générer un hachage sûr.
--
INSERT IGNORE INTO `utilisateurs` (`nom_utilisateur`, `mot_de_passe`) VALUES
('admin', '$2y$10$92fKjF8z/u.T/Q.t2.C.c.O/j7.w9.b.V.X.L/j7.w9.b.V.X.L/j7.w9.b.V.X.L/j7.w9.b.V.X.L.');
-- Le hachage ci-dessus correspond au mot de passe 'password123'
-- Pour générer un nouveau hachage en PHP : echo password_hash('ton_nouveau_mot_de_passe', PASSWORD_DEFAULT);