# Rebola Ai

Jogo de dança na webcam para ensinar **visão computacional**. Duas partes, na mesma página:

- **Aula**: 7 missões. Cada uma é um experimento com a câmera, com um desafio para cumprir na tela. Ninguém precisa programar.
- **Jogar**: um "Just Dance" em que o computador acha o seu corpo, mede o ângulo dos braços e dá nota para cada pose no ritmo da música. No fim, a pontuação entra num **ranking** que fica no telão.

Tudo é configurado no painel **`/admin`** (Laravel + Filament): missões, danças, músicas, poses, dificuldades, pontuação e a competição. Nada fica escrito no código.

## O que tem em cada endereço

| Endereço | Para quê |
| --- | --- |
| `/` | O jogo: aba **Aula** (missões) e aba **Jogar**. O QR code leva para cá |
| `/ranking` | Ranking para o telão: QR code, top 10, atualiza sozinho a cada 3 s |
| `/admin` | Painel de configuração (precisa de login) |
| `/api/config` | A configuração que o jogo recebe, em JSON (para conferir) |
| `/api/ranking` | Ranking em JSON (o telão consulta) |
| `POST /api/pontuacao` | O jogo envia o resultado no fim de cada dança |

## Instalar (Windows + XAMPP)

Precisa de **PHP 8.2+** e **Composer**. No `C:\xampp\php\php.ini`, tire o `;` do começo destas linhas e salve:

```ini
extension=fileinfo
extension=gd
extension=intl
extension=pdo_sqlite
extension=sqlite3
extension=zip
```

Confira com `php -m` (as seis têm que aparecer). Depois, no PowerShell:

```powershell
cd C:\projetos
composer create-project laravel/laravel rebola-ai
cd rebola-ai
composer require filament/filament
php artisan filament:install --panels      # Enter para aceitar o painel "admin"
```

> No `composer require`, não coloque versão (`"^5.0"`): o `cmd` come o `^`. Sem versão, ele escolhe a certa sozinho.
> Com PHP 8.2 o Composer instala o **Laravel 12** (o 13 pede PHP 8.3). Os dois funcionam.

Copie as pastas `app`, `database`, `resources` e `routes` deste pacote **por cima** das do projeto (substituindo). No `.env`, ajuste ou acrescente:

```
APP_NAME="Rebola Ai"
APP_LOCALE=pt_BR
ADMIN_EMAIL=admin@rebolaai.local
ADMIN_PASSWORD=uma-senha-sua
```

No `bootstrap/app.php`, dentro de `->withMiddleware(function (Middleware $middleware): void {`, troque o `//` por:

```php
$middleware->trustProxies(at: '*');
```

(sem isso o painel abre sem estilo quando acessado pelo link da Cloudflare). Por fim:

```powershell
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

- Jogo: http://localhost:8000
- Painel: http://localhost:8000/admin (login = `ADMIN_EMAIL` e `ADMIN_PASSWORD` do `.env`)

<details>
<summary>Linux / Mac / Git Bash: script que faz tudo isso</summary>

```bash
bash instalar.sh rebola-ai
cd rebola-ai
php artisan serve
```

O e-mail e a senha do painel aparecem no fim e ficam no `.env`.
</details>

## Atualizar uma instalação que já existe

1. Copie de novo as pastas `app`, `database`, `resources` e `routes` por cima das do projeto.
2. Rode `php artisan migrate` (cria o que for novo e não apaga nada).
3. Se algum arquivo deste pacote foi removido, apague também no projeto. Já removido: `app\Models\MissionTest.php`.

Quer voltar tudo ao conteúdo inicial (missões, danças, poses, competição)? `php artisan migrate:fresh --seed`. Isso **apaga o banco inteiro**: ranking, músicas cadastradas e configurações voltam ao padrão.

## O painel `/admin`

| Menu | O que dá para fazer |
| --- | --- |
| Início | Resumo "Como está o jogo": missões ativas, danças, músicas, poses e se alguma missão está sem alvo configurado |
| **Aula → Missões** | Ordem (arraste), ativar/desativar, tipo de experimento, título, conceito, desafio, mensagem de "completou", **o alvo do desafio** (os campos mudam conforme o tipo) e o código do botão "Ver como é feito" |
| **Jogo → Ranking** | Quem dançou e quantos pontos fez (atualiza a cada 5 s), apagar uma linha, **Zerar ranking**, **Abrir no telão** |
| **Jogo → Danças** | Sequência de poses (arraste para reordenar), batidas de cada pose, introdução, BPM da batida do jogo, música sugerida, botão **Duplicar** |
| **Jogo → Músicas** | Upload de mp3/ogg/wav, BPM e atraso até a primeira batida |
| **Jogo → Poses** | Os 4 ângulos de cada pose, com **prévia do boneco** ao vivo |
| **Jogo → Dificuldades** | Nome, tolerância (°) e janela de tempo (ms) |
| **Ajustes → Configurações** | Nome do jogo (aparece no jogo, no telão e no painel), texto da aula, **Competição**, "jogo sempre liberado", "pular missão", pontuação (nomes, notas mínimas e pontos de Perfeito, Ótimo, Bom, Errou), combo e opções técnicas (modelo do MediaPipe, confiança mínima, antecedência das poses) |

## Aula: as missões

- A câmera e o modelo de pose abrem sozinhos quando a página carrega.
- Clicar numa missão coloca o experimento dela no vídeo. Quando o desafio é cumprido, a missão fica ✅ e a próxima abre.
- O progresso fica salvo no navegador de cada pessoa. "recomeçar do zero" desmarca tudo.
- Com **"Mostrar pular missão"** ligado em Configurações, cada missão tem um link "pular esta missão".

As 7 missões iniciais:

| # | Missão | Tipo | O que a pessoa faz |
| --- | --- | --- | --- |
| 1 | Uma imagem é feita de números | Pixels | Diminui a resolução e toca no vídeo até achar um pixel bem vermelho |
| 2 | Cores e contraste | Cores | Usa cada filtro: tons de cinza, só vermelho, verde, azul e limiar |
| 3 | Bordas | Bordas | Ajusta a sensibilidade do filtro de Sobel até o contorno ficar marcado |
| 4 | A IA acha o corpo | Pontos | Esconde as mãos (a confiança dos pulsos cai) e mostra de novo |
| 5 | Do ponto ao ângulo | Ângulos | Estica o braço esquerdo para o lado (0°) e depois para cima (-90°) |
| 6 | Isso é a pose? | Comparar | Imita o boneco "Braços em Y" até tirar 85% |
| 7 | Engane a IA | Livre | Discussão: tenta fazer o esqueleto errar e clica em Concluir |

O que cada tipo mostra e o que dá para configurar:

| Tipo | No vídeo | Alvo configurável no painel |
| --- | --- | --- |
| **Pixels** | A imagem vira quadradinhos (slider de resolução); tocar mostra R, G, B | Cor procurada, valor mínimo dela, máximo das outras |
| **Cores e contraste** | Botões de filtro e slider de limiar | Quais filtros precisam ser usados e por quanto tempo |
| **Bordas** | Filtro de Sobel com slider de sensibilidade | % mínima e máxima de borda, tempo segurando |
| **Pontos do corpo** | Os 33 pontos coloridos pela confiança | Quais pontos, confiança para "sumiu" e para "apareceu", tempo |
| **Ângulos** | Esqueleto com o ângulo de cada osso e uma linha-guia | Lista de posições (osso + ângulo + texto), tolerância, tempo |
| **Comparar poses** | Boneco-alvo, nota ao vivo e slider de tolerância | Pose-alvo, nota mínima, tolerância inicial, tempo |
| **Livre** | Só o esqueleto e o botão Concluir | Nada |

Os ângulos são medidos na imagem **original** da câmera (sem espelho): braço esquerdo esticado para fora = 0°, para baixo = 90°, para cima = -90°, braço direito esticado para fora = 180°.

Depois de mudar missões no painel, basta recarregar a página do jogo.

## Jogar e o ranking

**Como o jogo dá nota:** o MediaPipe acha os pontos do corpo em cada quadro; o jogo transforma ombro, cotovelo e pulso no ângulo dos 4 ossos dos braços; cada pose da dança tem os 4 ângulos-alvo; a nota de cada osso cai conforme ele se afasta do alvo, até zerar na **tolerância** da dificuldade. Cada pose vale numa janela de tempo em volta da batida, e conta a melhor nota dentro dela. Acertos seguidos aumentam o **combo**, que multiplica os pontos.

**Competição** (Ajustes → Configurações → Competição):

- Escolha a **dança**, a **dificuldade** e a **música**. No jogo, essas opções ficam travadas, para todo mundo dançar a mesma coisa e o ranking ser justo. Vem marcada a "Febre do Disco (médio)", no Médio, com a música sugerida da dança (ou a batida do jogo, se ela não tiver).
- **Sem dança escolhida**, o ranking desliga e cada pessoa escolhe dança e música à vontade (inclusive um arquivo do próprio computador).
- **"Jogo sempre liberado"** (ligado por padrão) deixa dançar sem terminar as missões.

**Para quem dança:** aba **Jogar**, digita o nome, **Começar!**. No fim aparece a posição ("3º lugar de 23"), se foi recorde e o top 5. Cada nome fica no ranking com o **melhor** resultado. **Jogar de novo** mantém o nome; **Trocar de pessoa** limpa para a próxima.

O servidor recusa pontuação acima do máximo possível da dança (todas as poses em Perfeito com combo máximo).

## Celular

- O vídeo fica grudado no topo (no máximo uns 46% da tela) e as missões rolam embaixo dele.
- A câmera abre com o menor zoom que o aparelho permitir. No botão **📷**, no canto do vídeo, dá para trocar de câmera e mexer no zoom. Em muitos celulares a traseira **ultra-angular** é o "0,5×". Depende do aparelho e do navegador: a câmera frontal quase nunca tem 0,5× pelo navegador. Se só aparecer uma câmera sem zoom, afaste o celular.
- A câmera só funciona em **https** (ou em `localhost`). Por isso, no celular, use sempre o link da Cloudflare.

## Música

- A dança é contada em **batidas**, então funciona com qualquer música: o tempo se ajusta pelo BPM.
- **Atraso**: se a música tem introdução ou silêncio antes da primeira batida, coloque esse tempo em ms.
- Sem competição, o menu do jogo oferece: **Batida do jogo** (sintetizada no navegador, no BPM da dança), as **músicas cadastradas** e **Arquivo do meu computador** (informando o BPM).
- Upload: até 12 MB por arquivo (limite padrão do Livewire). Para aumentar: `php artisan livewire:publish --config`, mude `temporary_file_upload.rules` e ajuste `upload_max_filesize` e `post_max_size` no `php.ini`.

## Abrir para a sala (Cloudflare Tunnel)

Com `php artisan serve` rodando, em outro terminal:

```powershell
cloudflared tunnel --url http://localhost:8000
```

No meio do texto que aparece, procure a caixa **"Your quick Tunnel has been created!"**: o link `https://….trycloudflare.com` está logo abaixo. Para achar só o link: `cloudflared tunnel --url http://localhost:8000 2>&1 | Select-String "trycloudflare.com"`.

- O link muda toda vez que o túnel é aberto e só funciona com o notebook ligado.
- Uma mensagem de erro sobre "DNS local resolver" no começo é normal.

## Roteiro do dia

1. **Antes:** confira que a dança, a dificuldade e a música da competição estão certas (Configurações) e clique em **Zerar ranking** (Jogo → Ranking).
2. Terminal 1: `cd C:\projetos\rebola-ai` e `php artisan serve`.
3. Terminal 2: `cloudflared tunnel --url http://localhost:8000` e copie o link.
4. Notebook no telão: abra `https://<link>/` para dar a aula (botão **📱 Link para a sala** mostra o QR code das missões).
5. Na hora da competição: abra `https://<link>/ranking` em tela cheia (F11). O QR code dele também abre nas missões; a pessoa vai na aba **Jogar** para dançar.
6. **Plano B** sem internet ou sem notebook: o GitHub Pages (abaixo).

## Versão sem servidor (GitHub Pages)

O `index.html` que acompanha o pacote é o mesmo jogo, gerado a partir do `resources/views/jogo.blade.php` com a configuração padrão. Basta subir no repositório do GitHub Pages no lugar do antigo.

- Tem as missões e o jogo completos, já liberado.
- **Não tem** painel nem ranking (não há servidor para guardar as pontuações).
- Mudanças feitas no `/admin` não aparecem lá.

## Problemas comuns

| Sintoma | Causa e solução |
| --- | --- |
| "A permissão da câmera foi negada" | Clique no cadeado ao lado do endereço → Câmera → Permitir. No Windows: Configurações → Privacidade → Câmera → "Permitir que aplicativos da área de trabalho acessem". Depois clique em **Tentar de novo** |
| "A câmera está sendo usada por outro programa" | Feche Meet, Zoom, Teams ou outra aba usando a câmera |
| "Não consegui carregar o modelo de pose" | O modelo vem da internet (Google/jsDelivr). Confira a conexão e recarregue |
| Painel sem estilo pelo link da Cloudflare | Falta `$middleware->trustProxies(at: '*');` no `bootstrap/app.php` |
| Celular não abre a câmera | O endereço precisa ser `https` (use o link da Cloudflare, não o IP do notebook) |
| "Ranking desligado" no telão | Escolha a dança da competição em Configurações |
| "Não consegui salvar no ranking" | O servidor ou o túnel caiu. Volte com eles e toque em **Tentar de novo** (a pontuação ainda está na tela) |
| Jogo lento com muita gente no celular | O `php artisan serve` no Windows atende um pedido por vez. Para muitos aparelhos ao mesmo tempo, sirva pelo Apache do XAMPP apontando para a pasta `public` |
| Missão de bordas não chega no alvo | Câmera com muito ruído: aumente o máximo de bordas da missão no painel |

## Publicar numa VPS

- O servidor precisa de **HTTPS**, senão o celular não libera a câmera.
- É um Laravel comum: PHP-FPM + Nginx ou Caddy apontando para `public/`.
- Depois de copiar o projeto: `composer install --no-dev`, `php artisan migrate --force`, `php artisan storage:link`, `php artisan optimize`.
- Em produção (`APP_ENV=production`), só usuários cadastrados entram no `/admin` (veja `User::canAccessPanel`). Para criar outro: `php artisan make:filament-user`.

## Onde está cada coisa

```text
app/Models/                         Mission, Score, Dance, DanceStep, Song, Pose, Difficulty, Setting, User
app/Filament/Resources/             telas do painel (Missões, Ranking, Danças, Músicas, Poses, Dificuldades)
app/Filament/Pages/Configuracoes    Ajustes → Configurações (inclui a competição)
app/Filament/Widgets/               resumo "Como está o jogo"
app/Providers/Filament/             painel /admin (nome vem de Configurações)
app/Http/Controllers/               JogoController (página do jogo) e RankingController (/ranking e APIs)
app/Support/ConfigDoJogo.php        monta a configuração que o jogo recebe (window.JDP e /api/config)
app/Support/Ranking.php             regras do ranking: competição atual, posição, pontuação máxima
app/Support/BonecoSvg.php           prévia do boneco das poses no painel
resources/views/jogo.blade.php      o jogo inteiro (HTML + CSS + JS): missões, jogo, câmera
resources/views/ranking.blade.php   o ranking do telão
database/migrations/                tabelas do jogo, do ranking e a troca de nome para Rebola Ai
database/seeders/dados/jogo.php     conteúdo inicial: 10 poses, 3 danças, 3 dificuldades, 7 missões, competição
instalar.sh                         instalação automática (Linux/Mac/Git Bash)
```
