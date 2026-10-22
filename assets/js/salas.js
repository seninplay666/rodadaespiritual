/**
 * Gerenciador de Salas Sobrenaturais, Chat e Roletas Personalizadas
 */

class GerenciadorSalas {
    constructor() {
        this.usuario = null;
        this.salaAtiva = null;
        this.intervaloSincronizacao = null;
        this.inicializar();
    }

    async inicializar() {
        await this.verificarAutenticacao();
        this.vincularEventos();
    }

    async verificarAutenticacao() {
        try {
            const res = await fetch('api/auth.php?action=me');
            const dados = await res.json();
            if (dados.sucesso && dados.logado) {
                this.usuario = dados.usuario;
                this.atualizarInterfaceUsuario();
            } else {
                this.usuario = null;
                this.atualizarInterfaceUsuario();
            }
        } catch (e) {
            console.error('Erro de autenticação:', e);
        }
    }

    atualizarInterfaceUsuario() {
        const areaUsuario = document.getElementById('area-usuario-logado');
        const areaDeslogado = document.getElementById('area-usuario-deslogado');
        const badgePremium = document.getElementById('badge-usuario-premium');
        const nomeUsuario = document.getElementById('nome-usuario-topo');

        if (this.usuario) {
            if (areaUsuario) areaUsuario.classList.remove('hidden');
            if (areaDeslogado) areaDeslogado.classList.add('hidden');
            if (nomeUsuario) nomeUsuario.textContent = this.usuario.nome;
            if (badgePremium) {
                if (parseInt(this.usuario.is_premium) === 1) {
                    badgePremium.classList.remove('hidden');
                } else {
                    badgePremium.classList.add('hidden');
                }
            }
        } else {
            if (areaUsuario) areaUsuario.classList.add('hidden');
            if (areaDeslogado) areaDeslogado.classList.remove('hidden');
        }
    }

    vincularEventos() {
        // Botão de abrir modal de Login/Cadastro
        document.querySelectorAll('.btn-abrir-auth').forEach(btn => {
            btn.addEventListener('click', () => this.abrirModalAuth());
        });

        // Botão de Logout
        const btnLogout = document.getElementById('btn-logout');
        if (btnLogout) {
            btnLogout.addEventListener('click', () => this.fazerLogout());
        }

        // Botão de Criar Sala
        const btnCriarSala = document.getElementById('btn-criar-sala-modal');
        if (btnCriarSala) {
            btnCriarSala.addEventListener('click', () => this.abrirModalCriarSala());
        }

        // Formulário de envio de chat
        const formChat = document.getElementById('form-envio-chat');
        if (formChat) {
            formChat.addEventListener('submit', (e) => {
                e.preventDefault();
                this.enviarMensagemChat();
            });
        }

        // Botão de Criar Roleta Personalizada
        const btnRoletaPersonalizada = document.getElementById('btn-abrir-roleta-personalizada');
        if (btnRoletaPersonalizada) {
            btnRoletaPersonalizada.addEventListener('click', () => this.abrirModalRoletaPersonalizada());
        }

        // Botão de Upgrade Premium
        document.querySelectorAll('.btn-abrir-premium').forEach(btn => {
            btn.addEventListener('click', () => this.abrirModalPremium());
        });
    }

    abrirModalAuth(aba = 'login') {
        const modal = document.getElementById('modal-autenticacao');
        if (modal) {
            modal.classList.remove('hidden');
            this.alternarAbaAuth(aba);
        }
    }

    fecharModalAuth() {
        const modal = document.getElementById('modal-autenticacao');
        if (modal) modal.classList.add('hidden');
    }

    alternarAbaAuth(aba) {
        const formLogin = document.getElementById('form-login');
        const formCad = document.getElementById('form-cadastro');
        const tabLogin = document.getElementById('tab-btn-login');
        const tabCad = document.getElementById('tab-btn-cadastro');

        if (aba === 'login') {
            if (formLogin) formLogin.classList.remove('hidden');
            if (formCad) formCad.classList.add('hidden');
            if (tabLogin) tabLogin.className = 'flex-1 py-2 font-ritual text-sm border-b-2 border-red-500 text-red-400 font-bold';
            if (tabCad) tabCad.className = 'flex-1 py-2 font-ritual text-sm border-b border-stone-800 text-stone-500 hover:text-stone-300';
        } else {
            if (formLogin) formLogin.classList.add('hidden');
            if (formCad) formCad.classList.remove('hidden');
            if (tabCad) tabCad.className = 'flex-1 py-2 font-ritual text-sm border-b-2 border-red-500 text-red-400 font-bold';
            if (tabLogin) tabLogin.className = 'flex-1 py-2 font-ritual text-sm border-b border-stone-800 text-stone-500 hover:text-stone-300';
        }
    }

    async executarLogin(email, senha) {
        try {
            const fd = new FormData();
            fd.append('action', 'login');
            fd.append('email', email);
            fd.append('senha', senha);

            const res = await fetch('api/auth.php', { method: 'POST', body: fd });
            const dados = await res.json();

            if (dados.sucesso) {
                mostrarToast(dados.mensagem, 'sucesso');
                this.usuario = dados.usuario;
                this.atualizarInterfaceUsuario();
                this.fecharModalAuth();
            } else {
                mostrarToast(dados.erro || 'Falha no login espectral.', 'erro');
            }
        } catch (e) {
            mostrarToast('Erro de conexão ao além.', 'erro');
        }
    }

    async executarCadastro(nome, email, senha) {
        try {
            const fd = new FormData();
            fd.append('action', 'cadastrar');
            fd.append('nome', nome);
            fd.append('email', email);
            fd.append('senha', senha);

            const res = await fetch('api/auth.php', { method: 'POST', body: fd });
            const dados = await res.json();

            if (dados.sucesso) {
                mostrarToast(dados.mensagem, 'sucesso');
                this.usuario = dados.usuario;
                this.atualizarInterfaceUsuario();
                this.fecharModalAuth();
            } else {
                mostrarToast(dados.erro || 'Falha no cadastro arcano.', 'erro');
            }
        } catch (e) {
            mostrarToast('Erro de conexão ao além.', 'erro');
        }
    }

    async fazerLogout() {
        try {
            await fetch('api/auth.php?action=logout');
            this.usuario = null;
            this.atualizarInterfaceUsuario();
            mostrarToast('Você retornou ao mundo dos vivos.', 'aviso');
            setTimeout(() => window.location.reload(), 800);
        } catch (e) {
            window.location.reload();
        }
    }

    abrirModalCriarSala() {
        if (!this.usuario) {
            mostrarToast('Você precisa se identificar no portal para fundar um círculo.', 'aviso');
            this.abrirModalAuth('login');
            return;
        }

        const modal = document.getElementById('modal-criar-sala');
        if (modal) modal.classList.remove('hidden');
    }

    fecharModalCriarSala() {
        const modal = document.getElementById('modal-criar-sala');
        if (modal) modal.classList.add('hidden');
    }

    async confirmarCriarSala() {
        const inputNome = document.getElementById('input-nome-sala');
        const inputMax = document.getElementById('input-max-jogadores');

        const nome = inputNome ? inputNome.value.trim() : '';
        const max = inputMax ? parseInt(inputMax.value) : 3;

        // Regra CORE: Salas de jogos com mais de 3 pessoas só serão permitidas no premium
        const ehPremium = this.usuario && parseInt(this.usuario.is_premium) === 1;
        if (max > 3 && !ehPremium) {
            mostrarToast('Salas com mais de 3 pessoas são exclusivas para o Plano Premium!', 'erro');
            this.abrirModalPremium();
            return;
        }

        try {
            const fd = new FormData();
            fd.append('action', 'criar');
            fd.append('nome', nome);
            fd.append('max_jogadores', max);

            const res = await fetch('api/salas.php', { method: 'POST', body: fd });
            const dados = await res.json();

            if (dados.sucesso) {
                mostrarToast(dados.mensagem, 'sucesso');
                this.fecharModalCriarSala();
                // Redireciona ou carrega a sala criada
                window.location.href = `salas.php?sala_id=${dados.sala.id}`;
            } else {
                mostrarToast(dados.erro || 'Falha ao criar o círculo.', 'erro');
                if (dados.erro && dados.erro.includes('Premium')) {
                    this.abrirModalPremium();
                }
            }
        } catch (e) {
            mostrarToast('Perturbação ao criar sala.', 'erro');
        }
    }

    async entrarSala(codigoOuId) {
        if (!this.usuario) {
            mostrarToast('Identifique sua alma antes de adentrar a seita.', 'aviso');
            this.abrirModalAuth('login');
            return;
        }

        try {
            const fd = new FormData();
            fd.append('action', 'entrar');
            if (typeof codigoOuId === 'string' && codigoOuId.startsWith('RITUAL-')) {
                fd.append('codigo', codigoOuId);
            } else {
                fd.append('sala_id', codigoOuId);
            }

            const res = await fetch('api/salas.php', { method: 'POST', body: fd });
            const dados = await res.json();

            if (dados.sucesso) {
                mostrarToast(dados.mensagem, 'sucesso');
                window.location.href = `salas.php?sala_id=${dados.sala.id}`;
            } else {
                mostrarToast(dados.erro || 'Falha ao adentrar a sala.', 'erro');
            }
        } catch (e) {
            mostrarToast('Falha na transposição dimensional.', 'erro');
        }
    }

    async carregarSalasLobby() {
        const container = document.getElementById('lista-salas-lobby');
        if (!container) return;

        try {
            const res = await fetch('api/salas.php?action=listar');
            const dados = await res.json();

            if (dados.sucesso && dados.salas) {
                if (dados.salas.length === 0) {
                    container.innerHTML = `
                        <div class="col-span-full text-center py-12 text-stone-500 font-medieval">
                            <span class="text-4xl block mb-2">🕯️</span>
                            Nenhum círculo de invocação ativo no momento. Seja o primeiro a abrir um portal!
                        </div>
                    `;
                    return;
                }

                container.innerHTML = dados.salas.map(s => `
                    <div class="bg-stone-900/80 border border-red-900/50 hover:border-red-500 rounded-xl p-5 transition-all shadow-lg hover:shadow-[0_0_20px_rgba(220,38,38,0.25)] flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-mono px-2 py-0.5 rounded bg-stone-950 text-red-400 border border-red-900/40">${s.codigo}</span>
                                <span class="text-xs ${parseInt(s.total_jogadores) >= parseInt(s.max_jogadores) ? 'text-red-500 font-bold' : 'text-emerald-400'}">
                                    👤 ${s.total_jogadores}/${s.max_jogadores} almas
                                </span>
                            </div>
                            <h4 class="font-bold text-red-200 text-lg mb-1 font-ritual">${s.nome}</h4>
                            <p class="text-xs text-stone-400 mb-4">Invocador: <span class="text-stone-300 font-semibold">${s.criador_nome}</span> ${parseInt(s.criador_premium) === 1 ? '<span class="text-amber-400 font-bold" title="Criador Premium">★</span>' : ''}</p>
                        </div>
                        <button onclick="gerenciadorSalas.entrarSala(${s.id})" class="w-full bg-red-950/80 hover:bg-red-800 text-red-200 hover:text-white py-2 rounded-lg border border-red-700/60 transition-colors text-xs font-ritual font-bold tracking-wider">
                            ADENTRAR CÍRCULO →
                        </button>
                    </div>
                `).join('');
            }
        } catch (e) {
            console.error('Erro ao carregar lobby:', e);
        }
    }

    iniciarSincronizacaoSala(salaId) {
        this.salaAtiva = salaId;
        this.sincronizarSala();
        if (this.intervaloSincronizacao) clearInterval(this.intervaloSincronizacao);
        this.intervaloSincronizacao = setInterval(() => this.sincronizarSala(), 2800);
    }

    async sincronizarSala() {
        if (!this.salaAtiva) return;

        try {
            const res = await fetch(`api/sincronizar.php?sala_id=${this.salaAtiva}`);
            const dados = await res.json();

            if (dados.sucesso) {
                this.renderizarJogadoresSala(dados.jogadores, dados.total_jogadores, dados.pode_personalizar);
                this.renderizarMensagensChat(dados.mensagens);

                // Atualiza botão de roleta personalizada
                const btnRoleta = document.getElementById('btn-abrir-roleta-personalizada');
                const avisoRoleta = document.getElementById('aviso-roleta-bloqueada');
                if (btnRoleta) {
                    if (dados.pode_personalizar) {
                        btnRoleta.disabled = false;
                        btnRoleta.classList.remove('opacity-40', 'cursor-not-allowed');
                        if (avisoRoleta) avisoRoleta.classList.add('hidden');
                    } else {
                        btnRoleta.disabled = true;
                        btnRoleta.classList.add('opacity-40', 'cursor-not-allowed');
                        if (avisoRoleta) {
                            avisoRoleta.classList.remove('hidden');
                            avisoRoleta.textContent = `🔒 Requer mais de 4 pessoas na sala (atualmente: ${dados.total_jogadores})`;
                        }
                    }
                }
            }
        } catch (e) {
            console.error('Falha de sincronização:', e);
        }
    }

    renderizarJogadoresSala(jogadores, total, podePersonalizar) {
        const container = document.getElementById('lista-jogadores-sala');
        const contagem = document.getElementById('contagem-jogadores-sala');
        if (contagem) contagem.textContent = total;

        if (container && jogadores) {
            container.innerHTML = jogadores.map(j => `
                <div class="flex items-center space-x-2 py-1.5 px-2 rounded bg-stone-900/60 border border-stone-800 text-xs">
                    <span class="text-sm">🕯️</span>
                    <span class="font-medium text-stone-200 truncate flex-1">${j.nome_jogador}</span>
                    ${parseInt(j.is_premium) === 1 ? '<span class="text-amber-400 font-bold text-xs" title="Alma Premium">★ PREMIUM</span>' : ''}
                </div>
            `).join('');
        }
    }

    renderizarMensagensChat(mensagens) {
        const container = document.getElementById('chat-mensagens-container');
        if (!container || !mensagens) return;

        const rolouAteFim = container.scrollHeight - container.scrollTop <= container.clientHeight + 50;

        container.innerHTML = mensagens.map(m => `
            <div class="mb-3 text-xs">
                <div class="flex items-center space-x-1.5 mb-0.5">
                    <span class="font-bold ${parseInt(m.is_premium) === 1 ? 'text-amber-400' : 'text-red-400'} font-ritual">${m.usuario_nome}</span>
                    ${parseInt(m.is_premium) === 1 ? '<span class="text-[10px] bg-amber-950 text-amber-300 px-1 rounded border border-amber-800">★</span>' : ''}
                    <span class="text-[10px] text-stone-500">${m.criado_em.substring(11, 16)}</span>
                </div>
                <div class="bg-stone-900/90 text-stone-300 p-2.5 rounded-lg border border-red-950/80 break-words">
                    ${m.mensagem}
                </div>
            </div>
        `).join('');

        if (rolouAteFim) {
            container.scrollTop = container.scrollHeight;
        }
    }

    async enviarMensagemChat() {
        if (!this.usuario) {
            mostrarToast('Você deve adentrar o portal com uma conta.', 'aviso');
            this.abrirModalAuth('login');
            return;
        }

        // Regra CORE: Chats de mensagens só serão permitidos no premium
        const ehPremium = parseInt(this.usuario.is_premium) === 1;
        if (!ehPremium) {
            mostrarToast('Voz silenciada! Chats de mensagens são permitidos EXCLUSIVAMENTE para usuários Premium.', 'erro');
            this.abrirModalPremium();
            return;
        }

        const input = document.getElementById('input-mensagem-chat');
        const msg = input ? input.value.trim() : '';
        if (!msg) return;

        try {
            const fd = new FormData();
            fd.append('action', 'enviar');
            fd.append('mensagem', msg);
            if (this.salaAtiva) {
                fd.append('sala_id', this.salaAtiva);
            }

            const res = await fetch('api/chat.php', { method: 'POST', body: fd });
            const dados = await res.json();

            if (dados.sucesso) {
                if (input) input.value = '';
                this.sincronizarSala();
                if (window.audioEspectral) window.audioEspectral.tocarSussurro();
            } else {
                mostrarToast(dados.erro || 'Falha ao enviar sussurro.', 'erro');
            }
        } catch (e) {
            mostrarToast('Falha ao enviar mensagem.', 'erro');
        }
    }

    abrirModalRoletaPersonalizada() {
        // Regra CORE: Criação de roletas personalizadas, serão permitidas em salas com mais de 4 pessoas
        const containerJogadores = document.getElementById('contagem-jogadores-sala');
        const total = containerJogadores ? parseInt(containerJogadores.textContent) : 0;

        if (total <= 4) {
            mostrarToast(`A criação de roletas personalizadas exige a presença de MAIS DE 4 PESSOAS na sala (atualmente há ${total}). Reúna sua seita!`, 'erro');
            return;
        }

        const modal = document.getElementById('modal-roleta-personalizada');
        if (modal) modal.classList.remove('hidden');
    }

    fecharModalRoletaPersonalizada() {
        const modal = document.getElementById('modal-roleta-personalizada');
        if (modal) modal.classList.add('hidden');
    }

    async salvarRoletaPersonalizada() {
        const titulo = document.getElementById('input-titulo-roleta')?.value.trim() || 'Roleta da Seita';
        const opcoesTexto = document.getElementById('input-opcoes-roleta')?.value.trim() || '';

        const opcoes = opcoesTexto.split('\n').map(o => o.trim()).filter(Boolean);
        if (opcoes.length < 2) {
            mostrarToast('Insira pelo menos 2 opções (uma por linha).', 'aviso');
            return;
        }

        try {
            const fd = new FormData();
            fd.append('action', 'criar');
            fd.append('sala_id', this.salaAtiva);
            fd.append('titulo', titulo);
            opcoes.forEach(op => fd.append('opcoes[]', op));

            const res = await fetch('api/roleta_custom.php', { method: 'POST', body: fd });
            const dados = await res.json();

            if (dados.sucesso) {
                mostrarToast(dados.mensagem, 'sucesso');
                this.fecharModalRoletaPersonalizada();
                this.sincronizarSala();
            } else {
                mostrarToast(dados.erro || 'Falha ao consagrar roleta.', 'erro');
            }
        } catch (e) {
            mostrarToast('Erro ao criar roleta personalizada.', 'erro');
        }
    }

    abrirModalPremium() {
        const modal = document.getElementById('modal-plano-premium');
        if (modal) modal.classList.remove('hidden');
    }

    fecharModalPremium() {
        const modal = document.getElementById('modal-plano-premium');
        if (modal) modal.classList.add('hidden');
    }

    async alternarStatusPremium(ativar = 1) {
        if (!this.usuario) {
            mostrarToast('Acesse sua conta para selar o pacto Premium.', 'aviso');
            this.abrirModalAuth('login');
            return;
        }

        try {
            const fd = new FormData();
            fd.append('action', 'upgrade_premium');
            fd.append('status', ativar);

            const res = await fetch('api/auth.php', { method: 'POST', body: fd });
            const dados = await res.json();

            if (dados.sucesso) {
                this.usuario.is_premium = ativar;
                this.atualizarInterfaceUsuario();
                this.fecharModalPremium();
                mostrarModal({
                    titulo: ativar ? '🩸 Selo Ocultista Consagrado!' : 'Pacto Cancelado',
                    mensagem: dados.mensagem,
                    icone: ativar ? '👑' : '🕯️',
                    textoBotao: 'Desfrutar das Trevas'
                });
                if (window.audioEspectral && ativar) {
                    window.audioEspectral.tocarBadaloSino(520, 0.5);
                }
            } else {
                mostrarToast(dados.erro || 'Falha ao selar o pacto.', 'erro');
            }
        } catch (e) {
            mostrarToast('Erro ao processar invocação premium.', 'erro');
        }
    }
}

window.gerenciadorSalas = new GerenciadorSalas();
