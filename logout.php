<?php
// logout.php
session_start(); // On récupère la session en cours
session_unset(); // On vide toutes les variables (id, nom, role)
session_destroy(); // On détruit complètement la session

// On renvoie l'utilisateur vers la page de connexion
header("Location: login.php");
exit;
?>