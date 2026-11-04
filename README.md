# Just Dance de Pobre: painel (Laravel 13 + Filament 5)

O jogo e a aula continuam iguais, mas nada fica mais escrito no código. Tudo é configurado em `/admin`:

| Menu | O que dá para fazer |
| --- | --- |
| **Aula → Missões** | Quantas etapas existem e em que ordem (arraste); título, conceito, mensagem de "completou"; código inicial que aparece no quadro; resposta; tipo de verificação (testes ou câmera); **os testes** de cada missão |
| **Jogo → Danças** | Sequência de poses (arraste para reordenar), quantas batidas cada pose dura, introdução, BPM da batida do jogo, música sugerida; botão **Duplicar** |
| **Jogo → Músicas** | Upload de mp3/ogg/wav, BPM e atraso até a primeira batida. Elas aparecem no menu **Escolha a música** do jogo |
| **Jogo → Poses** | Biblioteca de poses com os 4 ângulos e **prévia do boneco** ao vivo |
| **Jogo → Dificuldades** | Nome, tolerância (°) e janela de tempo (ms) |
| **Ajustes → Configurações** | Nome do jogo, texto da aula, valores iniciais dos sliders, nomes/notas/pontos das classes (Perfeito, Ótimo…), combo, botão "liberar tudo", modelo do MediaPipe |

O painel inicial mostra se falta algo para o jogo funcionar (por exemplo, nenhuma missão definindo `comparar`).

## Instalar

Precisa de **PHP 8.3+** (extensões `pdo_sqlite`, `intl`, `zip`, `mbstring`, `gd`) e **Composer**.

```bash
bash instalar.sh just-dance
cd just-dance
php artisan serve
```

- Jogo: http://localhost:8000
- Painel: http://localhost:8000/admin (o e-mail e a senha aparecem no fim da instalação e ficam no `.env`)

<details>
<summary>Instalação manual (se o script não rodar, por exemplo no Windows sem Git Bash)</summary>

```bash
composer create-project laravel/laravel just-dance
cd just-dance
composer require filament/filament:"^5.0"
php artisan filament:install --panels      # Enter para aceitar o painel "admin"
```

Copie as pastas `app`, `database`, `resources` e `routes` deste pacote por cima das do projeto (substituindo). No `.env`:

```
APP_LOCALE=pt_BR
ADMIN_EMAIL=admin@justdance.local
ADMIN_PASSWORD=uma-senha-sua
```

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```
</details>

## Como as missões funcionam

- A turma vê as missões **ativas**, na ordem da lista.
- Cada missão diz quais **funções** define (campo "Funções que a missão define"). O jogo precisa que alguma missão ativa defina estas quatro:

| Função | Para quê |
| --- | --- |
| `abrirCamera(video)` | abrir a câmera. Use verificação **"Abrir a câmera de verdade"** |
| `encontrarCorpo(detector, video, agoraMs)` | devolver os 33 pontos da 1ª pessoa ou `null` |
| `direcao(a, b, largura, altura)` | direção de um osso em graus |
| `comparar(aluno, alvo, tolerancia)` | devolver `{ nota, porOsso }` |

- Uma missão pode usar funções de missões anteriores. Por exemplo, dá para separar `diferenca` numa missão e `comparar` (que usa `diferenca`) na seguinte.
- Pode ter missões extras que o jogo não usa (só para treinar), basta ter testes.
- O jogo só libera quando **todas** as missões ativas passam.

### Escrevendo testes

Cada teste é JavaScript que deve dar `true`:

- `f.nomeDaFuncao(...)` chama a função escrita pela pessoa;
- `perto(a, b)` compara números com folga de 0,01;
- `pt(x, y)` cria um ponto do corpo.

```js
perto(f.diferenca(170, -170), 20)
```

Com várias linhas, termine com `return`:

```js
const r = f.comparar({ bracoE: 0, bracoD: 0 }, { bracoE: 0, bracoD: 45 }, 45);
return perto(r.porOsso.bracoE, 1) && perto(r.porOsso.bracoD, 0);
```

Para conferir se os testes estão certos: abra o jogo e clique em **Ver resposta**. A resposta cadastrada tem que passar.

## Música

- A dança é contada em **batidas**, então funciona com qualquer música: o tempo se ajusta pelo BPM.
- No menu do jogo a pessoa escolhe entre: **Batida do jogo** (sintetizada, no BPM da dança), as **músicas cadastradas** e **Arquivo do meu computador** (informando o BPM).
- **Atraso**: se a música tem introdução ou silêncio antes da batida, coloque esse tempo em ms.
- Upload: até 12 MB por arquivo (limite padrão do Livewire). Para aumentar, rode `php artisan livewire:publish --config`, mude `temporary_file_upload.rules` e ajuste `upload_max_filesize`/`post_max_size` no `php.ini`.

## Publicar na VPS

- O servidor precisa de **HTTPS**, senão o celular não libera a câmera.
- É um Laravel comum: PHP-FPM + Nginx/Caddy apontando para `public/`.
- Depois de copiar o projeto: `composer install --no-dev`, `php artisan migrate --force`, `php artisan storage:link`, `php artisan optimize`.
- Em produção (`APP_ENV=production`), só usuários cadastrados entram no `/admin` (veja `User::canAccessPanel`). Para criar outro: `php artisan make:filament-user`.

## Onde está cada coisa

```text
app/Models/                    Mission, MissionTest, Dance, DanceStep, Song, Pose, Difficulty, Setting
app/Filament/Resources/        telas do painel (Missões, Danças, Músicas, Poses, Dificuldades)
app/Filament/Pages/            Configurações
app/Filament/Widgets/          resumo "Como está o jogo"
app/Support/ConfigDoJogo.php   monta a configuração que o jogo recebe (também em /api/config)
app/Support/BonecoSvg.php      prévia das poses no painel
resources/views/jogo.blade.php o jogo (HTML + JS), lê window.JDP
database/seeders/dados/jogo.php conteúdo inicial (as 4 missões, 10 poses, 3 danças, 3 dificuldades)
```
