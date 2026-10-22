/**
 * Motor Interativo da Roda Espiritual "Charlie Charlie"
 * 2 SIM's e 2 NÃO's alternados
 * Animação com física sobrenatural, sons e pop-ups macabros.
 */

class RoletaEspiritual {
    constructor() {
        this.girando = false;
        this.rotacaoAtual = 0;
        this.modoVisual = 'charlie'; // 'charlie' ou 'disco'
        this.salaId = null;

        // Ângulos precisos para os 4 quadrantes alternados:
        // Quadrante 0: Superior-Esquerdo = SIM (45°) -> Ponta do lápis aponta para Top-Left (SIM)
        // Quadrante 1: Superior-Direito  = NÃO (135°) -> Ponta do lápis aponta para Top-Right (NÃO)
        // Quadrante 2: Inferior-Direito  = SIM (225°) -> Ponta do lápis aponta para Bottom-Right (SIM)
        // Quadrante 3: Inferior-Esquerdo = NÃO (315°) -> Ponta do lápis aponta para Bottom-Left (NÃO)
        this.angulosCharlie = [45, 135, 225, 315];
        this.angulosDisco = [315, 225, 135, 45];
        this.angulosQuadrantes = this.angulosCharlie;

        this.inicializar();
    }

    inicializar() {
        this.inputPergunta = document.getElementById('input-pergunta');
        this.btnGirar = document.getElementById('btn-girar-roda');
        this.lapisGiratorio = document.getElementById('lapis-giratorio');
        this.discoRoleta = document.getElementById('roleta-disco');
        this.tabuleiroCharlie = document.getElementById('tabuleiro-charlie');
        this.containerDisco = document.getElementById('container-roleta-disco');
        this.historicoLista = document.getElementById('historico-giros-lista');

        if (this.btnGirar) {
            this.btnGirar.addEventListener('click', () => this.girar());
        }

        if (this.inputPergunta) {
            this.inputPergunta.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    this.girar();
                }
            });
        }

        // Alternador de visual
        const btnAlternarVisual = document.getElementById('btn-alternar-visual');
        if (btnAlternarVisual) {
            btnAlternarVisual.addEventListener('click', () => this.alternarVisual());
        }

        // Sugestões rápidas de perguntas macabras
        document.querySelectorAll('.btn-sugestao-pergunta').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const texto = e.currentTarget.getAttribute('data-pergunta');
                if (this.inputPergunta && texto) {
                    this.inputPergunta.value = texto;
                    this.girar();
                }
            });
        });
    }

    alternarVisual() {
        if (this.modoVisual === 'charlie') {
            this.modoVisual = 'disco';
            if (this.tabuleiroCharlie) this.tabuleiroCharlie.classList.add('hidden');
            if (this.containerDisco) this.containerDisco.classList.remove('hidden');
            mostrarToast('Modo Roleta Ritualística ativado.', 'aviso', 2500);
        } else {
            this.modoVisual = 'charlie';
            if (this.tabuleiroCharlie) this.tabuleiroCharlie.classList.remove('hidden');
            if (this.containerDisco) this.containerDisco.classList.add('hidden');
            mostrarToast('Modo Tradicional Lápis Charlie Charlie ativado.', 'aviso', 2500);
        }
    }

    definirSala(salaId) {
        this.salaId = salaId;
    }

    async girar() {
        if (this.girando) return;

        const pergunta = this.inputPergunta ? this.inputPergunta.value.trim() : '';
        const perguntaFinal = pergunta || 'Espírito, você está presente entre nós?';

        this.girando = true;
        if (this.btnGirar) {
            this.btnGirar.disabled = true;
            this.btnGirar.classList.add('opacity-50', 'cursor-not-allowed');
            this.btnGirar.innerHTML = `<span class="animate-spin mr-2">🕯️</span>Invocando os Mortos...`;
        }

        // Inicia áudios sobrenaturais
        if (window.audioEspectral) {
            window.audioEspectral.obterContexto();
            window.audioEspectral.tocarSussurro();
            window.audioEspectral.tocarGiroLapis();
        }

        // Pequena oscilação inicial do lápis (hesitação sobrenatural)
        if (this.lapisGiratorio && this.modoVisual === 'charlie') {
            this.lapisGiratorio.style.transition = 'transform 0.4s ease-in-out';
            this.lapisGiratorio.style.transform = `rotate(${this.rotacaoAtual - 25}deg)`;
        }

        try {
            // Requisição segura para o backend PHP via POST
            const formData = new FormData();
            formData.append('pergunta', perguntaFinal);
            if (this.salaId) {
                formData.append('sala_id', this.salaId);
            }

            const resposta = await fetch('api/girar.php', {
                method: 'POST',
                body: formData
            });

            const dados = await resposta.json();

            if (!dados.sucesso) {
                mostrarToast(dados.erro || 'Falha ao conectar com o plano espiritual.', 'erro');
                this.finalizarGiro();
                return;
            }

            // Executa a rotação sobrenatural
            this.executarAnimacaoRotacao(dados);

        } catch (erro) {
            mostrarToast('Perturbação cósmica: os espíritos romperam a conexão.', 'erro');
            this.finalizarGiro();
        }
    }

    executarAnimacaoRotacao(dados) {
        const indice = dados.indice !== undefined ? dados.indice : 0;
        const tabelaAngulos = this.modoVisual === 'charlie' ? this.angulosCharlie : this.angulosDisco;
        const anguloAlvo = tabelaAngulos[indice] !== undefined ? tabelaAngulos[indice] : 0;

        // Dá entre 5 e 8 voltas completas para o suspense
        const voltasCompletas = (Math.floor(Math.random() * 3) + 5) * 360;
        
        // Calcula a nova rotação contínua e exata (sempre para frente)
        const offsetAtual = this.rotacaoAtual % 360;
        const diferenca = ((anguloAlvo - offsetAtual) + 360) % 360;
        this.rotacaoAtual += voltasCompletas + diferenca;

        // Aplica rotação ao elemento ativo
        if (this.modoVisual === 'charlie' && this.lapisGiratorio) {
            this.lapisGiratorio.style.transition = 'transform 3.8s cubic-bezier(0.15, 0.85, 0.25, 1.0)';
            this.lapisGiratorio.style.transform = `rotate(${this.rotacaoAtual}deg)`;
        } else if (this.discoRoleta) {
            this.discoRoleta.style.transition = 'transform 4.0s cubic-bezier(0.12, 0.88, 0.28, 1.0)';
            this.discoRoleta.style.transform = `rotate(${this.rotacaoAtual}deg)`;
        }

        // Aguarda a parada exata do ponteiro/lápis
        setTimeout(() => {
            // Efeito de impacto sonoro
            if (window.audioEspectral) {
                window.audioEspectral.tocarImpactoResposta(dados.tipo);
            }

            // Destaca o quadrante vencedor no tabuleiro
            this.destacarQuadrante(indice, dados.tipo);

            // Adiciona ao histórico na tela
            this.adicionarAoHistorico(dados);

            // Regra CORE: Exibir Pop-Up macabro e divertido
            setTimeout(() => {
                mostrarPopUpResposta({
                    resultado: dados.resultado,
                    respostaMacabra: dados.respostaMacabra || dados.resposta_macabra,
                    pergunta: dados.pergunta,
                    tipo: dados.tipo,
                    aoJogarNovamente: () => {
                        if (this.inputPergunta) {
                            this.inputPergunta.value = '';
                            this.inputPergunta.focus();
                        }
                    }
                });

                this.finalizarGiro();
            }, 450);

        }, 3900);
    }

    destacarQuadrante(indice, tipo = 'sim') {
        const quadrantes = document.querySelectorAll('.quadrante-charlie');
        quadrantes.forEach(q => {
            q.classList.remove('bg-red-950/90', 'bg-stone-800/90', 'ring-4', 'ring-red-500', 'ring-stone-300', 'shadow-[0_0_25px_#dc2626]', 'shadow-[0_0_25px_rgba(255,255,255,0.4)]');
        });

        const quadranteAlvo = document.getElementById(`quadrante-${indice}`);
        if (quadranteAlvo) {
            const isSim = tipo === 'sim';
            if (isSim) {
                quadranteAlvo.classList.add('bg-red-950/90', 'ring-4', 'ring-red-500', 'shadow-[0_0_25px_#dc2626]');
            } else {
                quadranteAlvo.classList.add('bg-stone-800/90', 'ring-4', 'ring-stone-300', 'shadow-[0_0_25px_rgba(255,255,255,0.4)]');
            }

            setTimeout(() => {
                quadranteAlvo.classList.remove('bg-red-950/90', 'bg-stone-800/90', 'ring-4', 'ring-red-500', 'ring-stone-300', 'shadow-[0_0_25px_#dc2626]', 'shadow-[0_0_25px_rgba(255,255,255,0.4)]');
            }, 3500);
        }
    }

    adicionarAoHistorico(dados) {
        if (!this.historicoLista) return;

        const item = document.createElement('div');
        const isSim = dados.tipo === 'sim';
        item.className = 'p-3 bg-stone-900/70 rounded-lg border border-red-900/40 flex items-center justify-between text-xs transition-all hover:border-red-600';
        item.innerHTML = `
            <div class="truncate mr-2 max-w-[200px] sm:max-w-xs">
                <span class="text-gray-400">"${dados.pergunta}"</span>
            </div>
            <div class="flex items-center space-x-2 flex-shrink-0">
                <span class="font-horror text-sm ${isSim ? 'text-red-500' : 'text-stone-300'} font-bold">${dados.resultado}</span>
                <span class="text-stone-600">${dados.data_hora || 'Agora'}</span>
            </div>
        `;

        this.historicoLista.prepend(item);

        // Limita a 5 itens visíveis
        while (this.historicoLista.children.length > 6) {
            this.historicoLista.removeChild(this.historicoLista.lastChild);
        }
    }

    finalizarGiro() {
        this.girando = false;
        if (this.btnGirar) {
            this.btnGirar.disabled = false;
            this.btnGirar.classList.remove('opacity-50', 'cursor-not-allowed');
            this.btnGirar.innerHTML = `
                <span class="text-xl mr-2">☠️</span>
                <span>GIRAR A RODA DO ALÉM</span>
            `;
        }
    }
}

window.roletaEspiritual = new RoletaEspiritual();
