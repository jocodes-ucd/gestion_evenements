<?php
// forgot_password.php
require 'includes/db.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // 1. Vérifier si l'email existe
    $stmt = $pdo->prepare("SELECT id, nom FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Générer Token + Expiration (1 heure)
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // 3. Sauvegarder dans la BDD
        $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $update->execute([$token, $expires, $user['id']]);

        // 4. Envoyer l'email
        require_once 'includes/mailer.php';
        
        $link = "http://localhost/gestion_evenements/reset_password.php?token=" . $token;
        $subject = "Réinitialisation de votre mot de passe";
        $body = "Bonjour " . $user['nom'] . ",\n\nVous avez demandé à réinitialiser votre mot de passe.\nCliquez sur ce lien (valable 1h) :\n\n<a href='$link'>$link</a>\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez cet email.";

        if (sendEmail($email, $subject, $body)) {
            $message = "<div class='alert alert-success'>Un email de réinitialisation a été envoyé.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Erreur d'envoi de l'email.</div>";
        }
    } else {
        // Sécurité : On affiche le même message même si l'email n'existe pas (pour ne pas aider les pirates)
        $message = "<div class='alert alert-success'>Si cet email existe, un lien a été envoyé.</div>";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex align-items-center justify-content-center py-5" style="min-height: 80vh;">
    <div class="card card-custom p-4 p-md-5 text-center" style="max-width: 450px; width: 100%;">
        <div class="mb-4 text-primary"><i class="bi bi-key-fill display-1"></i></div>
        <h3 class="fw-bold mb-2">Mot de passe oublié ?</h3>
        <p class="text-muted mb-4">Entrez votre email pour recevoir un lien de réinitialisation.</p>

        <?= $message ?>

        <form method="POST">
            <div class="form-floating mb-3">
                <input type="email" name="email" class="form-control rounded-3 border-light bg-light" id="floatEmail" placeholder="name@example.com" required>
                <label for="floatEmail">Votre adresse email</label>
            </div>
            <button type="submit" class="btn btn-gradient w-100 py-3 rounded-pill fw-bold shadow-sm">Envoyer le lien</button>
        </form>
        
        <div class="mt-4 pt-3 border-top">
            <a href="login.php" class="text-decoration-none fw-bold text-muted">Retour à la connexion</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>