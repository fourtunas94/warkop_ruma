<?php
helper('app_setting');

$storeName = app_setting('store_name', 'ARZANA');
$storeLogo = app_setting('store_logo', '');

$logoSrc = ! empty($storeLogo)
    ? base_url($storeLogo)
    : base_url('assets/img/logo_k.png');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Pesanan — <?= esc($storeName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bg: #020617;
            --text: #ffffff;
            --muted: #94a3b8;
            --border: rgba(255,255,255,.10);
            --card: rgba(255,255,255,.055);
            --sky: #0ea5e9;
            --purple: #7c3aed;
            --emerald: #22c55e;
            --amber: #f59e0b;
            --rose: #f43f5e;
            --gradient: linear-gradient(135deg, #0ea5e9, #7c3aed);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(14,165,233,.24), transparent 28rem),
                radial-gradient(circle at top right, rgba(124,58,237,.22), transparent 30rem),
                linear-gradient(135deg, #020617 0%, #07111f 50%, #020617 100%);
            color: var(--text);
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 44px 44px;
            z-index: 0;
        }

        .page {
            position: relative;
            z-index: 1;
            width: min(1180px, calc(100% - 28px));
            margin: 0 auto;
            padding: 26px 0;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo {
            width: 58px;
            height: 58px;
            border-radius: 22px;
            background: rgba(255,255,255,.06);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 0 40px rgba(14,165,233,.16);
        }

        .logo img {
            width: 46px;
            height: 46px;
            object-fit: contain;
        }

        .brand h1 {
            font-size: clamp(24px, 3vw, 42px);
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: -.05em;
        }

        .brand p {
            color: var(--muted);
            margin-top: 5px;
            font-size: 14px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            background: rgba(255,255,255,.055);
            border: 1px solid var(--border);
            padding: 11px 15px;
            font-size: 13px;
            color: #cbd5e1;
        }

        .btn-sound {
            border: 0;
            cursor: pointer;
            border-radius: 999px;
            background: var(--gradient);
            color: white;
            padding: 12px 17px;
            font-size: 13px;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 16px 40px rgba(14,165,233,.22);
        }

        .hero {
            position: relative;
            overflow: hidden;
            border-radius: 34px;
            background: rgba(255,255,255,.045);
            border: 1px solid var(--border);
            padding: 24px;
            margin-bottom: 20px;
        }

        .hero::before {
            content: '';
            position: absolute;
            right: -90px;
            top: -120px;
            width: 260px;
            height: 260px;
            background: rgba(14,165,233,.18);
            filter: blur(44px);
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            background: rgba(34,197,94,.10);
            border: 1px solid rgba(34,197,94,.20);
            color: #86efac;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 14px;
        }

        .badge span {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--emerald);
            box-shadow: 0 0 0 6px rgba(34,197,94,.10);
        }

        .hero h2 {
            font-size: clamp(30px, 4vw, 58px);
            line-height: 1.04;
            letter-spacing: -.06em;
            max-width: 840px;
            font-weight: 900;
        }

        .hero p {
            margin-top: 12px;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.6;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .panel {
            border-radius: 30px;
            background: rgba(255,255,255,.045);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .panel-head {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .panel-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
            font-size: 18px;
        }

        .panel-title i {
            width: 38px;
            height: 38px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .waiting-icon {
            background: rgba(245,158,11,.10);
            color: #fbbf24;
            border: 1px solid rgba(245,158,11,.20);
        }

        .ready-icon {
            background: rgba(34,197,94,.10);
            color: #86efac;
            border: 1px solid rgba(34,197,94,.20);
        }

        .count {
            min-width: 34px;
            height: 34px;
            border-radius: 999px;
            background: rgba(255,255,255,.08);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
        }

        .list {
            padding: 14px;
            display: grid;
            gap: 12px;
            min-height: 260px;
        }

        .order-card {
            border-radius: 24px;
            padding: 17px;
            background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.10);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            animation: pop .25s ease;
        }

        .order-card.ready {
            background:
                radial-gradient(circle at top right, rgba(34,197,94,.20), transparent 10rem),
                rgba(34,197,94,.075);
            border-color: rgba(34,197,94,.25);
            box-shadow: 0 18px 50px rgba(34,197,94,.10);
        }

        .queue {
            font-size: clamp(28px, 4vw, 46px);
            line-height: 1;
            font-weight: 900;
            letter-spacing: -.05em;
        }

        .meta {
            color: var(--muted);
            font-size: 13px;
            margin-top: 7px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .status {
            white-space: nowrap;
            font-size: 12px;
            font-weight: 900;
            padding: 8px 11px;
            border-radius: 999px;
        }

        .status.waiting {
            color: #fbbf24;
            background: rgba(245,158,11,.10);
            border: 1px solid rgba(245,158,11,.20);
        }

        .status.ready {
            color: #86efac;
            background: rgba(34,197,94,.12);
            border: 1px solid rgba(34,197,94,.24);
        }

        .empty {
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            text-align: center;
            font-size: 14px;
            line-height: 1.5;
        }

        .last-call {
            margin-top: 18px;
            border-radius: 30px;
            background: linear-gradient(135deg, rgba(14,165,233,.18), rgba(124,58,237,.16));
            border: 1px solid rgba(255,255,255,.12);
            padding: 18px 20px;
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .last-call.show {
            display: flex;
        }

        .last-call h3 {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .last-call strong {
            font-size: clamp(28px, 4vw, 48px);
            line-height: 1;
        }

        .last-call .speaker {
            width: 54px;
            height: 54px;
            border-radius: 22px;
            background: rgba(255,255,255,.10);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            animation: pulse 1s infinite;
        }

        @keyframes pop {
            from {
                opacity: 0;
                transform: translateY(8px) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.08);
            }
        }

        @media (max-width: 820px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .actions {
                width: 100%;
                justify-content: space-between;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .hero {
                border-radius: 26px;
                padding: 18px;
            }

            .panel {
                border-radius: 24px;
            }
        }
    </style>
</head>

<body>

<div class="page">
    <header class="header">
        <div class="brand">
            <div class="logo">
                <img src="<?= esc($logoSrc) ?>" alt="<?= esc($storeName) ?>">
            </div>

            <div>
                <h1><?= esc($storeName) ?></h1>
                <p>Monitoring antrean pesanan hari ini</p>
            </div>
        </div>

        <div class="actions">
            <button type="button" class="btn-sound" id="btn-sound" onclick="enableSound()">
                <i class="fa-solid fa-volume-high"></i>
                Aktifkan Suara
            </button>

            <div class="pill">
                <i class="fa-regular fa-clock"></i>
                <span id="clock">--:--:--</span>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <div class="badge">
                <span></span>
                Live Order Monitor
            </div>

            <h2>Silakan pantau nomor antrian pesanan Anda</h2>

            <p>
                Pesanan yang sudah siap akan muncul di kolom <strong>Siap Diantarkan</strong> dan akan dibacakan otomatis melalui suara monitor.
            </p>
        </div>
    </section>

    <div class="grid">
        <section class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="fa-solid fa-kitchen-set waiting-icon"></i>
                    Sedang Disiapkan
                </div>
                <div class="count" id="count-waiting">0</div>
            </div>

            <div class="list" id="waiting-list">
                <div class="empty">Memuat data pesanan...</div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="fa-solid fa-bell-concierge ready-icon"></i>
                    Siap Diantarkan
                </div>
                <div class="count" id="count-ready">0</div>
            </div>

            <div class="list" id="ready-list">
                <div class="empty">Belum ada pesanan siap.</div>
            </div>
        </section>
    </div>

    <div class="last-call" id="last-call">
        <div>
            <h3>Terakhir dipanggil</h3>
            <strong id="last-call-number">A000</strong>
        </div>

        <div class="speaker">
            <i class="fa-solid fa-volume-high"></i>
        </div>
    </div>
</div>

<script>
    const DATA_URL = <?= json_encode(base_url('monitor/data')) ?>;

    let soundEnabled = localStorage.getItem('arzana_monitor_sound') === '1';
    let spokenReadyIds = JSON.parse(localStorage.getItem('arzana_monitor_spoken_ready') || '[]');

    updateSoundButton();

    function updateClock() {
        document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    updateClock();
    setInterval(updateClock, 1000);

    function enableSound() {
        soundEnabled = true;
        localStorage.setItem('arzana_monitor_sound', '1');
        updateSoundButton();

        speakText('Suara monitoring pesanan aktif.');
    }

    function updateSoundButton() {
        const btn = document.getElementById('btn-sound');

        if (soundEnabled) {
            btn.innerHTML = '<i class="fa-solid fa-volume-high"></i> Suara Aktif';
        } else {
            btn.innerHTML = '<i class="fa-solid fa-volume-high"></i> Aktifkan Suara';
        }
    }

    function escapeHTML(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function speakText(text) {
        if (!soundEnabled) {
            return;
        }

        try {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 0.92;
            utterance.pitch = 1.05;
            utterance.volume = 1;

            window.speechSynthesis.cancel();
            window.speechSynthesis.speak(utterance);
        } catch (err) {
            console.error('TTS gagal:', err);
        }
    }

    function renderCard(order) {
        const isReady = order.tone === 'ready';

        return `
            <div class="order-card ${isReady ? 'ready' : ''}">
                <div>
                    <div class="queue">${escapeHTML(order.nomor_antrian || '-')}</div>
                    <div class="meta">
                        <span><i class="fa-solid fa-chair"></i> Meja ${escapeHTML(order.nomor_meja || '-')}</span>
                        <span><i class="fa-regular fa-clock"></i> ${escapeHTML(order.waktu || '-')}</span>
                    </div>
                </div>

                <div class="status ${isReady ? 'ready' : 'waiting'}">
                    ${escapeHTML(order.label || '-')}
                </div>
            </div>
        `;
    }

    function renderList(id, orders, emptyText) {
        const el = document.getElementById(id);

        if (orders.length === 0) {
            el.innerHTML = `<div class="empty">${emptyText}</div>`;
            return;
        }

        el.innerHTML = orders.map(renderCard).join('');
    }

    function handleReadyTTS(readyOrders) {
        readyOrders.forEach(order => {
            const id = Number(order.id || 0);

            if (!id || spokenReadyIds.includes(id)) {
                return;
            }

            spokenReadyIds.push(id);

            const nomor = order.nomor_antrian || '-';

            document.getElementById('last-call').classList.add('show');
            document.getElementById('last-call-number').textContent = nomor;

            speakText(`Pesanan nomor antrian ${nomor}, meja ${order.nomor_meja}, siap diantarkan.`);

            if (spokenReadyIds.length > 100) {
                spokenReadyIds = spokenReadyIds.slice(-60);
            }

            localStorage.setItem('arzana_monitor_spoken_ready', JSON.stringify(spokenReadyIds));
        });
    }

    async function loadMonitor() {
        try {
            const res = await fetch(DATA_URL + '?t=' + Date.now(), {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const json = await res.json();

            if (!json.success) {
                return;
            }

            const data = Array.isArray(json.data) ? json.data : [];

            const waiting = data.filter(item => item.tone !== 'ready');
            const ready = data.filter(item => item.tone === 'ready');

            document.getElementById('count-waiting').textContent = waiting.length;
            document.getElementById('count-ready').textContent = ready.length;

            renderList('waiting-list', waiting, 'Belum ada pesanan yang sedang disiapkan.');
            renderList('ready-list', ready, 'Belum ada pesanan siap diantarkan.');

            handleReadyTTS(ready);
        } catch (err) {
            console.error('Gagal memuat monitor:', err);
        }
    }

    loadMonitor();
    setInterval(loadMonitor, 3000);
</script>

</body>
</html>