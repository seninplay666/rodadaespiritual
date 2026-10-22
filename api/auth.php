<?php
/**
 * API de Autenticação e Gestão de Usuários
 * Regra: Senhas tratadas com bcrypt
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../auth_check.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'cadastrar':
            $nome = trim($_POST['nome'] ?? '');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $senha = $_POST['senha'] ?? '';

            if (empty($nome) || mb_strlen($nome) < 2) {
                echo json_encode(['sucesso' => false, 'erro' => 'Revele seu verdadeiro nome ou apelido sobrenatural.']);
                exit;
            }

            if (!$email) {
                echo json_encode(['sucesso' => false, 'erro' => 'Forneça um e-mail válido para selar o pacto.']);
                exit;
            }

            if (mb_strlen($senha) < 6) {
                echo json_encode(['sucesso' => false, 'erro' => 'A senha deve conter no mínimo 6 caracteres para resistir aos espíritos.']);
                exit;
            }

            // Verifica se o email já está cadastrado
            $check = $pdo->prepare("SELECT `id` FROM `usuarios` WHERE `email` = :email LIMIT 1");
            $check->execute([':email' => $email]);
            if ($check->fetch()) {
                echo json_encode(['sucesso' => false, 'erro' => 'Esta alma já está registrada no livro dos mortos. Tente fazer login.']);
                exit;
            }

            // Regra CORE: Tratar senhas de usuários com hash bcrypt
            $senhaHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 10]);

            $stmt = $pdo->prepare("
                INSERT INTO `usuarios` (`nome`, `email`, `senha`, `is_premium`, `avatar`)
                VALUES (:nome, :email, :senha, 0, 'fantasma')
            ");
            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => $senhaHash
            ]);

            $novoId = (int)$pdo->lastInsertId();

            // Autentica automaticamente
            $_SESSION['usuario_id'] = $novoId;
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_email'] = $email;
            $_SESSION['is_premium'] = 0;

            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Pacto realizado com sucesso! Bem-vindo à Rodada Espiritual.',
                'usuario' => [
                    'id' => $novoId,
                    'nome' => $nome,
                    'email' => $email,
                    'is_premium' => 0
                ]
            ]);
            break;

        case 'login':
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $senha = $_POST['senha'] ?? '';

            if (!$email || empty($senha)) {
                echo json_encode(['sucesso' => false, 'erro' => 'Preencha todos os campos do ritual de acesso.']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT `id`, `nome`, `email`, `senha`, `is_premium`, `avatar` FROM `usuarios` WHERE `email` = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($senha, $user['senha'])) {
                echo json_encode(['sucesso' => false, 'erro' => 'E-mail ou senha incorretos. As sombras rejeitaram sua entrada.']);
                exit;
            }

            $_SESSION['usuario_id'] = (int)$user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_email'] = $user['email'];
            $_SESSION['is_premium'] = (int)$user['is_premium'];

            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Acesso concedido pelo além. Você agora está conectado.',
                'usuario' => [
                    'id' => (int)$user['id'],
                    'nome' => $user['nome'],
                    'email' => $user['email'],
                    'is_premium' => (int)$user['is_premium']
                ]
            ]);
            break;

        case 'logout':
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();

            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Você retornou com segurança ao plano dos vivos.'
            ]);
            break;

        case 'upgrade_premium':
            exigirLoginJson();
            $userId = (int)$_SESSION['usuario_id'];
            $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

            $stmt = $pdo->prepare("UPDATE `usuarios` SET `is_premium` = :status WHERE `id` = :id");
            $stmt->execute([':status' => $status, ':id' => $userId]);

            $_SESSION['is_premium'] = $status;

            echo json_encode([
                'sucesso' => true,
                'mensagem' => $status ? 'Selo Premium invocado! Você agora possui privilégios de alto ocultismo.' : 'Plano Premium cancelado.',
                'is_premium' => $status
            ]);
            break;

        case 'me':
            $usuario = obterUsuarioLogado($pdo);
            echo json_encode([
                'sucesso' => true,
                'logado' => !empty($usuario),
                'usuario' => $usuario
            ]);
            break;

        default:
            echo json_encode(['sucesso' => false, 'erro' => 'Ação espectral não reconhecida.']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Perturbação na força: ' . $e->getMessage()]);
}
