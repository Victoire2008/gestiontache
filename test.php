<?php
$pdo = require_once 'config.php';
require_once 'tache.php';
require_once 'tachegestion.php';

$tacheGestion = new TacheGestion($pdo);

// 1. Création
$tache = new Tache(null, 'Tache 1', 'Description', 'moyenne', 'à faire');
echo "Création de la tâche...\n";
$tacheGestion->create($tache);
echo "Tâche créée avec succès.\n";

// 2. Lecture
echo "\nLecture des tâches :\n";
$taches = $tacheGestion->read();
foreach ($taches as $item) {
    echo $item->getTitre() . ' - ' 
       . $item->getDescription() . ' - ' 
       . $item->getPriorite() . ' - ' 
       . $item->getStatut() . "\n";
}

// 3. Mise à jour
if (!empty($taches)) {
    $tache = $taches[0];
    $tache->setTitre('Tache 1 mise à jour');
    echo "\nMise à jour...\n";
    $tacheGestion->update($tache);
    echo "Mise à jour réussie.\n";

    // 4. Suppression
    echo "\nSuppression...\n";
    $tacheGestion->delete($tache);
    echo "Suppression réussie.\n";
} else {
    echo "Aucune tâche trouvée.\n";
}

echo "\nTests terminés avec succès !\n";
?>