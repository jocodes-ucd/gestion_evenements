<?php
// reset_password.php
require 'includes/db.php';
$message = "";
$validToken = false;

// 1. Vérifier le token dans l'URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // On cherche un user avec ce token ET une date d'expiration future
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $validToken = true;
    } else {
        $message = "<div class='alert alert-danger'>Ce lien est invalide ou a expiré. <a href='forgot_password.php'>Renvoyer un lien</a></div>";
    }
} else {
    header("Location: login.php");
    exit;
}

// 2. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $pass = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if ($pass === $confirm) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        
        // Mise à jour du mot de passe et suppression du token
        $update = $pdo->prepare("UPDATE users SET mot_de_passe = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        if ($update->execute([$hash, $user['id']])) {
            header("Location: login.php?msg=password_reset");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Erreur technique.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Les mots de passe ne correspondent pas.</div>";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex align-items-center justify-content-center py-5" style="min-height: 80vh;">
    <div class="card card-custom p-4 p-md-5" style="max-width: 450px; width: 100%;">
        <div class="text-center mb-4">
            <h3 class="fw-bold">Nouveau mot de passe</h3>
        </div>

        <?= $message ?>

        <?php if ($validToken): ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">NOUVEAU MOT DE PASSE</label>
                <input type="password" name="password" class="form-control bg-light border-0 py-2" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">CONFIRMER</label>
                <input type="password" name="confirm" class="form-control bg-light border-0 py-2" required>
            </div>
            <button type="submit" class="btn btn-gradient w-100 py-3 rounded-pill fw-bold shadow-sm">
                Réinitialiser
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>