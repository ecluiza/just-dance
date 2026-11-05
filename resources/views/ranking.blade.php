{{-- Ranking para o telão. Atualiza sozinho a cada 3 segundos (GET /api/ranking). --}}
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Ranking · {{ $titulo }}</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏆</text></svg>" />
<style>
  :root { --fundo: #0d0b1a; --cartao: #17142b; --cartao-2: #221d3d; --borda: #342d5c; --texto: #f4f1ff; --texto-2: #a9a2cc;
    --rosa: #ff3ea5; --ciano: #2de2e6; --amarelo: #ffd319; --verde: #3cff8f; color-scheme: dark; }
  * { box-sizing: border-box; }
  body { margin: 0; min-height: 100vh; color: var(--texto); font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    background: radial-gradient(1200px 700px at 10% -10%, rgba(255, 62, 165, .22), transparent 60%), radial-gradient(900px 600px at 110% 110%, rgba(45, 226, 230, .15), transparent 60%), var(--fundo);
    display: grid; grid-template-columns: minmax(260px, 30vw) 1fr; gap: 4vw; padding: 4vh 4vw; align-items: start; }
  .lado { display: flex; flex-direction: column; gap: 2vh; align-items: center; text-align: center; position: sticky; top: 4vh; }
  .logo { font-weight: 900; font-size: clamp(1.6rem, 3vw, 3rem); line-height: 1.1; }
  .logo span { color: var(--rosa); }
  #qr { width: 100%; max-width: 380px; aspect-ratio: 1; background: #fff; border-radius: 18px; padding: 14px; }
  .chamada { font-size: clamp(1.2rem, 2.2vw, 2.2rem); font-weight: 900; color: var(--amarelo); }
  #link { color: var(--texto-2); font-size: clamp(.8rem, 1.1vw, 1.1rem); word-break: break-all; }
  h1 { margin: 0 0 .3em; font-size: clamp(2rem, 4.5vw, 4.5rem); }
  #sub { color: var(--texto-2); font-size: clamp(1rem, 1.6vw, 1.6rem); margin: 0 0 3vh; }
  ol { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 1.2vh; }
  li { display: grid; grid-template-columns: 2.2em 1fr auto; gap: .8em; align-items: center; background: var(--cartao); border: 2px solid var(--borda);
    border-radius: 16px; padding: .5em .9em; font-size: clamp(1.1rem, 2.4vw, 2.4rem); font-weight: 800; transition: transform .3s; }
  li .pos { text-align: center; color: var(--texto-2); }
  li .nome { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  li .pts { color: var(--amarelo); font-variant-numeric: tabular-nums; }
  li small { display: block; font-size: .45em; color: var(--texto-2); font-weight: 600; }
  li.p1 { border-color: #ffd319; background: linear-gradient(90deg, rgba(255, 211, 25, .25), var(--cartao)); font-size: clamp(1.4rem, 3.2vw, 3.2rem); }
  li.p2 { border-color: #c9d1e0; background: linear-gradient(90deg, rgba(201, 209, 224, .18), var(--cartao)); }
  li.p3 { border-color: #d98b4a; background: linear-gradient(90deg, rgba(217, 139, 74, .2), var(--cartao)); }
  li.novo { animation: chegou 1.2s ease-out 2; border-color: var(--verde); }
  @keyframes chegou { 0% { transform: scale(1); box-shadow: 0 0 0 rgba(60, 255, 143, 0) } 30% { transform: scale(1.04); box-shadow: 0 0 40px rgba(60, 255, 143, .7) } 100% { transform: scale(1); box-shadow: 0 0 0 rgba(60, 255, 143, 0) } }
  .vazio { color: var(--texto-2); font-size: clamp(1.2rem, 2.4vw, 2.4rem); padding: 4vh 0; }
  @media (max-width: 800px) { body { grid-template-columns: 1fr; } .lado { position: static; } #qr { max-width: 220px; } }
</style>
</head>
<body>
  <div class="lado">
    <div class="logo" id="logo"></div>
    <img id="qr" alt="QR code para jogar" />
    <div class="chamada">Escaneie e participe! 💃🕺</div>
    <div id="link"></div>
  </div>
  <main>
    <h1>🏆 Ranking</h1>
    <p id="sub">Carregando…</p>
    <ol id="lista"></ol>
  </main>

<script>
  const titulo = @json($titulo);
  const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
  document.getElementById('logo').innerHTML = esc(titulo).replace(/^(.*\s)?(\S+)$/, (_, a, b) => (a ? `${a}<span>${b}</span>` : b)); // última palavra em rosa

  const link = `${location.origin}/`; // abre nas missões
  document.getElementById('link').textContent = link;
  document.getElementById('qr').src = `https://api.qrserver.com/v1/create-qr-code/?size=600x600&margin=10&data=${encodeURIComponent(link)}`;

  const medalha = ['🥇', '🥈', '🥉'];
  let anterior = '';

  async function atualizar() {
    try {
      const r = await fetch('/api/ranking', { headers: { Accept: 'application/json' }, cache: 'no-store' });
      const d = await r.json();
      const assinatura = JSON.stringify(d);
      if (assinatura === anterior) return; // nada mudou: não refaz a lista (evita piscar)
      anterior = assinatura;
      const sub = document.getElementById('sub');
      const lista = document.getElementById('lista');
      if (!d.ativo) {
        sub.textContent = 'Ranking desligado: escolha a dança da competição no painel (/admin → Configurações).';
        lista.innerHTML = '';
        return;
      }
      sub.textContent = `${d.danca} · ${d.dificuldade} · ${d.total} ${d.total === 1 ? 'pessoa dançou' : 'pessoas dançaram'}`;
      lista.innerHTML = d.top.length
        ? d.top.map((s) => `
            <li class="${s.posicao <= 3 ? `p${s.posicao}` : ''} ${s.novo ? 'novo' : ''}">
              <span class="pos">${medalha[s.posicao - 1] ?? `${s.posicao}º`}</span>
              <span class="nome">${esc(s.nome)}<small>${s.perfeitos} ${s.perfeitos === 1 ? 'perfeito' : 'perfeitos'} · combo ${s.comboMax}</small></span>
              <span class="pts">${s.pontos.toLocaleString('pt-BR')}</span>
            </li>`).join('')
        : '<div class="vazio">Ninguém dançou ainda. Seja a primeira pessoa! 👆</div>';
    } catch {
      document.getElementById('sub').textContent = 'Sem conexão com o servidor… tentando de novo.';
    }
  }

  atualizar();
  setInterval(atualizar, 3000);
</script>
</body>
</html>
