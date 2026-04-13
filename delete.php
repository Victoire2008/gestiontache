<?php
$pdo = require_once 'config.php';
require_once 'tache.php';
require_once 'tachegestion.php';

$tacheGestion = new TacheGestion($pdo);

// Vérification de l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php?message=ID invalide.');
    exit;
}

$id = (int) $_GET['id'];

// Récupération de la tâche
$tache = $tacheGestion->readById($id);

if (!$tache) {
    header('Location: index.php?message=Tâche introuvable.');
    exit;
}

// Confirmation de suppression via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {
    $tacheGestion->delete($tache);
    header('Location: index.php?message=Tâche supprimée avec succès !');
    exit;
}

// Annulation → retour index
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer la tâche #<?= $id ?> — G.Tâches</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
         :root {
            --bg:       #a685b6;
            --surface:  #1a1a24;
            --border:   #2a2a3a;
            --accent:   #d71cdd;
            --accent2:  #ff6a9b;
            --text:     #161624;
            --muted:    #ffffff;
            --success:  #4ade80;
            --warning:  #facc15;
            --danger:   #f87171;
            --radius:   14px;
        }  

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─── HEADER ─── */
        header {
            padding: 32px 48px 28px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, rgba(124,106,255,.08), rgba(255,106,155,.04));
        }

        .logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 26px;
            letter-spacing: -0.5px;
        }
        .logo span { color: var(--accent); }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 50px;
            border: 1px solid var(--border);
            transition: all .2s;
        }
        .btn-back:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        /* ─── MAIN ─── */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
        }

        .confirm-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 44px 40px;
            max-width: 520px;
            width: 100%;
            text-align: center;
        }

        /* ─── ICÔNE DANGER ─── */
        .danger-icon {
            width: 64px;
            height: 64px;
            background: rgba(248,113,113,.12);
            border: 1px solid rgba(248,113,113,.25);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 28px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(248,113,113,.3); }
            50%       { box-shadow: 0 0 0 10px rgba(248,113,113,.0); }
        }

        /* ─── TEXTES ─── */
        .confirm-title {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .confirm-title span { color: var(--danger); }

        .confirm-sub {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        /* ─── CARTE RÉCAP DE LA TÂCHE ─── */
        .task-recap {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px 20px;
            margin-bottom: 32px;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .recap-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }
        .recap-label {
            color: var(--muted);
            min-width: 90px;
            font-size: 11px;
            font-family: 'Syne', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .recap-value {
            color: var(--text);
            font-weight: 500;
        }

        /* ─── BADGES ─── */
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 50px;
        }
        .badge-haute    { background: rgba(248,113,113,.15); color: var(--danger);  }
        .badge-moyenne  { background: rgba(250,204, 21,.15); color: var(--warning); }
        .badge-basse    { background: rgba( 74,222,128,.15); color: var(--success); }
        .badge-afaire   { background: rgba(107,107,133,.15); color: var(--muted);   }
        .badge-encours  { background: rgba(250,204, 21,.15); color: var(--warning); }
        .badge-terminee { background: rgba( 74,222,128,.15); color: var(--success); }

        /* ─── AVERTISSEMENT ─── */
        .warning-msg {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(248,113,113,.08);
            border: 1px solid rgba(248,113,113,.2);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12px;
            color: var(--danger);
            margin-bottom: 28px;
        }

        /* ─── BOUTONS ─── */
        .confirm-actions {
            display: flex;
            gap: 12px;
        }

        .btn-confirm {
            flex: 1;
            background: var(--danger);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            padding: 14px 24px;
            cursor: pointer;
            transition: opacity .2s, transform .15s;
        }
        .btn-confirm:hover {
            opacity: .85;
            transform: translateY(-1px);
        }

        .btn-cancel {
            flex: 1;
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            padding: 14px 20px;
            cursor: pointer;
            transition: all .2s;
        }
        .btn-cancel:hover {
            border-color: var(--muted);
            color: var(--text);
        }

        /* ─── COUNTDOWN ─── */
        .countdown {
            font-size: 12px;
            color: var(--muted);
            margin-top: 16px;
        }
        .countdown span {
            color: var(--danger);
            font-weight: 700;
        }

        @media (max-width: 560px) {
            header { padding: 20px; flex-direction: column; align-items: stretch; gap: 16px; }
            .btn-back { justify-content: center; }
            main { padding: 24px 16px; }
            .confirm-box { padding: 28px 20px; }
            .confirm-actions { flex-direction: column-reverse; }
            .btn-confirm, .btn-cancel { width: 100%; justify-content: center; text-align: center; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">G<span>.</span>Tâches</div>
    <a href="index.php" class="btn-back">← Retour</a>
</header>

<main>
    <div class="confirm-box">

        <!-- Icône danger -->
        <div class="danger-icon">🗑️</div>

        <!-- Titre -->
        <h1 class="confirm-title">Supprimer la <span>tâche</span> ?</h1>
        <p class="confirm-sub">
            Vous êtes sur le point de supprimer définitivement cette tâche.<br>
            Cette action est <strong>irréversible</strong>.
        </p>

        <!-- Récap de la tâche -->
        <div class="task-recap">
            <div class="recap-row">
                <span class="recap-label">ID</span>
                <span class="recap-value">#<?= htmlspecialchars($tache->getId()) ?></span>
            </div>
            <div class="recap-row">
                <span class="recap-label">Titre</span>
                <span class="recap-value"><?= htmlspecialchars($tache->getTitre()) ?></span>
            </div>
            <?php if ($tache->getDescription()): ?>
            <div class="recap-row">
                <span class="recap-label">Description</span>
                <span class="recap-value" style="color:var(--muted);">
                    <?= htmlspecialchars(mb_strimwidth($tache->getDescription(), 0, 60, '...')) ?>
                </span>
            </div>
            <?php endif; ?>
            <div class="recap-row">
                <span class="recap-label">Priorité</span>
                <?php
                    $pClass = match($tache->getPriorite()) {
                        'haute'   => 'badge-haute',
                        'moyenne' => 'badge-moyenne',
                        default   => 'badge-basse'
                    };
                ?>
                <span class="badge <?= $pClass ?>"><?= htmlspecialchars($tache->getPriorite()) ?></span>
            </div>
            <div class="recap-row">
                <span class="recap-label">Statut</span>
                <?php
                    $sClass = match($tache->getStatut()) {
                        'en cours' => 'badge-encours',
                        'terminée' => 'badge-terminee',
                        default    => 'badge-afaire'
                    };
                ?>
                <span class="badge <?= $sClass ?>"><?= htmlspecialchars($tache->getStatut()) ?></span>
            </div>
        </div>

        <!-- Avertissement -->
        <div class="warning-msg">
            ⚠️ Cette suppression est permanente et ne peut pas être annulée.
        </div>

        <!-- Formulaire de confirmation -->
        <form method="POST" action="delete.php?id=<?= $id ?>">
            <div class="confirm-actions">
                <button type="submit" name="annuler" class="btn-cancel">
                    Annuler
                </button>
                <button type="submit" name="confirmer" class="btn-confirm" id="confirmBtn">
                    🗑️ Supprimer
                </button>
            </div>
        </form>

        <p class="countdown" id="countdown"></p>

    </div>
</main>

<script>
    // Compte à rebours avant activation du bouton supprimer
    const confirmBtn = document.getElementById('confirmBtn');
    const countdownEl = document.getElementById('countdown');
    let seconds = 3;

    confirmBtn.disabled = true;
    confirmBtn.style.opacity = '0.4';
    confirmBtn.style.cursor = 'not-allowed';

    const timer = setInterval(() => {
        countdownEl.innerHTML = `Le bouton sera actif dans <span>${seconds}</span> seconde${seconds > 1 ? 's' : ''}...`;
        seconds--;

        if (seconds < 0) {
            clearInterval(timer);
            confirmBtn.disabled = false;
            confirmBtn.style.opacity = '1';
            confirmBtn.style.cursor = 'pointer';
            countdownEl.textContent = '';
        }
    }, 1000);
</script>

</body>
</html>