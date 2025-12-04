<?php
require 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = $_SESSION['user_id'];
    $old = $_POST['old_pass'];
    $new = $_POST['new_pass'];
    $conf = $_POST['confirm_pass'];

    // 1. Vérifier l'ancien mot de passe
    $stmt = $pdo->prepare("SELECT mot_de_passe FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    $currentHash = $stmt->fetchColumn();

    if (password_verify($old, $currentHash)) {
        if ($new === $conf) {
            // 2. Mettre à jour
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
            $upd->execute([$newHash, $uid]);
            
            // Succès : on renvoie vers le profil avec un message (tu peux ajouter la gestion du message dans profile.php comme d'habitude)
            echo "<script>alert('Mot de passe modifié avec succès !'); window.location.href='profile.php';</script>";
        } else {
            echo "<script>alert('Les nouveaux mots de passe ne correspondent pas.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Ancien mot de passe incorrect.'); window.history.back();</script>";
    }
}
?>