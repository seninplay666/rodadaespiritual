<?php
/**
 * API de Sincronização em Tempo Real para Salas Espirituais
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../auth_check.php';

$salaId = (int)($_GET['sala_id'] ?? $_POST['sala_id'] ?? 0);

if (!$salaId) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sala não informada.']);
    exit;
}

try {
    // Atualiza o ping se logado
    if (usuarioEstaLogado()) {
        $user = obterUsuarioLogado($pdo);
        $stmtPing = $pdo->prepare("UPDATE `sala_jogadores` SET `ultimo_ping` = NOW() WHERE `sala_id` = :sala_id AND `usuario_id` = :uid");
        $stmtPing->execute([':sala_id' => $salaId, ':uid' => $user['id']]);
    }

    // Busca jogadores ativos (últimos 10 minutos)
    $stmtJog = $pdo->prepare("
        SELECT j.`usuario_id`, j.`nome_jogador`, j.`entrou_em`, u.`is_premium`, u.`avatar`
        FROM `sala_jogadores` j
        JOIN `usuarios` u ON u.`id` = j.`usuario_id`
        WHERE j.`sala_id` = :sala_id AND j.`ultimo_ping` >= (NOW() - INTERVAL 10 MINUTE)
        ORDER BY j.`entrou_em` ASC
    ");
    $stmtJog->execute([':sala_id' => $salaId]);
    $jogadores = $stmtJog->fetchAll();

    // Último giro da sala
    $stmtGiro = $pdo->prepare("
        SELECT h.*, u.`nome` AS `autor_nome`
        FROM `historico_giros` h
        LEFT JOIN `usuarios` u ON u.`id` = h.`usuario_id`
        WHERE h.`sala_id` = :sala_id
        ORDER BY h.`id` DESC LIMIT 1
    ");
    $stmtGiro->execute([':sala_id' => $salaId]);
    $ultimoGiro = $stmtGiro->fetch();

    // Roleta personalizada ativa
    $stmtRoleta = $pdo->prepare("
        SELECT * FROM `roletas_personalizadas` 
        WHERE `sala_id` = :sala_id AND `ativa` = 1 
        ORDER BY `id` DESC LIMIT 1
    ");
    $stmtRoleta->execute([':sala_id' => $salaId]);
    $roleta = $stmtRoleta->fetch();

    // Últimas mensagens de chat da sala
    $stmtChat = $pdo->prepare("
        SELECT m.`id`, m.`mensagem`, m.`criado_em`, m.`usuario_id`,
               u.`nome` AS `usuario_nome`, u.`is_premium`
        FROM `mensagens_chat` m
        JOIN `usuarios` u ON u.`id` = m.`usuario_id`
        WHERE m.`sala_id` = :sala_id
        ORDER BY m.`id` DESC LIMIT 20
    ");
    $stmtChat->execute([':sala_id' => $salaId]);
    $mensagens = array_reverse($stmtChat->fetchAll());

    echo json_encode([
        'sucesso' => true,
        'jogadores' => $jogadores,
        'total_jogadores' => count($jogadores),
        'pode_personalizar' => count($jogadores) > 4, // Regra CORE: mais de 4 pessoas
        'ultimo_giro' => $ultimoGiro,
        'roleta' => $roleta ? [
            'id' => $roleta['id'],
            'titulo' => $roleta['titulo'],
            'opcoes' => json_decode($roleta['opcoes_json'], true)
        ] : null,
        'mensagens' => $mensagens
    ]);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro de sintonia: ' . $e->getMessage()]);
}
