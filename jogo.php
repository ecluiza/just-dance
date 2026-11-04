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

    'missions' => [
        [
            'title' => 'Abra os olhos',
            'concept' => 'Um vídeo são ~30 imagens por segundo, e cada imagem é uma matriz de pixels.',
            'unlock_message' => 'o vídeo da câmera apareceu!',
            'check_type' => 'camera',
            'exports' => ['abrirCamera'],
            'starter_code' => <<<'JS'
// Peça a câmera ao navegador e coloque a imagem no <video>.
//  - navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
//    devolve o "stream" da câmera (use await)
//  - para mostrar no vídeo: video.srcObject = stream
//  - depois: await video.play()

async function abrirCamera(video) {
  // escreva aqui

}
JS,
            'answer_code' => <<<'JS'
async function abrirCamera(video) {
  const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
  video.srcObject = stream;
  await video.play();
}
JS,
            'tests' => [],
        ],
        [
            'title' => 'Encontre o corpo',
            'concept' => 'Um modelo treinado acha 33 pontos do corpo em cada frame, cada um com uma confiança (visibility).',
            'unlock_message' => 'o esqueleto apareceu!',
            'check_type' => 'tests',
            'exports' => ['encontrarCorpo'],
            'starter_code' => <<<'JS'
// O detector é o modelo do MediaPipe: ele olha o vídeo e acha 33 pontos do corpo.
//  - detector.detectForVideo(video, agoraMs) devolve um objeto com "landmarks"
//  - landmarks é uma LISTA DE PESSOAS; cada pessoa é uma lista de 33 pontos {x, y, visibility}
//  - se ninguém aparecer, landmarks vem vazio: []
// Devolva os pontos da PRIMEIRA pessoa, ou null se não tiver ninguém.

function encontrarCorpo(detector, video, agoraMs) {
  // escreva aqui

}
JS,
            'answer_code' => <<<'JS'
function encontrarCorpo(detector, video, agoraMs) {
  const resultado = detector.detectForVideo(video, agoraMs);
  return resultado.landmarks[0] ?? null;
}
JS,
            'tests' => [
                ['devolve os pontos da primeira pessoa', 'const p1 = [pt(.1, .1)];
return f.encontrarCorpo({ detectForVideo: () => ({ landmarks: [p1, [pt(.9, .9)]] }) }, {}, 1) === p1;'],
                ['devolve null quando não tem ninguém', 'f.encontrarCorpo({ detectForVideo: () => ({ landmarks: [] }) }, {}, 1) === null'],
                ['passa o vídeo e o tempo para o detector', 'let r; const v = {};
f.encontrarCorpo({ detectForVideo: (a, b) => { r = [a, b]; return { landmarks: [] }; } }, v, 1234);
return !!r && r[0] === v && r[1] === 1234;'],
            ],
        ],
        [
            'title' => 'Para onde aponta?',
            'concept' => 'Transformamos pontos em características: a direção de cada osso, em graus.',
            'unlock_message' => 'o medidor do braço apareceu!',
            'check_type' => 'tests',
            'exports' => ['direcao'],
            'starter_code' => <<<'JS'
// Um osso vai do ponto a até o ponto b. Devolva para onde ele aponta, em GRAUS.
//  - Math.atan2(dy, dx) dá o ângulo em radianos; graus = radianos * 180 / Math.PI
//  - na imagem o y cresce PARA BAIXO: direita = 0°, baixo = 90°, cima = -90°
//  - CUIDADO: x e y vão de 0 a 1. Multiplique dx por largura e dy por altura.

function direcao(a, b, largura, altura) {
  // escreva aqui

}
JS,
            'answer_code' => <<<'JS'
function direcao(a, b, largura, altura) {
  const dx = (b.x - a.x) * largura;
  const dy = (b.y - a.y) * altura;
  return Math.atan2(dy, dx) * 180 / Math.PI;
}
JS,
            'tests' => [
                ['apontando para a direita dá 0°', 'perto(f.direcao(pt(.2, .5), pt(.8, .5), 640, 480), 0)'],
                ['apontando para baixo dá 90°', 'perto(f.direcao(pt(.5, .2), pt(.5, .8), 640, 480), 90)'],
                ['apontando para cima dá -90°', 'perto(f.direcao(pt(.5, .8), pt(.5, .2), 640, 480), -90)'],
                ['apontando para a esquerda dá 180°', 'perto(Math.abs(f.direcao(pt(.8, .5), pt(.2, .5), 640, 480)), 180)'],
                ['usa largura e altura (640×480 → 36,87°)', 'perto(f.direcao(pt(0, 0), pt(.5, .5), 640, 480), 36.87)'],
            ],
        ],
        [
            'title' => 'Isso é a pose?',
            'concept' => 'Similaridade: quanto cada osso está longe do alvo, dentro de uma tolerância.',
            'unlock_message' => 'a nota apareceu e o jogo liberou!',
            'check_type' => 'tests',
            'exports' => ['diferenca', 'comparar'],
            'starter_code' => <<<'JS'
// Parte A: quantos graus separam duas direções? (sempre de 0 a 180)
//   PEGADINHA: 170° e -170° estão a 20° (dando a volta), não a 340°.
//   Dica: d = Math.abs(a - b) % 360; se d > 180, a resposta é 360 - d.
function diferenca(a, b) {
  // escreva aqui

}

// Parte B: compare a pose do aluno com a do alvo.
//   As poses são objetos assim: { bracoE: 30, antebracoE: -45, bracoD: 150, antebracoD: 170 }
//   Para cada osso do alvo:
//     - se o aluno não tem esse osso (a câmera não viu), pule
//     - nota do osso = Math.max(0, 1 - diferenca(aluno[osso], alvo[osso]) / tolerancia)
//   nota final = média das notas dos ossos vistos
//   se viu MENOS DA METADE dos ossos do alvo, a nota final é 0
//   devolva { nota, porOsso }  (porOsso guarda a nota de cada osso visto)
function comparar(aluno, alvo, tolerancia) {
  const ossos = Object.keys(alvo);
  const porOsso = {};
  // escreva aqui

}
JS,
            'answer_code' => <<<'JS'
function diferenca(a, b) {
  const d = Math.abs(a - b) % 360;
  return d > 180 ? 360 - d : d;
}

function comparar(aluno, alvo, tolerancia) {
  const ossos = Object.keys(alvo);
  const porOsso = {};
  let soma = 0;
  let vistos = 0;
  for (const osso of ossos) {
    if (aluno[osso] === undefined) continue;
    porOsso[osso] = Math.max(0, 1 - diferenca(aluno[osso], alvo[osso]) / tolerancia);
    soma += porOsso[osso];
    vistos++;
  }
  const nota = vistos > 0 && vistos >= ossos.length / 2 ? soma / vistos : 0;
  return { nota, porOsso };
}
JS,
            'tests' => [
                ['diferenca(30, 10) = 20', 'perto(f.diferenca(30, 10), 20)'],
                ['diferenca(10, 30) = 20 (sem negativo)', 'perto(f.diferenca(10, 30), 20)'],
                ['170° e -170° estão a 20°', 'perto(f.diferenca(170, -170), 20)'],
                ['10° e 350° estão a 20°', 'perto(f.diferenca(10, 350), 20)'],
                ['pose igual tem nota 1', 'perto(f.comparar({ bracoE: 30, bracoD: 150 }, { bracoE: 30, bracoD: 150 }, 45)?.nota, 1)'],
                ['metade da tolerância dá 0,5', 'perto(f.comparar({ bracoE: 0 }, { bracoE: 20 }, 40)?.nota, 0.5)'],
                ['fora da tolerância dá 0 (nunca negativo)', 'perto(f.comparar({ bracoE: 0 }, { bracoE: 90 }, 45)?.nota, 0)'],
                ['a nota é a média dos ossos', 'perto(f.comparar({ bracoE: 0, bracoD: 0 }, { bracoE: 0, bracoD: 45 }, 45)?.nota, 0.5)'],
                ['ignora osso que a câmera não viu', 'const r = f.comparar({ bracoE: 1, bracoD: 1, antebracoE: 1 }, { bracoE: 1, bracoD: 1, antebracoE: 1, antebracoD: 1 }, 45);
return perto(r?.nota, 1) && r.porOsso.antebracoD === undefined;'],
                ['vendo menos da metade, nota 0', 'perto(f.comparar({ bracoE: 1 }, { bracoE: 1, bracoD: 1, antebracoE: 1, antebracoD: 1 }, 45)?.nota, 0)'],
                ['preenche a nota de cada osso (porOsso)', 'const r = f.comparar({ bracoE: 0, bracoD: 0 }, { bracoE: 0, bracoD: 45 }, 45);
return perto(r?.porOsso?.bracoE, 1) && perto(r?.porOsso?.bracoD, 0);'],
            ],
        ],
    ],
];
