<?php
/**
 * API de Chat Espiritual
 * Regra CORE: Chats de mensagens só serão permitidos no premium.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../auth_check.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'enviar':
            // Regra CORE: Usuário deve ser Premium para usar o chat
            exigirLoginJson();
            if (!usuarioEhPremium($pdo)) {
                echo json_encode([
                    'sucesso' => false,
                    'erro' => 'Voz silenciada pelas trevas! O envio de mensagens no chat é uma dádiva exclusiva para almas do Plano Premium.'
                ]);
                exit;
            }

            $usuario = obterUsuarioLogado($pdo);
            $mensagem = trim($_POST['mensagem'] ?? '');
            $salaId = !empty($_POST['sala_id']) ? (int)$_POST['sala_id'] : null;

            if (empty($mensagem)) {
                echo json_encode(['sucesso' => false, 'erro' => 'Não envie sussurros vazios ao além.']);
                exit;
            }

            if (mb_strlen($mensagem) > 500) {
                echo json_encode(['sucesso' => false, 'erro' => 'Sua mensagem é muito longa para os espíritos assimilarem.']);
                exit;
            }

            $stmt = $pdo->prepare("
                INSERT INTO `mensagens_chat` (`sala_id`, `usuario_id`, `mensagem`)
                VALUES (:sala_id, :usuario_id, :mensagem)
            ");
            $stmt->execute([
                ':sala_id' => $salaId,
                ':usuario_id' => $usuario['id'],
                ':mensagem' => htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8')
            ]);

            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Sussurro ecoado com sucesso no além!'
            ]);
            break;

        case 'listar':
            $salaId = !empty($_GET['sala_id']) ? (int)$_GET['sala_id'] : null;

            if ($salaId) {
                $stmt = $pdo->prepare("
                    SELECT m.`id`, m.`mensagem`, m.`criado_em`, m.`usuario_id`,
                           u.`nome` AS `usuario_nome`, u.`is_premium`, u.`avatar`
                    FROM `mensagens_chat` m
                    JOIN `usuarios` u ON u.`id` = m.`usuario_id`
                    WHERE m.`sala_id` = :sala_id
                    ORDER BY m.`id` ASC
                    LIMIT 50
                ");
                $stmt->execute([':sala_id' => $salaId]);
            } else {
                $stmt = $pdo->query("
                    SELECT m.`id`, m.`mensagem`, m.`criado_em`, m.`usuario_id`,
                           u.`nome` AS `usuario_nome`, u.`is_premium`, u.`avatar`
                    FROM `mensagens_chat` m
                    JOIN `usuarios` u ON u.`id` = m.`usuario_id`
                    WHERE m.`sala_id` IS NULL
                    ORDER BY m.`id` DESC
                    LIMIT 30
                ");
            }

            $mensagens = $stmt->fetchAll();
            if (!$salaId) {
                $mensagens = array_reverse($mensagens);
            }

            echo json_encode([
                'sucesso' => true,
                'mensagens' => $mensagens,
                'eh_premium' => usuarioEhPremium($pdo)
            ]);
            break;

        default:
            echo json_encode(['sucesso' => false, 'erro' => 'Ação de chat inválida.']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Eco espectral falhou: ' . $e->getMessage()]);
}
