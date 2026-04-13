<?php 
require_once 'tache.php';
/**
 * class TacheGestion pour la gestion des taches 
 */
class TacheGestion{

/**
 * @var PDO $pdo
 */

private $pdo;

/**
 *  @param PDO $pdo
 */

public function __construct(PDO $pdo){ 
    $this->pdo = $pdo;
}
/**
 * @param Tache $tache
 * Création d'une tache
 */

public function create(Tache $tache){
    $stmt = $this->pdo->prepare("INSERT INTO tache (titre, description, priorite, statut) VALUES (:titre, :description, :priorite, :statut)");
    $result = $stmt->execute([
        ':titre' => $tache->getTitre(),
        ':description' => $tache->getDescription(),
        ':priorite' => $tache->getPriorite(),
        ':statut' => $tache->getStatut()
    ]);
    return $result;
}
 
/**
 * @param Tache $tache (unused, reads all)
 * @return Tache[] $taches
 * lecture et affichage de toutes les taches
 */
public function read(){
    $stmt = $this->pdo->prepare("SELECT * FROM tache");
    $stmt->execute();
    $taches = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $taches[] = new Tache($row['id'], $row['titre'], $row['description'], $row['priorite'], $row['statut']);
    }
    return $taches;
}
/**
 * @param int $id
 * @return Tache|null
 * lecture et affichage d'une tache par son ID
 */
public function readById($id) {
    $stmt = $this->pdo->prepare("SELECT * FROM tache WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return new Tache($row['id'], $row['titre'], $row['description'], $row['priorite'], $row['statut']);
    }
    return null;
}

/**
 * @param Tache $tache
 * mise a jour de la tache
 */
public function update(Tache $tache){
 $stmt = $this->pdo->prepare("UPDATE tache SET titre = :titre, description = :description, priorite = :priorite, statut = :statut WHERE id = :id");
 $result = $stmt->execute([
     ':id' => $tache->getId(),
     ':titre' => $tache->getTitre(),
     ':description' => $tache->getDescription(),
     ':priorite' => $tache->getPriorite(),
     ':statut' => $tache->getStatut()
 ]);
 return $result;
}
/**
 * @param Tache $tache
 * suppression d'une tache
 */
public function delete(Tache $tache){
    $stmt = $this->pdo->prepare("DELETE FROM tache WHERE id = :id");
    $result = $stmt->execute([':id' => $tache->getId()]);
    return $result;
}

}
?>
