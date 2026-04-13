<?php 

define('DB_HOST','localhost');
define('DB_NAME','gtache');
define('DB_USER','root');
define('DB_PASSWORD','');



try{
$pdo=new PDO('mysql:host=' .DB_HOST .';dbname=' .DB_NAME,DB_USER,DB_PASSWORD);
$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)  {
    
echo "erreur de connexion".$e->getmessage();

exit;


}
return $pdo; // ou utiliser global $pdo;


?>