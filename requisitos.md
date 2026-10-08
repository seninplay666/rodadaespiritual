# Rodada Espiritual

## Objetivos
Criar uma roda com 2 Sim's e 2 não's alternados inspirado no jogo "Charlie Charlie"

### Stack Tecnlógico
- Backend: PHP estruturado com sessões nativas
- Banco de dados: MySQL (PDO para segurança)
- Frontend: HTML5, PHP, CSS, Tailwind CSS

#### Regras de negócio (CORE)
-Tratar senhas de usuários com hash bcript
-Abrir automaticamente na roda, o acesso só será aberto caso o usuário o abra, seja para acessar o seu plano premium, seus chats de conversas e suas salas de jogos 
-Chats de mensagens só serão permitidos no premium
-Salas de jogos com mais de 3 pessoas só serão permitidas no premium 
-Criação de roletas personalizadas, serão permitidas em salas com mais de 4 pessoas
-Toda resposta "Sim" será Respondida por um Pop-Up, com uma resposta "macabra", mas ao mesmo tempo divertida e que incentiva um novo jogo
-Toda resposta "Não" será Respondida por um Pop-Up, com uma resposta "macabra", mas ao mesmo tempo divertida e que incentiva um novo jogo

##### Regras Globais
- Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections
- Mantenha o código limpo e comente apenas logicas complexas.
- Separe os arquivos de forma lógica: um arquivo para conexão com a base (bd.php) e scripts de backend isolados e views em HTML5/PHP, nunca faça o sistema como um monolito, deixe sempre separados todas as regras para facilitar os futuros upgrades.
 - Estilize as telas em Tailwind de forma responsiva priorizando o MobileFrist.
 - Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)
 - sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL
 - 
  
