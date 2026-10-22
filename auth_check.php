<?php
/**
 * Gerenciador de Sessões Nativas e Verificação de Autenticação
 * Projeto: Rodada Espiritual
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Retorna os dados do usuário autenticado a partir do banco de dados ou null.
 */
function obterUsuarioLogado(?PDO $pdo = null): ?array {
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }

    if ($pdo) {
        $stmt = $pdo->prepare("SELECT `id`, `nome`, `email`, `is_premium`, `avatar`, `criado_em` FROM `usuarios` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $_SESSION['usuario_id']]);
        $user = $stmt->fetch();
        if ($user) {
            // Atualiza status na sessão
            $_SESSION['is_premium'] = (int)$user['is_premium'];
            $_SESSION['usuario_nome'] = $user['nome'];
            return $user;
        }
    }

    return [
        'id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['usuario_nome'] ?? 'Iniciado do Além',
        'is_premium' => (int)($_SESSION['is_premium'] ?? 0),
        'email' => $_SESSION['usuario_email'] ?? ''
    ];
}

/**
 * Verifica se o usuário atual está logado.
 */
function usuarioEstaLogado(): bool {
    return !empty($_SESSION['usuario_id']);
}

/**
 * Verifica se o usuário possui plano Premium ativo.
 */
function usuarioEhPremium(?PDO $pdo = null): bool {
    $user = obterUsuarioLogado($pdo);
    return !empty($user) && (int)$user['is_premium'] === 1;
}

/**
 * Interrompe a requisição e retorna JSON caso o usuário não esteja logado.
 */
function exigirLoginJson(): void {
    if (!usuarioEstaLogado()) {
        header('Content-Type: application/json; charset=utf-8', true, 401);
        echo json_encode([
            'sucesso' => false,
            'erro' => 'Esta invocação requer que você acesse sua conta no portal das trevas.'
        ]);
        exit;
    }
}

/**
 * Interrompe a requisição e retorna JSON caso o usuário não possua plano Premium.
 */
function exigirPremiumJson(?PDO $pdo = null): void {
    exigirLoginJson();
    if (!usuarioEhPremium($pdo)) {
        header('Content-Type: application/json; charset=utf-8', true, 403);
        echo json_encode([
            'sucesso' => false,
            'erro' => 'Apenas almas com o Selo Premium possuem acesso a este ritual sombrio.'
        ]);
        exit;
    }
}
