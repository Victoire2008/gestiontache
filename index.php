<?php

$pdo = require_once 'config.php';
require_once 'tache.php';
require_once 'tachegestion.php';
 
$tacheGestion = new TacheGestion($pdo);
$taches = $tacheGestion->read();
 
// Filtrage par statut (optionnel)
$filtreStatut = isset($_GET['statut']) ? $_GET['statut'] : 'tous';
if ($filtreStatut !== 'tous') {
    // fonction fléchée pour filtrer les tâches selon le statut 
    $taches = array_filter($taches, fn($t) => $t->getStatut() === $filtreStatut);
}
 
// Message de succès/suppression
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gestion des tâches</title>
        <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #000000;
            --surface:  #1a1a24;
            --border:   #2a2a3a;
            --accent:   #d71cdd;
            --accent2:  #ff6a9b;
            --text:     #e7e7e7;
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
            gap: 16px;
            background: linear-gradient(135deg, rgba(124,106,255,.08), rgba(255,106,155,.04));
        }
 
        .logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 26px;
            letter-spacing: -0.5px;
        }
        .logo span { color: var(--accent); }
 
        .btn-new {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent);
            color: #fff;
            text-decoration: none;
            font-family: 'Syne', sans-serif;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 22px;
            border-radius: 50px;
            transition: opacity .2s, transform .15s;
        }
        .btn-new:hover { opacity: .85; transform: translateY(-1px); }
 
        /* ─── MAIN ─── */
        main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 24px;
        }
 
        /* ─── STATS ─── */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 24px;
        }
        .stat-card .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 8px;
        }
        .stat-card .value {
            font-family: 'Syne', sans-serif;
            font-size: 32px;
            font-weight: 800;
            margin-top: 20px;
        }
        .stat-card.total   .value { color: var(--accent); }
        .stat-card.todo    .value { color: var(--muted); }
        .stat-card.inprogress .value { color: var(--warning); }
        .stat-card.done    .value { color: var(--success); }
 
        /* ─── FILTRES ─── */
        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }
 
        .filter-btn {
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 7px 18px;
            border-radius: 50px;
            border: 1px solid var(--border);
            color: var(--muted);
            background: transparent;
            transition: all .2s;
            white-space: nowrap;
        }
        .filter-btn:hover,
        .filter-btn.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
 
        /* ─── MESSAGE ─── */
        .alert {
            background: rgba(74,222,128,.1);
            border: 1px solid rgba(74,222,128,.3);
            color: var(--success);
            padding: 12px 20px;
            border-radius: var(--radius);
            margin-bottom: 28px;
            font-size: 14px;
        }
 
        /* ─── TABLE ─── */
        .table-wrap {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow-x: auto;
            margin-top: 50px;
            -webkit-overflow-scrolling: touch;
        }
 
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
 
        thead tr {
            border-bottom: 1px solid var(--border);
        }
        thead th {
            font-family: 'Syne', sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--muted);
            padding: 16px 20px;
            text-align: left;
        }
 
        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(124,106,255,.05); }
 
        tbody td {
            padding: 16px 20px;
            vertical-align: middle;
        }
 
        .td-titre {
            font-weight: 500;
            font-size: 15px;
        }
        .td-desc {
            color: var(--muted);
            font-size: 13px;
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
 
        /* ─── BADGES ─── */
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 50px;
            letter-spacing: .4px;
            white-space: nowrap;
        }
 
        .badge-haute    { background: rgba(248,113,113,.15); color: var(--danger);  }
        .badge-moyenne  { background: rgba(250,204, 21,.15); color: var(--warning); }
        .badge-basse    { background: rgba( 74,222,128,.15); color: var(--success); }
 
        .badge-afaire   { background: rgba(107,107,133,.15); color: var(--muted);   }
        .badge-encours  { background: rgba(250,204, 21,.15); color: var(--warning); }
        .badge-terminee { background: rgba( 74,222,128,.15); color: var(--success); }
 
        /* ─── ACTIONS ─── */
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
 
        .btn-edit, .btn-del {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 8px;
            text-decoration: none;
            transition: opacity .2s;
            white-space: nowrap;
        }
        .btn-edit {
            background: rgba(124,106,255,.15);
            color: var(--accent);
            border: 1px solid rgba(124,106,255,.25);
        }
        .btn-del {
            background: rgba(248,113,113,.12);
            color: var(--danger);
            border: 1px solid rgba(248,113,113,.2);
        }
        .btn-edit:hover, .btn-del:hover { opacity: .7; }
 
        /* ─── VIDE ─── */
        .empty {
            text-align: center;
            padding: 60px 24px;
            color: var(--muted);
        }
        .empty svg { opacity: .3; margin-bottom: 16px; }
        .empty p { font-size: 15px; }
        .empty a { color: var(--accent); text-decoration: none; }
 
        @media (max-width: 800px) {
            .td-desc, thead th:nth-child(3) { display: none; }
            table { min-width: 500px; }
            .stats { grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); }
        }

        @media (max-width: 600px) {
            header { padding: 20px; flex-direction: column; align-items: stretch; gap: 16px; }
            .btn-new { justify-content: center; }
            main { padding: 24px 16px; }
            .stats { grid-template-columns: 1fr 1fr; }
            .actions { flex-direction: column; gap: 5px; }
        }

        @media (max-width: 400px) {
            .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    
<header>

    <div class="logo">G<span>.</span>Tâches</div>
    <a href="create.php" class="btn-new">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle tâche
    </a>
</header>

    <main>
 
    <?php if ($message): ?>
        <div class="alert">✓ <?= $message ?></div>
    <?php endif; ?>
 
    <?php
        // Calcul des stats
        //fonction fléchée pour filtrer les tâches selon le statut
        $allTaches  = $tacheGestion->read();
        $total      = count($allTaches);
        $nbAfaire   = count(array_filter($allTaches, fn($t) => $t->getStatut() === 'à faire'));
        $nbEnCours  = count(array_filter($allTaches, fn($t) => $t->getStatut() === 'en cours'));
        $nbTerminee = count(array_filter($allTaches, fn($t) => $t->getStatut() === 'terminée'));
    ?>
 
    <!-- Stats -->
    <div class="stats">
        <div class="stat-card total">
            <div class="label">Total</div>
            <div class="value"><?= $total ?></div>
        </div>
        <div class="stat-card todo">
            <div class="label">À faire</div>
            <div class="value"><?= $nbAfaire ?></div>
        </div>
        <div class="stat-card inprogress">
            <div class="label">En cours</div>
            <div class="value"><?= $nbEnCours ?></div>
        </div>
        <div class="stat-card done">
            <div class="label">Terminées</div>
            <div class="value"><?= $nbTerminee ?></div>
        </div>
    </div>
 
    <!-- Filtres -->
    <div class="filters">
        <a href="index.php" class="filter-btn <?= $filtreStatut === 'tous'     ? 'active' : '' ?>">Toutes</a>
        <a href="?statut=à faire"  class="filter-btn <?= $filtreStatut === 'à faire'  ? 'active' : '' ?>">À faire</a>
        <a href="?statut=en cours" class="filter-btn <?= $filtreStatut === 'en cours' ? 'active' : '' ?>">En cours</a>
        <a href="?statut=terminée" class="filter-btn <?= $filtreStatut === 'terminée' ? 'active' : '' ?>">Terminées</a>
    </div>
 
    <!-- Tableau des tâches -->
    <div class="table-wrap">
        <?php if (empty($taches)): ?>
            <div class="empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 12l2 2 4-4"/></svg>
                <p>Aucune tâche trouvée. <a href="create.php">Créer une tâche →</a></p>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Priorité</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($taches as $t): ?>
                <?php
                    // Classes des badges priorité
                    $pClass = match($t->getPriorite()) {
                        'haute'   => 'badge-haute',
                        'moyenne' => 'badge-moyenne',
                        default   => 'badge-basse'
                    };
                    // Classes des badges statut
                    $sClass = match($t->getStatut()) {
                        'en cours' => 'badge-encours',
                        'terminée' => 'badge-terminee',
                        default    => 'badge-afaire'
                    };
                ?>
                <tr>
                    <td style="color:var(--muted);font-size:13px;"><?= htmlspecialchars($t->getId()) ?></td>
                    <td class="td-titre"><?= htmlspecialchars($t->getTitre()) ?></td>
                    <td class="td-desc"><?= htmlspecialchars($t->getDescription()) ?></td>
                    <td><span class="badge <?= $pClass ?>"><?= htmlspecialchars($t->getPriorite()) ?></span></td>
                    <td><span class="badge <?= $sClass ?>"><?= htmlspecialchars($t->getStatut()) ?></span></td>
                    <td>
                        <div class="actions">
                            <a href="edit.php?id=<?= $t->getId() ?>" class="btn-edit">
                                Éditer
                            </a>
                            <a href="delete.php?id=<?= $t->getId() ?>" class="btn-del"
                               onclick="return confirm('Supprimer cette tâche ?')">
                                 Supprimer
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
 
</main>
</body>
</html>