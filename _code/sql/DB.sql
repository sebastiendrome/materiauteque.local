
SET NAMES utf8mb4;

# Dump of table _adhesions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `_adhesions`;

CREATE TABLE `_adhesions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `date` int NOT NULL,
  `membre_type` varchar(20) NOT NULL DEFAULT 'normal' COMMENT 'normal, admin, manageur',
  `nom` varchar(20) NOT NULL DEFAULT '',
  `prenom` varchar(20) NOT NULL DEFAULT '',
  `commune` varchar(20) NOT NULL DEFAULT '',
  `mail` varchar(50) DEFAULT NULL,
  `tel` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table _passages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `_passages`;

CREATE TABLE `_passages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `date` int NOT NULL,
  `nombre` decimal(10,0) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table articles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `articles`;

CREATE TABLE `articles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `titre` text NOT NULL,
  `descriptif` text,
  `vrac` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=non, 1=oui',
  `categories_id` int NOT NULL,
  `sous_categories_id` int DEFAULT NULL,
  `matieres_id` int NOT NULL,
  `sous_matieres_id` int DEFAULT NULL,
  `statut_id` int NOT NULL DEFAULT '1',
  `poids` decimal(19,3) NOT NULL,
  `prix` decimal(19,2) DEFAULT NULL COMMENT 'prix conséillé, ou vide=prix libre',
  `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=invisible, 1=visible',
  `observations` text,
  `paniers_id` int DEFAULT NULL,
  `date` int NOT NULL,
  `date_vente` int DEFAULT NULL,
  `etiquette` varchar(20) DEFAULT NULL COMMENT 'pas utilisé, pour plus tard',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `titre` (`titre`,`descriptif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table caisse
# ------------------------------------------------------------

DROP TABLE IF EXISTS `caisse`;

CREATE TABLE `caisse` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `statut_id` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=caisse ouverte, 2=caisse fermée',
  `especes_ouverture` float DEFAULT '0',
  `cheques_ouverture` float DEFAULT '0',
  `especes_fermeture` float DEFAULT '0',
  `cheques_fermeture` float DEFAULT '0',
  `depot_especes` float DEFAULT '0',
  `depot_cheques` float DEFAULT '0',
  `porteur_depot_banque` tinytext CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'MAX LENGTH: 250',
  `remarques` tinytext CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'MAX LENGTH: 250',
  `passages` smallint DEFAULT NULL,
  `referents` tinytext CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT 'MAX LENGTH: 250',
  `horaire_am_start` time DEFAULT NULL,
  `horaire_am_end` time DEFAULT NULL,
  `horaire_pm_start` time DEFAULT NULL,
  `horaire_pm_end` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;



# Dump of table categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(23) NOT NULL DEFAULT '',
  `id_parent` int NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=non, 1=oui',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;

INSERT INTO `categories` (`id`, `nom`, `id_parent`, `visible`)
VALUES
	(1,'Quincaillerie',0,1),
	(2,'Bois',0,1),
	(3,'Construction',0,1),
	(4,'Plomberie',0,1),
	(5,'Eléctricité',0,1),
	(6,'Outillage',0,1),
	(7,'Equipement',0,1),
	(8,'Vélo',0,1),
	(9,'Autres',0,1),
	(11,'Visserie',1,1),
	(13,'Profilés',1,1),
	(14,'Accessoires',1,1),
	(15,'Droguerie',1,1),
	(16,'Bâches',1,1),
	(17,'Charpente',2,1),
	(18,'Bricolage',2,1),
	(19,'Palette',2,1),
	(20,'Maçonnerie',3,1),
	(21,'Menuiserie',3,1),
	(22,'Couverture',3,1),
	(23,'Cloisons',3,1),
	(24,'Carrelage',3,1),
	(25,'Revêtement',3,1),
	(26,'Isolation',3,1),
	(27,'Fumisterie/Chauffage',3,1),
	(28,'Tuyauterie',4,1),
	(29,'Accessoires',4,1),
	(30,'Arrosage',4,1),
	(31,'Câblage/gaines',5,1),
	(32,'Raccordement',5,1),
	(33,'Appareillage',5,1),
	(34,'Machine',6,1),
	(35,'Electro-portatif',6,1),
	(36,'Manuel',6,1),
	(37,'Accessoires',6,1),
	(38,'Bidons',6,1),
	(39,'Ameublement',7,1),
	(40,'Sanitaires',7,1),
	(41,'Electrique',7,1),
	(42,'Luminaires',5,1),
	(43,'Vélo/Attelage',8,1),
	(44,'Pneu/Chambre à air',8,1),
	(45,'Pièces détachées',8,1),
	(46,'Accessoires',8,1),
	(47,'Textile',9,1),
	(48,'Déco',9,1),
	(49,'Loisir',9,1),
	(50,'Participations',0,1),
	(51,'Adhésion',50,1),
	(52,'Atelier',50,1),
	(53,'Don',50,1);

/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table matieres
# ------------------------------------------------------------

DROP TABLE IF EXISTS `matieres`;

CREATE TABLE `matieres` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(23) NOT NULL DEFAULT '',
  `id_parent` int NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=oui, 2=non',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `matieres` WRITE;
/*!40000 ALTER TABLE `matieres` DISABLE KEYS */;

INSERT INTO `matieres` (`id`, `nom`, `id_parent`, `visible`)
VALUES
	(1,'Bois',0,1),
	(2,'DEEE',0,1),
	(3,'Ferraille/Métal',0,1),
	(4,'Huile/Peinture',0,1),
	(5,'Inerte',0,1),
	(6,'Plastique & dérivés',0,1),
	(7,'Textile',0,1),
	(8,'Verre',0,1),
	(9,'Brut',1,1),
	(10,'Traité',1,1),
	(11,'Contreplaqué',1,1),
	(12,'Aggloméré',1,1),
	(13,'OSB',1,1),
	(17,'Appareil',2,1),
	(18,'Composant',2,1),
	(20,'Cuivre',3,1),
	(21,'Alu',3,1),
	(22,'Zinc',3,1),
	(28,'Minéral',4,1),
	(29,'Végétal',4,1),
	(31,'Béton, Ciment, etc.',5,1),
	(32,'Plâtre',5,1),
	(33,'Terre cuite',5,1),
	(34,'Plastique',6,1),
	(35,'Résine / Composite',6,1),
	(36,'Faïence',5,1),
	(37,'Gypse',5,1),
	(38,'Verre cellulaire',5,1),
	(39,'Caoutchouc',6,1),
	(40,'Goudron',6,1),
	(41,'Plexiglas',6,1),
	(42,'Tissu',7,1),
	(43,'Cuir',7,1),
	(44,'Brut',8,1),
	(45,'Renforcé',8,1),
	(46,'Autre',0,1),
	(47,'Fonte',3,1),
	(48,'Carton',46,1),
	(49,'Polystyrène',46,1),
	(50,'Miroir',8,1),
	(51,'PVC',6,1),
	(52,'Ampoule',2,1);

/*!40000 ALTER TABLE `matieres` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table paiement
# ------------------------------------------------------------

DROP TABLE IF EXISTS `paiement`;

CREATE TABLE `paiement` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `paiement` WRITE;
/*!40000 ALTER TABLE `paiement` DISABLE KEYS */;

INSERT INTO `paiement` (`id`, `nom`)
VALUES
	(1,'espèces'),
	(2,'chèque');

/*!40000 ALTER TABLE `paiement` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table paniers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `paniers`;

CREATE TABLE `paniers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `date` int DEFAULT NULL,
  `nom` text NOT NULL,
  `paiement_id` int NOT NULL DEFAULT '1',
  `total` decimal(19,2) DEFAULT NULL,
  `poids` decimal(19,3) DEFAULT NULL,
  `date_vente` int DEFAULT NULL,
  `statut_id` int DEFAULT '1',
  `notes` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table statut
# ------------------------------------------------------------

DROP TABLE IF EXISTS `statut`;

CREATE TABLE `statut` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `statut` WRITE;
/*!40000 ALTER TABLE `statut` DISABLE KEYS */;

INSERT INTO `statut` (`id`, `nom`)
VALUES
	(1,'disponible'),
	(2,'à réparer'),
	(3,'réservé'),
	(4,'vendu'),
	(5,'transféré'),
	(6,'rejeté');

/*!40000 ALTER TABLE `statut` ENABLE KEYS */;
UNLOCK TABLES;
