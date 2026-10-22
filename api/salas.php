<?php
/**
 * API de Gerenciamento de Salas de Jogos Sobrenaturais
 * Regras:
 * - Salas com mais de 3 pessoas só são permitidas para usuários com Plano Premium.
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
            $nomeSala = trim($_POST['nome'] ?? 'Círculo de Invocação');
            $maxJogadores = (int)($_POST['max_jogadores'] ?? 3);

            if (empty($nomeSala)) {
                $nomeSala = 'Círculo de Invocação #' . random_int(100, 999);
            }

            if ($maxJogadores < 2) {
                $maxJogadores = 2;
            }

            // Regra CORE: Salas de jogos com mais de 3 pessoas só serão permitidas no premium
            if ($maxJogadores > 3 && !usuarioEhPremium($pdo)) {
                echo json_encode([
                    'sucesso' => false,
                    'erro' => 'Salas com mais de 3 almas exigem o Plano Premium! Adquira o Selo das Trevas para reunir grandes seitas.'
                ]);
                exit;
            }

            // Limite superior razoável de segurança (até 12 pessoas)
            if ($maxJogadores > 12) {
                $maxJogadores = 12;
            }

            // Gera código único para convidar amigos
            $codigo = 'RITUAL-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));

            $stmt = $pdo->prepare("
                INSERT INTO `salas` (`codigo`, `nome`, `criador_id`, `max_jogadores`, `ativa`)
                VALUES (:codigo, :nome, :criador_id, :max_jogadores, 1)
            ");
            $stmt->execute([
                ':codigo' => $codigo,
                ':nome' => $nomeSala,
                ':criador_id' => $usuario['id'],
                ':max_jogadores' => $maxJogadores
            ]);

            $salaId = (int)$pdo->lastInsertId();

            // Adiciona o criador automaticamente à lista de jogadores da sala
            $stmtJog = $pdo->prepare("
                INSERT INTO `sala_jogadores` (`sala_id`, `usuario_id`, `nome_jogador`, `ultimo_ping`)
                VALUES (:sala_id, :usuario_id, :nome_jogador, NOW())
                ON DUPLICATE KEY UPDATE `ultimo_ping` = NOW()
            ");
            $stmtJog->execute([
                ':sala_id' => $salaId,
                ':usuario_id' => $usuario['id'],
                ':nome_jogador' => $usuario['nome']
            ]);

            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Círculo espiritual aberto com sucesso!',
                'sala' => [
                    'id' => $salaId,
                    'codigo' => $codigo,
                    'nome' => $nomeSala,
                    'max_jogadores' => $maxJogadores
                ]
            ]);
            break;

        case 'listar':
            // Lista salas ativas com contagem de jogadores
            $stmt = $pdo->query("
                SELECT s.`id`, s.`codigo`, s.`nome`, s.`max_jogadores`, s.`criador_id`, s.`criado_em`,
                       u.`nome` AS `criador_nome`, u.`is_premium` AS `criador_premium`,
                       COUNT(j.`id`) AS `total_jogadores`
                FROM `salas` s
                INNER JOIN `usuarios` u ON u.`id` = s.`criador_id`
                LEFT JOIN `sala_jogadores` j ON j.`sala_id` = s.`id` AND j.`ultimo_ping` >= (NOW() - INTERVAL 30 MINUTE)
                WHERE s.`ativa` = 1
                GROUP BY s.`id`
                ORDER BY s.`id` DESC
                LIMIT 30
            ");
            $salas = $stmt->fetchAll();

            echo json_encode([
                'sucesso' => true,
                'salas' => $salas
            ]);
            break;

        case 'entrar':
            exigirLoginJson();
            $usuario = obterUsuarioLogado($pdo);
            $codigo = trim($_POST['codigo'] ?? '');
            $salaId = !empty($_POST['sala_id']) ? (int)$_POST['sala_id'] : 0;

            if (empty($codigo) && empty($salaId)) {
                echo json_encode(['sucesso' => false, 'erro' => 'Informe o código do ritual para se juntar à seita.']);
                exit;
            }

            if (!empty($codigo)) {
                $stmt = $pdo->prepare("SELECT * FROM `salas` WHERE `codigo` = :codigo AND `ativa` = 1 LIMIT 1");
                $stmt->execute([':codigo' => $codigo]);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM `salas` WHERE `id` = :id AND `ativa` = 1 LIMIT 1");
                $stmt->execute([':id' => $salaId]);
            }
            $sala = $stmt->fetch();

            if (!$sala) {
                echo json_encode(['sucesso' => false, 'erro' => 'Este círculo de invocação não existe ou já foi dissipado.']);
                exit;
            }

            // Verifica jogadores ativos
            $stmtCount = $pdo->prepare("
                SELECT COUNT(*) FROM `sala_jogadores` 
                WHERE `sala_id` = :sala_id AND `usuario_id` != :usuario_id AND `ultimo_ping` >= (NOW() - INTERVAL 30 MINUTE)
            ");
            $stmtCount->execute([':sala_id' => $sala['id'], ':usuario_id' => $usuario['id']]);
            $outrosJogadores = (int)$stmtCount->fetchColumn();

            if ($outrosJogadores >= (int)$sala['max_jogadores']) {
                echo json_encode(['sucesso' => false, 'erro' => 'Este círculo já está com a capacidade máxima de almas (' . $sala['max_jogadores'] . ').']);
                exit;
            }

            // Registra ou atualiza jogador na sala
            $stmtAdd = $pdo->prepare("
                INSERT INTO `sala_jogadores` (`sala_id`, `usuario_id`, `nome_jogador`, `ultimo_ping`)
                VALUES (:sala_id, :usuario_id, :nome_jogador, NOW())
                ON DUPLICATE KEY UPDATE `ultimo_ping` = NOW(), `nome_jogador` = :nome_jogador2
            ");
            $stmtAdd->execute([
                ':sala_id' => $sala['id'],
                ':usuario_id' => $usuario['id'],
                ':nome_jogador' => $usuario['nome'],
                ':nome_jogador2' => $usuario['nome']
            ]);

            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Você adentrou o círculo de invocação!',
                'sala' => $sala
            ]);
            break;

        case 'detalhes':
            $salaId = (int)($_GET['sala_id'] ?? $_POST['sala_id'] ?? 0);
            if (!$salaId) {
                echo json_encode(['sucesso' => false, 'erro' => 'Círculo espiritual não informado.']);
                exit;
            }

            $stmt = $pdo->prepare("
                SELECT s.*, u.`nome` AS `criador_nome`, u.`is_premium` AS `criador_premium` 
                FROM `salas` s 
                JOIN `usuarios` u ON u.`id` = s.`criador_id` 
                WHERE s.`id` = :id LIMIT 1
            ");
            $stmt->execute([':id' => $salaId]);
            $sala = $stmt->fetch();

            if (!$sala) {
                echo json_encode(['sucesso' => false, 'erro' => 'Círculo não encontrado.']);
                exit;
            }

            // Atualiza ping se usuário estiver logado
            if (usuarioEstaLogado()) {
                $usuario = obterUsuarioLogado($pdo);
                $stmtPing = $pdo->prepare("UPDATE `sala_jogadores` SET `ultimo_ping` = NOW() WHERE `sala_id` = :sala_id AND `usuario_id` = :uid");
                $stmtPing->execute([':sala_id' => $salaId, ':uid' => $usuario['id']]);
            }

            // Lista jogadores da sala
            $stmtJog = $pdo->prepare("
                SELECT j.`usuario_id`, j.`nome_jogador`, j.`entrou_em`, u.`is_premium`, u.`avatar`
                FROM `sala_jogadores` j
                JOIN `usuarios` u ON u.`id` = j.`usuario_id`
                WHERE j.`sala_id` = :sala_id AND j.`ultimo_ping` >= (NOW() - INTERVAL 15 MINUTE)
                ORDER BY j.`entrou_em` ASC
            ");
            $stmtJog->execute([':sala_id' => $salaId]);
            $jogadores = $stmtJog->fetchAll();

            // Verifica roleta personalizada ativa
            $stmtRoleta = $pdo->prepare("SELECT * FROM `roletas_personalizadas` WHERE `sala_id` = :sala_id AND `ativa` = 1 ORDER BY `id` DESC LIMIT 1");
            $stmtRoleta->execute([':sala_id' => $salaId]);
            $roletaAtiva = $stmtRoleta->fetch();

            // Último giro da sala
            $stmtGiro = $pdo->prepare("SELECT * FROM `historico_giros` WHERE `sala_id` = :sala_id ORDER BY `id` DESC LIMIT 1");
            $stmtGiro->execute([':sala_id' => $salaId]);
            $ultimoGiro = $stmtGiro->fetch();

            echo json_encode([
                'sucesso' => true,
                'sala' => $sala,
                'jogadores' => $jogadores,
                'total_jogadores' => count($jogadores),
                'pode_personalizar' => count($jogadores) > 4, // Regra CORE: criação de roletas personalizadas permitida em salas com mais de 4 pessoas
                'roleta_personalizada' => $roletaAtiva ? [
                    'id' => $roletaAtiva['id'],
                    'titulo' => $roletaAtiva['titulo'],
                    'opcoes' => json_decode($roletaAtiva['opcoes_json'], true)
                ] : null,
                'ultimo_giro' => $ultimoGiro
            ]);
            break;

        case 'sair':
            if (usuarioEstaLogado()) {
                $usuario = obterUsuarioLogado($pdo);
                $salaId = (int)($_POST['sala_id'] ?? 0);
                if ($salaId) {
                    $stmt = $pdo->prepare("DELETE FROM `sala_jogadores` WHERE `sala_id` = :sala_id AND `usuario_id` = :uid");
                    $stmt->execute([':sala_id' => $salaId, ':uid' => $usuario['id']]);
                }
            }
            echo json_encode(['sucesso' => true, 'mensagem' => 'Você deixou o círculo espiritual.']);
            break;

        default:
            echo json_encode(['sucesso' => false, 'erro' => 'Operação de sala desconhecida.']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Perturbação no círculo: ' . $e->getMessage()]);
}
