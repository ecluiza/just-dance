<?php

/*
|--------------------------------------------------------------------------
| Conteúdo inicial do jogo
|--------------------------------------------------------------------------
| É o que o JogoSeeder grava no banco na primeira instalação.
| Depois disso, tudo é editado pelo painel /admin.
|
| Ângulos em graus, na imagem ORIGINAL da câmera (sem espelho). O lado esquerdo da
| pessoa aparece à direita da imagem: braço esquerdo esticado = 0°, direito = 180°.
| O y cresce para baixo: apontando para cima = -90°, para baixo = 90°.
*/

return [

    'poses' => [
        ['label' => 'Braços em T', 'arm_left' => 0, 'forearm_left' => 0, 'arm_right' => 180, 'forearm_right' => 180],
        ['label' => 'Braços em Y', 'arm_left' => -55, 'forearm_left' => -55, 'arm_right' => -125, 'forearm_right' => -125],
        ['label' => 'Tudo pra cima', 'arm_left' => -80, 'forearm_left' => -88, 'arm_right' => -100, 'forearm_right' => -92],
        ['label' => 'Mãos na cintura', 'arm_left' => 60, 'forearm_left' => 150, 'arm_right' => 120, 'forearm_right' => 30],
        ['label' => 'Disco!', 'arm_left' => -45, 'forearm_left' => -45, 'arm_right' => 135, 'forearm_right' => 135],
        ['label' => 'Disco ao contrário', 'arm_left' => 45, 'forearm_left' => 45, 'arm_right' => -135, 'forearm_right' => -135],
        ['label' => 'Muque', 'arm_left' => 0, 'forearm_left' => -90, 'arm_right' => 180, 'forearm_right' => -90],
        ['label' => 'L', 'arm_left' => -90, 'forearm_left' => -90, 'arm_right' => 180, 'forearm_right' => 180],
        ['label' => 'L ao contrário', 'arm_left' => 0, 'forearm_left' => 0, 'arm_right' => -90, 'forearm_right' => -90],
        ['label' => 'Descansar', 'arm_left' => 80, 'forearm_left' => 85, 'arm_right' => 100, 'forearm_right' => 95],
    ],

    'difficulties' => [
        ['name' => 'Fácil', 'tolerance' => 60, 'window_ms' => 500],
        ['name' => 'Médio', 'tolerance' => 45, 'window_ms' => 400],
        ['name' => 'Difícil', 'tolerance' => 30, 'window_ms' => 300],
    ],

    // Músicas não vêm prontas (precisam de arquivo de áudio): cadastre no painel em Jogo → Músicas.
    // Sem música, o jogo toca a "Batida do jogo", sintetizada no navegador no BPM da dança.

    // 'steps' usa o nome (label) das poses acima
    'dances' => [
        [
            'name' => 'Aquecimento (fácil)', 'bpm' => 110, 'beats_per_pose' => 4, 'intro_beats' => 8,
            'steps' => ['Braços em T', 'Tudo pra cima', 'Mãos na cintura', 'Braços em T', 'Braços em Y',
                'Descansar', 'Muque', 'Braços em Y', 'Mãos na cintura', 'Tudo pra cima'],
        ],
        [
            'name' => 'Febre do Disco (médio)', 'bpm' => 120, 'beats_per_pose' => 2, 'intro_beats' => 8,
            'steps' => ['Disco!', 'Disco ao contrário', 'Disco!', 'Disco ao contrário', 'Braços em T', 'Muque', 'Braços em T', 'Muque',
                'L', 'L ao contrário', 'L', 'L ao contrário', 'Braços em Y', 'Mãos na cintura', 'Braços em Y', 'Tudo pra cima'],
        ],
        [
            'name' => 'Turbo (difícil)', 'bpm' => 128, 'beats_per_pose' => 2, 'intro_beats' => 8,
            'steps' => ['Braços em Y', 'Muque', 'Disco!', 'L ao contrário', 'Mãos na cintura', 'Braços em T', 'Disco ao contrário', 'L',
                'Tudo pra cima', 'Muque', 'Disco!', 'Disco ao contrário', 'Braços em Y', 'Mãos na cintura', 'L', 'L ao contrário',
                'Braços em T', 'Tudo pra cima', 'Muque', 'Descansar'],
        ],
    ],

    // A dança que vale para o ranking (todo mundo dança a mesma coisa).
    'competition' => ['dance' => 'Febre do Disco (médio)', 'difficulty' => 'Médio'],

    // Missões da aula: um experimento com a câmera por etapa. 'settings' são os alvos do desafio.
    'missions' => [
        [
            'title' => 'Uma imagem é feita de números',
            'type' => 'pixels',
            'concept' => 'Para o computador, uma imagem é uma tabela (matriz) de pixels. Cada pixel tem 3 números de 0 a 255: vermelho, verde e azul (RGB).',
            'challenge' => 'Mexa na resolução para ver os pixels. Depois toque em algo bem vermelho (roupa, estojo, boca) até achar um pixel com muito vermelho e pouco verde e azul.',
            'unlock_message' => 'você achou o vermelho nos números!',
            'settings' => ['canal' => 'r', 'minimo' => 170, 'max_outros' => 110],
            'code_snippet' => <<<'JS'
// Cada pixel tem 3 números: vermelho, verde e azul (0 a 255)
const imagem = ctx.getImageData(0, 0, largura, altura);
const i = (y * largura + x) * 4;
const r = imagem.data[i];
const g = imagem.data[i + 1];
const b = imagem.data[i + 2];
JS,
        ],
        [
            'title' => 'Cores e contraste',
            'type' => 'cores',
            'concept' => 'Dá para separar a imagem em canais (só vermelho, só verde, só azul) ou transformar em tons de cinza. Com um limiar, cada pixel vira preto ou branco: é o processamento mais simples que existe.',
            'challenge' => 'Experimente cada filtro por alguns segundos. No "limiar", mexa no slider até sua silhueta ficar separada do fundo.',
            'unlock_message' => 'você viu a imagem com outros olhos!',
            'settings' => ['modos' => ['cinza', 'vermelho', 'verde', 'azul', 'limiar'], 'segurar_ms' => 1500],
            'code_snippet' => <<<'JS'
// Tons de cinza: média dos 3 canais (o olho é mais sensível ao verde)
const cinza = 0.299 * r + 0.587 * g + 0.114 * b;

// Limiar (binarização): acima do limite vira branco, abaixo vira preto
const pixel = cinza > limiar ? 255 : 0;
JS,
        ],
        [
            'title' => 'Bordas',
            'type' => 'bordas',
            'concept' => 'Antes da inteligência artificial, o computador achava formas procurando bordas: lugares onde a cor muda de repente. Isso é feito com um filtro que compara cada pixel com os vizinhos (convolução).',
            'challenge' => 'Ajuste a sensibilidade até seu contorno aparecer bem marcado, sem virar um borrão.',
            'unlock_message' => 'você enxergou como os filtros veem!',
            'settings' => ['min_bordas' => 4, 'max_bordas' => 18, 'segurar_ms' => 2000],
            'code_snippet' => <<<'JS'
// Filtro de Sobel: compara cada pixel (c) com os vizinhos
const gx = (c(x+1, y-1) + 2*c(x+1, y) + c(x+1, y+1)) - (c(x-1, y-1) + 2*c(x-1, y) + c(x-1, y+1));
const gy = (c(x-1, y+1) + 2*c(x, y+1) + c(x+1, y+1)) - (c(x-1, y-1) + 2*c(x, y-1) + c(x+1, y-1));
const forca = Math.hypot(gx, gy);  // quanto a cor muda ali
const ehBorda = forca > limite;     // sensibilidade alta = limite baixo = mais bordas
JS,
        ],
        [
            'title' => 'A IA acha o corpo',
            'type' => 'pontos',
            'concept' => 'Um modelo de inteligência artificial (MediaPipe), treinado com milhares de fotos, acha 33 pontos do corpo em cada frame. Cada ponto vem com uma confiança: o quanto o modelo tem certeza de que está vendo aquilo.',
            'challenge' => 'Esconda as duas mãos atrás das costas até a confiança dos pulsos cair. Depois mostre as mãos de novo.',
            'unlock_message' => 'você viu a confiança da IA subir e descer!',
            'settings' => ['pontos' => [15, 16], 'baixo' => 0.3, 'alto' => 0.8, 'segurar_ms' => 1000],
            'code_snippet' => <<<'JS'
const resultado = detector.detectForVideo(video, performance.now());
const pontos = resultado.landmarks[0]; // 33 pontos: {x, y, visibility}
const pulso = pontos[15];              // 15 = pulso esquerdo
console.log(pulso.visibility);         // 0 = não vê, 1 = certeza
JS,
        ],
        [
            'title' => 'Do ponto ao ângulo',
            'type' => 'angulo',
            'concept' => 'Pontos sozinhos mudam quando você se mexe ou chega perto. Por isso transformamos pontos em características: a direção de cada osso, em graus. Ela é a mesma longe ou perto da câmera.',
            'challenge' => 'Faça cada posição pedida e segure até a barra encher.',
            'unlock_message' => 'você virou um transferidor humano!',
            'settings' => [
                'alvos' => [
                    ['osso' => 'bracoE', 'angulo' => 0, 'rotulo' => 'Estique o braço esquerdo para o lado (0°)'],
                    ['osso' => 'bracoE', 'angulo' => -90, 'rotulo' => 'Agora aponte o braço esquerdo para cima (-90°)'],
                ],
                'tolerancia' => 15,
                'segurar_ms' => 2000,
            ],
            'code_snippet' => <<<'JS'
// Direção do osso que vai do ombro (a) até o cotovelo (b), em graus
const dx = (b.x - a.x) * largura;
const dy = (b.y - a.y) * altura;
const angulo = Math.atan2(dy, dx) * 180 / Math.PI;
// direita = 0°, baixo = 90°, cima = -90°, esquerda = 180°
JS,
        ],
        [
            'title' => 'Isso é a pose?',
            'type' => 'comparar',
            'concept' => 'Para dar nota, o jogo compara o ângulo de cada osso com o da pose-alvo. A tolerância diz quanto pode errar: com 15° o jogo fica exigente, com 90° qualquer coisa vale.',
            'challenge' => 'Imite o boneco azul até tirar a nota mínima. Depois teste o slider de tolerância e veja a nota mudar.',
            'unlock_message' => 'agora você sabe como o jogo dá nota!',
            'settings' => ['pose' => 'Braços em Y', 'nota_minima' => 0.85, 'segurar_ms' => 1500, 'tolerancia' => 45],
            'code_snippet' => <<<'JS'
// Para cada osso: quanto longe do alvo, dentro da tolerância
const d = Math.abs(aluno - alvo) % 360;
const diferenca = d > 180 ? 360 - d : d; // 170° e -170° estão a 20°
const nota = Math.max(0, 1 - diferenca / tolerancia);
// nota da pose = média das notas dos ossos
JS,
        ],
        [
            'title' => 'Engane a IA',
            'type' => 'livre',
            'concept' => 'Todo modelo aprende com exemplos e falha quando vê algo diferente do treino. Entender os limites é parte da visão computacional (e do uso responsável de IA).',
            'challenge' => 'Tente fazer o esqueleto errar: pouca luz, duas pessoas na câmera, casaco largo, ficar de costas. O que confundiu mais? Por quê? Quando terminar a discussão, clique em Concluir.',
            'unlock_message' => 'vocês conhecem os limites da IA. O jogo está liberado!',
            'settings' => [],
            'code_snippet' => null,
        ],
    ],
];
