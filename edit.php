<?php
$pdo = require_once 'config.php';
require_once 'tache.php';
require_once 'tachegestion.php';

$tacheGestion = new TacheGestion($pdo);
$errors = [];

// Vérification de l'ID en GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php?message=ID invalide.');
    exit;
}

$id = (int) $_GET['id'];

// Récupération de la tâche existante
$tache = $tacheGestion->readById($id);

if (!$tache) {
    header('Location: index.php?message=Tâche introuvable.');
    exit;
}

// Traitement du formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre       = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priorite    = $_POST['priorite'] ?? '';
    $statut      = $_POST['statut'] ?? '';

    // Validations
    if (empty($titre)) {
        $errors[] = "Le titre est obligatoire.";
    } elseif (strlen($titre) > 100) {
        $errors[] = "Le titre ne doit pas dépasser 100 caractères.";
    }

    $prioritesValides = ['basse', 'moyenne', 'haute'];
    if (!in_array($priorite, $prioritesValides)) {
        $errors[] = "La priorité sélectionnée est invalide.";
    }

    $statutsValides = ['à faire', 'en cours', 'terminée'];
    if (!in_array($statut, $statutsValides)) {
        $errors[] = "Le statut sélectionné est invalide.";
    }

    // Si pas d'erreur → on met à jour
    if (empty($errors)) {
        $tache->setTitre($titre);
        $tache->setDescription($description);
        $tache->setPriorite($priorite);
        $tache->setStatut($statut);

        $tacheGestion->update($tache);
        header('Location: index.php?message=Tâche mise à jour avec succès !');
        exit;
    } else {
        // On garde les nouvelles valeurs saisies même en cas d'erreur
        $tache->setTitre($titre);
        $tache->setDescription($description);
        $tache->setPriorite($priorite);
        $tache->setStatut($statut);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la tâche #<?= $id ?> — G.Tâches</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
       :root {
            --bg:       #000000;
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
            max-width: 680px;
            margin: 0 auto;
            padding: 48px 24px;
        }

        /* ─── BADGE ID ─── */
        .id-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(124,106,255,.12);
            border: 1px solid rgba(124,106,255,.25);
            color: var(--accent);
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 50px;
            margin-bottom: 12px;
        }

        .page-title {
            font-family: 'Syne', sans-serif;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .page-title span { color: var(--accent2); }

        .page-sub {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 36px;
        }

        /* ─── CARTE INFO ORIGINALE ─── */
        .original-info {
            background: rgba(124,106,255,.06);
            border: 1px solid rgba(124,106,255,.15);
            border-radius: var(--radius);
            padding: 14px 20px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--muted);
        }
        .original-info strong { color: var(--text); }

        /* ─── ERREURS ─── */
        .errors {
            background: rgba(248,113,113,.1);
            border: 1px solid rgba(248,113,113,.3);
            border-radius: var(--radius);
            padding: 16px 20px;
            margin-bottom: 28px;
        }
        .errors p {
            color: var(--danger);
            font-size: 13px;
            margin-bottom: 4px;
        }
        .errors p:last-child { margin-bottom: 0; }
        .errors p::before { content: "✕  "; }

        /* ─── FORMULAIRE ─── */
        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 36px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            font-family: 'Syne', sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--muted);
        }

        input[type="text"],
        textarea,
        select {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            padding: 12px 16px;
            width: 100%;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(124,106,255,.15);
        }

        input[type="text"].error,
        select.error {
            border-color: var(--danger);
        }

        /* Champ modifié → bordure accent2 */
        input.modified,
        textarea.modified,
        select.modified {
            border-color: rgba(255,106,155,.5);
        }

        textarea {
            resize: vertical;
            min-height: 110px;
        }

        select option { background: var(--surface); }

        /* ─── GRILLE PRIORITÉ + STATUT ─── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* ─── COMPTEUR ─── */
        .char-count {
            font-size: 11px;
            color: var(--muted);
            text-align: right;
            margin-top: -4px;
        }
        .char-count.warn { color: var(--warning); }
        .char-count.over { color: var(--danger); }

        /* ─── INDICATEUR DE MODIFICATIONS ─── */
        .change-indicator {
            display: none;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--accent2);
            background: rgba(255,106,155,.08);
            border: 1px solid rgba(255,106,155,.2);
            border-radius: 8px;
            padding: 8px 14px;
        }
        .change-indicator.visible { display: flex; }

        /* ─── BOUTONS ─── */
        .form-actions {
            display: flex;
            gap: 12px;
            padding-top: 8px;
        }

        .btn-submit {
            flex: 1;
            background: var(--accent);
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
        .btn-submit:hover {
            opacity: .85;
            transform: translateY(-1px);
        }

        .btn-cancel {
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .btn-cancel:hover {
            border-color: var(--muted);
            color: var(--text);
        }

        @media (max-width: 600px) {
            header { padding: 20px; flex-direction: column; align-items: stretch; gap: 16px; }
            .btn-back { justify-content: center; }
            main { padding: 24px 16px; }
            .form-card { padding: 24px 20px; }
            .form-row { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column; }
            .btn-submit, .btn-cancel { width: 100%; justify-content: center; text-align: center; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">G<span>.</span>Tâches</div>
    <a href="index.php" class="btn-back">← Retour</a>
</header>

<main>

    <div class="id-badge">✏️ Tâche #<?= $id ?></div>
    <h1 class="page-title">Modifier la <span>tâche</span></h1>
    <p class="page-sub">Modifiez les champs souhaités puis sauvegardez.</p>

    <!-- Info tâche originale -->
    <div class="original-info">
        📌 Titre original : <strong><?= htmlspecialchars($tache->getTitre()) ?></strong>
    </div>

    <!-- Erreurs -->
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <div class="form-card">
        <form method="POST" action="edit.php?id=<?= $id ?>" id="editForm">

            <!-- Titre -->
            <div class="form-group">
                <label for="titre">Titre *</label>
                <input
                    type="text"
                    id="titre"
                    name="titre"
                    placeholder="Titre de la tâche"
                    maxlength="100"
                    value="<?= htmlspecialchars($tache->getTitre()) ?>"
                    data-original="<?= htmlspecialchars($tache->getTitre()) ?>"
                >
                <div class="char-count" id="titreCount">0 / 100</div>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="Description de la tâche..."
                    data-original="<?= htmlspecialchars($tache->getDescription()) ?>"
                ><?= htmlspecialchars($tache->getDescription()) ?></textarea>
            </div>

            <!-- Priorité + Statut -->
            <div class="form-row">
                <div class="form-group">
                    <label for="priorite">Priorité *</label>
                    <select id="priorite" name="priorite" data-original="<?= htmlspecialchars($tache->getPriorite()) ?>">
                        <option value="basse"   <?= $tache->getPriorite() === 'basse'   ? 'selected' : '' ?>>🟢 Basse</option>
                        <option value="moyenne" <?= $tache->getPriorite() === 'moyenne' ? 'selected' : '' ?>>🟡 Moyenne</option>
                        <option value="haute"   <?= $tache->getPriorite() === 'haute'   ? 'selected' : '' ?>>🔴 Haute</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="statut">Statut *</label>
                    <select id="statut" name="statut" data-original="<?= htmlspecialchars($tache->getStatut()) ?>">
                        <option value="à faire"  <?= $tache->getStatut() === 'à faire'  ? 'selected' : '' ?>>📋 À faire</option>
                        <option value="en cours" <?= $tache->getStatut() === 'en cours' ? 'selected' : '' ?>>⚡ En cours</option>
                        <option value="terminée" <?= $tache->getStatut() === 'terminée' ? 'selected' : '' ?>>✅ Terminée</option>
                    </select>
                </div>
            </div>

            <!-- Indicateur de modifications -->
            <div class="change-indicator" id="changeIndicator">
                🖊️ Des modifications ont été détectées
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">💾 Sauvegarder</button>
                <a href="index.php" class="btn-cancel">Annuler</a>
            </div>

        </form>
    </div>

</main>

<script>
    const fields = document.querySelectorAll('[data-original]');
    const indicator = document.getElementById('changeIndicator');

    // Compteur titre
    const titreInput = document.getElementById('titre');
    const titreCount = document.getElementById('titreCount');

    function updateCount() {
        const len = titreInput.value.length;
        titreCount.textContent = len + ' / 100';
        titreCount.className = 'char-count' + (len >= 100 ? ' over' : len >= 80 ? ' warn' : '');
    }
    titreInput.addEventListener('input', updateCount);
    updateCount();

    // Détection des modifications
    function checkChanges() {
        let hasChange = false;
        fields.forEach(field => {
            const original = field.dataset.original;
            const current  = field.value;
            const changed  = current !== original;
            if (changed) hasChange = true;
            field.classList.toggle('modified', changed);
        });
        indicator.classList.toggle('visible', hasChange);
    }

    fields.forEach(f => f.addEventListener('input', checkChanges));
    fields.forEach(f => f.addEventListener('change', checkChanges));
    checkChanges(); // init au chargement
</script>

</body>
</html>