<?php
$pdo = require_once 'config.php';
require_once 'tache.php';
require_once 'tachegestion.php';

$tacheGestion = new TacheGestion($pdo);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage des données
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

    // Si pas d'erreur → on crée la tâche
    if (empty($errors)) {
        $tache = new Tache(null, $titre, $description, $priorite, $statut);
        $tacheGestion->create($tache);
        header('Location: index.php?message=Tâche créée avec succès !');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Tâche — G.Tâches</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
       
        :root {
            --bg:       #000000;
            --surface:  #000000;
            --border:   #2a2a3a;
            --accent:   #d71cdd;
            --accent2:  #ff6a9b;
            --text:     #f8f8f8;
            --text1:   #efeffa  ;
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

        .page-title {
            font-family: 'Syne', sans-serif;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .page-title span { color: var(--accent); }

        .page-sub {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 36px;
        }

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

        textarea {
            resize: vertical;
            min-height: 110px;
        }

        select option {
            background: var(--surface);
        }

        /* ─── GRILLE PRIORITÉ + STATUT ─── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* ─── COMPTEUR DE CARACTÈRES ─── */
        .char-count {
            font-size: 11px;
            color: var(--muted);
            text-align: right;
            margin-top: -4px;
        }
        .char-count.warn { color: #facc15; }
        .char-count.over { color: var(--danger); }

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

        .btn-reset {
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
        .btn-reset:hover {
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
            .btn-submit, .btn-reset { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">G<span>.</span>Tâches</div>
    <a href="index.php" class="btn-back">
        ← Retour
    </a>
</header>

<main>
    <h1 class="page-title">Nouvelle <span>tâche</span></h1>
    <p class="page-sub">Remplissez les informations ci-dessous pour créer une tâche.</p>

    <!-- Affichage des erreurs -->
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <div class="form-card">
        <form method="POST" action="create.php" id="createForm">

            <!-- Titre -->
            <div class="form-group">
                <label for="titre">Titre *</label>
                <input
                    type="text"
                    id="titre"
                    name="titre"
                    placeholder="Ex: Rédiger le rapport mensuel"
                    maxlength="100"
                    value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                    class="<?= (isset($_POST['titre']) && empty(trim($_POST['titre']))) ? 'error' : '' ?>"
                >
                <div class="char-count" id="titreCount">0 / 100</div>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="Décrivez la tâche en détail (optionnel)..."
                ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Priorité + Statut -->
            <div class="form-row">
                <div class="form-group">
                    <label for="priorite">Priorité *</label>
                    <select id="priorite" name="priorite">
                        <option value="" disabled <?= empty($_POST['priorite']) ? 'selected' : '' ?>>-- Choisir --</option>
                        <option value="basse"   <?= (($_POST['priorite'] ?? '') === 'basse')   ? 'selected' : '' ?>>🟢 Basse</option>
                        <option value="moyenne" <?= (($_POST['priorite'] ?? '') === 'moyenne') ? 'selected' : '' ?>>🟡 Moyenne</option>
                        <option value="haute"   <?= (($_POST['priorite'] ?? '') === 'haute')   ? 'selected' : '' ?>>🔴 Haute</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="statut">Statut *</label>
                    <select id="statut" name="statut">
                        <option value="" disabled <?= empty($_POST['statut']) ? 'selected' : '' ?>>-- Choisir --</option>
                        <option value="à faire"  <?= (($_POST['statut'] ?? '') === 'à faire')  ? 'selected' : '' ?>>📋 À faire</option>
                        <option value="en cours" <?= (($_POST['statut'] ?? '') === 'en cours') ? 'selected' : '' ?>>⚡ En cours</option>
                        <option value="terminée" <?= (($_POST['statut'] ?? '') === 'terminée') ? 'selected' : '' ?>>✅ Terminée</option>
                    </select>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">✚ Créer la tâche</button>
                <a href="index.php" class="btn-reset">Annuler</a>
            </div>

        </form>
    </div>
</main>

<script>
    // Compteur de caractères pour le titre
    const titreInput = document.getElementById('titre');
    const titreCount = document.getElementById('titreCount');

    function updateCount() {
        const len = titreInput.value.length;
        titreCount.textContent = len + ' / 100';
        titreCount.className = 'char-count' + (len >= 100 ? ' over' : len >= 80 ? ' warn' : '');
    }

    titreInput.addEventListener('input', updateCount);
    updateCount(); // init au chargement si valeur pré-remplie
</script>

</body>
</html>