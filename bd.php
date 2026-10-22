<?php
/**
 * Conexão com o Banco de Dados MySQL via PDO
 * Projeto: Rodada Espiritual
 * Regras: Utiliza sempre PDO para prevenção contra SQL Injection.
 * Criação automática da base e tabelas para fácil portabilidade.
 */

$host = '127.0.0.1';
$dbname = 'rodada_espiritual';
$username = 'root';
$password = ''; // Senha padrão do XAMPP/local

try {
    // Conexão inicial para garantir que a base existe
    $pdoInit = new PDO("mysql:host={$host};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Cria a base de dados caso ainda não exista
    $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    // Conecta diretamente à base do projeto
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Inicialização automática das tabelas (Schema migratório seguro)
    criarTabelasSeNecessario($pdo);

} catch (PDOException $e) {
    // Em caso de erro fatal de conexão, encerra e exibe de forma segura
    header('Content-Type: application/json; charset=utf-8', true, 500);
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Falha na conexão com os espíritos do banco de dados: ' . $e->getMessage()
    ]);
    exit;
}

/**
 * Cria a estrutura de tabelas necessária caso ainda não existam.
 */
function criarTabelasSeNecessario(PDO $pdo): void {
    // 1. Tabela de Usuários (senhas tratadas com bcrypt)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `usuarios` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nome` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `senha` VARCHAR(255) NOT NULL,
            `is_premium` TINYINT(1) DEFAULT 0,
            `avatar` VARCHAR(50) DEFAULT 'oculto',
            `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Tabela de Salas de Jogo
    $pdo->exec("
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
    ");

    // 3. Tabela de Jogadores Ativos na Sala
    $pdo->exec("
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
    ");

    // 4. Tabela de Mensagens do Chat (apenas almas Premium)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `mensagens_chat` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sala_id` INT DEFAULT NULL,
            `usuario_id` INT NOT NULL,
            `mensagem` TEXT NOT NULL,
            `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 5. Tabela de Roletas Personalizadas (salas com mais de 4 pessoas)
    $pdo->exec("
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
    ");

    // 6. Tabela de Histórico de Giros Espirituais
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `historico_giros` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sala_id` INT DEFAULT NULL,
            `usuario_id` INT DEFAULT NULL,
            `pergunta` VARCHAR(255) NOT NULL,
            `resultado` VARCHAR(50) NOT NULL,
            `resposta_macabra` TEXT NOT NULL,
            `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Cria um usuário mestre/demonstrativo se a tabela de usuários estiver vazia
    $stmt = $pdo->query("SELECT COUNT(*) FROM `usuarios`");
    if ($stmt->fetchColumn() == 0) {
        $senhaDemo = password_hash('charlie123', PASSWORD_BCRYPT);
        $insert = $pdo->prepare("
            INSERT INTO `usuarios` (`nome`, `email`, `senha`, `is_premium`, `avatar`)
            VALUES (:nome, :email, :senha, :is_premium, :avatar)
        ");
        // Usuário Premium demonstrativo
        $insert->execute([
            ':nome' => 'Mestre Ocultista (Charlie)',
            ':email' => 'mestre@oculto.com',
            ':senha' => $senhaDemo,
            ':is_premium' => 1,
            ':avatar' => 'pentagrama'
        ]);
        // Usuário Normal demonstrativo
        $insert->execute([
            ':nome' => 'Alma Curiosa',
            ':email' => 'curioso@oculto.com',
            ':senha' => $senhaDemo,
            ':is_premium' => 0,
            ':avatar' => 'vela'
        ]);
    }
}
