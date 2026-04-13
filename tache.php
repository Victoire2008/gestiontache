<?php
require_once 'config.php';

/**
 * class Tache ,elle permet de créer differentes taches
 */

class Tache{

/** 
 * @var string  champs de la tache
 */
private $id;
private $titre;
private $description;
private $priorite;
private $statut;

/**
 *$param string $id,$titre,$description,$priorite,$statut
 */
public function __construct($id,$titre,$description,$priorite,$statut )
{
    $this->id=$id;
    $this->titre=$titre;
    $this->description=$description;
    $this->priorite=$priorite;
    $this->statut=$statut;

}

/**
 * $return $id
 */

public function getId(){

return $this->id;

}

/**
 * return $titre recupération du titre de la tâche
 */
public function getTitre(){

return $this->titre;

}

/**
 * return $description recupération de la dscription de la tache 
 */
public function getDescription(){

return $this->description;

}
/**
 * return $priorite recupération de la priorité de la tache 
 */
public function getPriorite(){

return $this->priorite;

}

/**
 * return $statut recupération du statut de la tache 
 */
public function getStatut(){

return $this->statut;

}

/**
 * assignation de la valeur id a la variabe $id
 */
public function setId($id){
    $this->id=$id;
}
/**
 * assignation de la valeur titre a la variabe $titre
 */
public function setTitre($titre){
    $this->titre=$titre;

}
/**
 * assignation de la valeur description a la variabe $description
 */
public function setDescription($description){
    $this->description=$description;
}
/**
 * assignation de la valeur priorite a la variabe $priorite
 */

public function setPriorite($priorite){
    $this->priorite=$priorite;
}
/**
 * assignation de la valeur statut a la variabe $statut
 */
public function setStatut($statut){
    $this->statut=$statut;
}

}




?>