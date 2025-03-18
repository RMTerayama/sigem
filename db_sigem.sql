
CREATE TABLE `item` (
  `id_item` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `modelo` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `descricao` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `quantidade` int NOT NULL,
  `identificacao` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_item`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `itens_saida` (
  `id` int NOT NULL AUTO_INCREMENT,
  `termo_id` int NOT NULL,
  `item_id` int NOT NULL,
  `quantidade` int NOT NULL,
  `identificacao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `termo_id` (`termo_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `itens_saida_ibfk_1` FOREIGN KEY (`termo_id`) REFERENCES `termos_saida` (`id`) ON DELETE CASCADE,
  CONSTRAINT `itens_saida_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `item` (`id_item`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `termos_saida` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_termo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `data_saida` datetime DEFAULT CURRENT_TIMESTAMP,
  `secretaria` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `departamento` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `responsavel` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `assinatura` longblob,
  `assinado` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
