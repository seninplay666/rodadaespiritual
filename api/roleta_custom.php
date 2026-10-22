<?php
/**
 * API de Roletas Personalizadas
 * Regra CORE: Criação de roletas personalizadas só é permitida em salas com mais de 4 pessoas.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../auth_check.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'criar':
            exigirLoginJson();
            $usuario = obterUsuarioLogado($pdo);
            $salaId = (int)($_POST['sala_id'] ?? 0);
            $titulo = trim($_POST['titulo'] ?? 'Roleta Oculta');
            $opcoesRaw = $_POST['opcoes'] ?? $_POST['opcoes_json'] ?? $_POST['opcoes_str'] ?? $_POST['opcoes[]'] ?? [];

            if (!$salaId) {
                echo json_encode(['sucesso' => false, 'erro' => 'Identificação do círculo espiritual é obrigatória.']);
                exit;
            }

            // Conta os jogadores ativos na sala
            $stmtCount = $pdo->prepare("
                SELECT COUNT(*) FROM `sala_jogadores`
                WHERE `sala_id` = :sala_id AND `ultimo_ping` >= (NOW() - INTERVAL 15 MINUTE)
            ");
            $stmtCount->execute([':sala_id' => $salaId]);
            $totalJogadores = (int)$stmtCount->fetchColumn();

            // Regra CORE: Criação de roletas personalizadas, serão permitidas em salas com mais de 4 pessoas
            if ($totalJogadores <= 4) {
                echo json_encode([
                    'sucesso' => false,
                    'erro' => "Poder insuficiente! A criação de roletas personalizadas exige a energia de MAIS DE 4 PESSOAS na sala (atualmente há {$totalJogadores} participantes). Convide mais almas para liberar esta magia obscura!"
                ]);
                exit;
            }

            if (is_string($opcoesRaw)) {
                $trimmed = trim($opcoesRaw);
                if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
                    $opcoesRaw = json_decode($trimmed, true) ?: [];
                } elseif (str_contains($opcoesRaw, "\n")) {
                    $opcoesRaw = explode("\n", $opcoesRaw);
                } else {
                    $opcoesRaw = explode(',', $opcoesRaw);
                }
            }

            $opcoes = array_filter(array_map('trim', (array)$opcoesRaw));
            if (count($opcoes) < 2) {
                echo json_encode(['sucesso' => false, 'erro' => 'A roleta personalizada deve conter no mínimo 2 opções do além.']);
                exit;
            }

            // Desativa roletas anteriores da sala
            $pdo->prepare("UPDATE `roletas_personalizadas` SET `ativa` = 0 WHERE `sala_id` = :sala_id")->execute([':sala_id' => $salaId]);

            // Salva a nova roleta
            $stmt = $pdo->prepare("
                INSERT INTO `roletas_personalizadas` (`sala_id`, `usuario_id`, `titulo`, `opcoes_json`, `ativa`)
                VALUES (:sala_id, :usuario_id, :titulo, :opcoes_json, 1)
            ");
            $stmt->execute([
                ':sala_id' => $salaId,
                ':usuario_id' => $usuario['id'],
                ':titulo' => htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'),
                ':opcoes_json' => json_encode(array_values($opcoes), JSON_UNESCAPED_UNICODE)
            ]);

            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Roleta personalizada invocada com sucesso no círculo!',
                'roleta' => [
                    'id' => (int)$pdo->lastInsertId(),
                    'titulo' => $titulo,
                    'opcoes' => array_values($opcoes)
                ]
            ]);
            break;

        case 'obter':
            $salaId = (int)($_GET['sala_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM `roletas_personalizadas` WHERE `sala_id` = :sala_id AND `ativa` = 1 ORDER BY `id` DESC LIMIT 1");
            $stmt->execute([':sala_id' => $salaId]);
            $roleta = $stmt->fetch();

            if ($roleta) {
                echo json_encode([
                    'sucesso' => true,
                    'roleta' => [
                        'id' => $roleta['id'],
                        'titulo' => $roleta['titulo'],
                        'opcoes' => json_decode($roleta['opcoes_json'], true)
                    ]
                ]);
            } else {
                echo json_encode(['sucesso' => true, 'roleta' => null]);
            }
            break;

        case 'restaurar_padrao':
            exigirLoginJson();
            $salaId = (int)($_POST['sala_id'] ?? 0);
            if ($salaId) {
                $pdo->prepare("UPDATE `roletas_personalizadas` SET `ativa` = 0 WHERE `sala_id` = :sala_id")->execute([':sala_id' => $salaId]);
            }
            echo json_encode(['sucesso' => true, 'mensagem' => 'Roleta padrão Charlie Charlie restaurada.']);
            break;

        default:
            echo json_encode(['sucesso' => false, 'erro' => 'Ação de roleta inválida.']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Falha arcana: ' . $e->getMessage()]);
}
