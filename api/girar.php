<?php
/**
 * API da Roda Espiritual "Charlie Charlie"
 * Roda com 2 SIM's e 2 NÃO's alternados
 * Retorna respostas macabras, divertidas e que incentivam um novo jogo.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../auth_check.php';

try {
    $pergunta = trim($_POST['pergunta'] ?? 'Espírito, você está presente entre nós?');
    if (empty($pergunta)) {
        $pergunta = 'Espírito, você está presente entre nós?';
    }

    $salaId = !empty($_POST['sala_id']) ? (int)$_POST['sala_id'] : null;
    $usuario = obterUsuarioLogado($pdo);
    $usuarioId = $usuario ? (int)$usuario['id'] : null;

    // Verifica se a sala possui uma roleta personalizada ativa
    $roletaCustom = null;
    if ($salaId) {
        $stmtRoleta = $pdo->prepare("SELECT * FROM `roletas_personalizadas` WHERE `sala_id` = :sala_id AND `ativa` = 1 ORDER BY `id` DESC LIMIT 1");
        $stmtRoleta->execute([':sala_id' => $salaId]);
        $roletaCustom = $stmtRoleta->fetch();
    }

    $respostasSim = [
        "SIM! O espírito sussurrou que sim... e ele agora está bem atrás de você! Não olhe para trás, apenas pergunte de novo antes que ele se aproxime...",
        "SIM! O além concedeu o que você queria ouvir... Mas a que custo? Uma alma já foi marcada no submundo. Tem coragem de girar mais uma vez?",
        "SIM! O lápis tremeu e a resposta ecoou da tumba. Os mortos adoram a sua audácia. Invoque outra pergunta se não tiver medo!",
        "SIM! As velas piscaram e o espírito confirmou. Ele disse que você deve continuar jogando... ou ele puxará seu pé hoje à noite!",
        "SIM! O pacto foi aceito com sucesso! Charlie Charlie aprovou sua ousadia. Não pare agora, faça mais uma pergunta sombria!",
        "SIM! Os sussurros do além nunca mentem (quase nunca). A porta do cômodo acabou de ranger? Melhor girar de novo para conferir!",
        "SIM! A profecia foi selada com sangue e risadas espectrais. O Charlie quer outra rodada... Você ousa desafiá-lo novamente?"
    ];

    $respostasNao = [
        "NÃO! Os mortos se recusam a compactuar com você... ou talvez o espírito só tenha se assustado com a sua cara! Faça outra pergunta antes que ele fuja!",
        "NÃO! Uma força gélida bloqueou sua resposta. As trevas disseram que não, mas você pode tentar corrompê-las com uma nova pergunta!",
        "NÃO! O espírito soltou uma gargalhada sinistra e virou as costas. Prove sua bravura e faça outra pergunta imediatamente!",
        "NÃO! As entidades do submundo acharam sua pergunta um ultraje! Mas Charlie Charlie está de bom humor e permite que você pergunte de novo...",
        "NÃO! Um calafrio correu pela sua espinha e a resposta foi negada com veemência. Não fuja agora, o ritual precisa de outra pergunta!",
        "NÃO! As almas penadas discordam veementemente. A temperatura do ambiente caiu 5 graus. Quer tentar a sorte mais uma vez?",
        "NÃO! Nem o próprio diabo concordaria com isso! Mas o portal continua aberto... gire novamente se tiver estômago!"
    ];

    if ($roletaCustom && !empty($roletaCustom['opcoes_json'])) {
        $opcoes = json_decode($roletaCustom['opcoes_json'], true);
        if (!is_array($opcoes) || count($opcoes) < 2) {
            $opcoes = ['SIM', 'NÃO', 'SIM', 'NÃO'];
        }
        $totalOpcoes = count($opcoes);
        $indiceSorteado = random_int(0, $totalOpcoes - 1);
        $resultado = $opcoes[$indiceSorteado];

        // Se a opção personalizada for sim ou não, usa os textos macabros; senão gera mensagem mística
        if (mb_strtoupper(trim($resultado)) === 'SIM') {
            $respostaMacabra = $respostasSim[array_rand($respostasSim)];
            $tipo = 'sim';
        } elseif (mb_strtoupper(trim($resultado)) === 'NÃO' || mb_strtoupper(trim($resultado)) === 'NAO') {
            $respostaMacabra = $respostasNao[array_rand($respostasNao)];
            $tipo = 'nao';
        } else {
            $frasesCustom = [
                "O espírito escolheu: '{$resultado}'! O destino foi escrito nas lápides. Deseja desafiar o sobrenatural novamente?",
                "As forças ocultas apontaram para '{$resultado}'! Uma escolha perturbadora... Faça uma nova pergunta se tiver audácia!",
                "Charlie Charlie sentenciou: '{$resultado}'! O além vibra com sua curiosidade. Ouse girar a roda mais uma vez!"
            ];
            $respostaMacabra = $frasesCustom[array_rand($frasesCustom)];
            $tipo = 'custom';
        }
    } else {
        // Regra CORE: Roda com 2 SIM's e 2 NÃO's alternados
        // Índice 0: SIM, Índice 1: NÃO, Índice 2: SIM, Índice 3: NÃO
        $quadrantes = [
            0 => ['resultado' => 'SIM', 'tipo' => 'sim'],
            1 => ['resultado' => 'NÃO', 'tipo' => 'nao'],
            2 => ['resultado' => 'SIM', 'tipo' => 'sim'],
            3 => ['resultado' => 'NÃO', 'tipo' => 'nao']
        ];

        $indiceSorteado = random_int(0, 3);
        $escolha = $quadrantes[$indiceSorteado];
        $resultado = $escolha['resultado'];
        $tipo = $escolha['tipo'];

        if ($tipo === 'sim') {
            $respostaMacabra = $respostasSim[array_rand($respostasSim)];
        } else {
            $respostaMacabra = $respostasNao[array_rand($respostasNao)];
        }
    }

    // Grava no histórico de invocações
    $stmtHist = $pdo->prepare("
        INSERT INTO `historico_giros` (`sala_id`, `usuario_id`, `pergunta`, `resultado`, `resposta_macabra`)
        VALUES (:sala_id, :usuario_id, :pergunta, :resultado, :resposta_macabra)
    ");
    $stmtHist->execute([
        ':sala_id' => $salaId,
        ':usuario_id' => $usuarioId,
        ':pergunta' => $pergunta,
        ':resultado' => $resultado,
        ':resposta_macabra' => $respostaMacabra
    ]);

    $idGiro = $pdo->lastInsertId();

    echo json_encode([
        'sucesso' => true,
        'giro_id' => (int)$idGiro,
        'pergunta' => $pergunta,
        'resultado' => $resultado,
        'tipo' => $tipo,
        'indice' => $indiceSorteado,
        'resposta_macabra' => $respostaMacabra,
        'data_hora' => date('H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Os espíritos se revoltaram: ' . $e->getMessage()]);
}
