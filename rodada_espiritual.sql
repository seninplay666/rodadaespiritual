-- ============================================================================
-- Banco de Dados: rodada_espiritual
-- Projeto: Rodada Espiritual (O Tabuleiro de Charlie Charlie)
-- Stack: PHP 8 + MySQL/MariaDB (PDO)
-- ============================================================================

SET NAMES utf8mb4;
SET time_zone = '-03:00';
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- Criação do Banco de Dados
-- ----------------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `rodada_espiritual`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `rodada_espiritual`;

-- ----------------------------------------------------------------------------
-- 1. Tabela de Usuários (senhas criptografadas com Bcrypt)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `is_premium` TINYINT(1) DEFAULT 0,
    `avatar` VARCHAR(50) DEFAULT 'oculto',
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Tabela de Salas de Jogo
-- Regra CORE: Salas com mais de 3 pessoas são exclusivas para usuários Premium
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `salas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(12) NOT NULL UNIQUE,
    `nome` VARCHAR(100) NOT NULL,
    `criador_id` INT NOT NULL,
    `max_jogadores` INT DEFAULT 3,
    `ativa` TINYINT(1) DEFAULT 1,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`criador_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Tabela de Jogadores Ativos na Sala
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sala_jogadores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sala_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `nome_jogador` VARCHAR(100) NOT NULL,
    `ultimo_ping` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `entrou_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_sala_usuario` (`sala_id`, `usuario_id`),
    FOREIGN KEY (`sala_id`) REFERENCES `salas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Tabela de Mensagens do Chat
-- Regra CORE: Envio de mensagens exclusivo para almas do Plano Premium
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mensagens_chat` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sala_id` INT DEFAULT NULL,
    `usuario_id` INT NOT NULL,
    `mensagem` TEXT NOT NULL,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sala_id`) REFERENCES `salas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Tabela de Roletas Personalizadas
-- Regra CORE: Permitida apenas em salas com mais de 4 pessoas conectadas
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roletas_personalizadas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sala_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `titulo` VARCHAR(150) NOT NULL,
    `opcoes_json` TEXT NOT NULL,
    `ativa` TINYINT(1) DEFAULT 1,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sala_id`) REFERENCES `salas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. Tabela de Histórico de Giros Espirituais
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `historico_giros` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sala_id` INT DEFAULT NULL,
    `usuario_id` INT DEFAULT NULL,
    `pergunta` VARCHAR(255) NOT NULL,
    `resultado` VARCHAR(50) NOT NULL,
    `resposta_macabra` TEXT NOT NULL,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sala_id`) REFERENCES `salas`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Dados Iniciais / Contas de Demonstração
-- Senha padrão para ambas as contas: charlie123 (Hash Bcrypt cost 10)
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO `usuarios` (`id`, `nome`, `email`, `senha`, `is_premium`, `avatar`) VALUES
(1, 'Mestre Ocultista (Charlie)', 'mestre@oculto.com', '$2y$10$mxYmPYoU/BWLE8SjiTTEYOf41uspE.KT0WASwq4cmHQZ1hto3Gkd6', 1, 'pentagrama'),
(2, 'Alma Curiosa', 'curioso@oculto.com', '$2y$10$mxYmPYoU/BWLE8SjiTTEYOf41uspE.KT0WASwq4cmHQZ1hto3Gkd6', 0, 'vela');

SET FOREIGN_KEY_CHECKS = 1;
