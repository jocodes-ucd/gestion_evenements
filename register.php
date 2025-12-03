<?php
// register.php
require 'includes/db.php';
// On n'inclut PAS header.php tout de suite car on a de la logique de redirection
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);
    $pass_confirm = trim($_POST['password_confirm']);

    if (!empty($nom) && !empty($email) && !empty($pass)) {
        if ($pass === $pass_confirm) {
            // 1. Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() == 0) {
                // 2. Hacher le mot de passe (Sécurité)
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                
                // 3. Insérer le nouvel utilisateur (Rôle 'user' par défaut)
                $insert = $pdo->prepare("INSERT INTO users (nom, email, mot_de_passe, role) VALUES (?, ?, ?, 'user')");
                if ($insert->execute([$nom, $email, $hash])) {
                    // Succès -> Redirection vers Login
                    header("Location: login.php?msg=registered");
                    exit;
                } else {
                    $error = "Erreur lors de l'inscription.";
                }
            } else {
                $error = "Cet email est déjà utilisé.";
            }
        } else {
            $error = "Les mots de passe ne correspondent pas.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-white text-center py-4">
                <h3 class="mb-0 fw-bold text-primary">🚀 Créer un compte</h3>
                <p class="text-muted mb-0">Rejoignez-nous pour gérer vos événements</p>
            </div>
            <div class="card-body p-5">
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted">Nom complet</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-person"></i></span>
                            <input type="text" name="nom" class="form-control bg-light border-0" placeholder="Jean Dupont" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Adresse Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control bg-light border-0" placeholder="jean@exemple.com" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Mot de passe</label>
                            <input type="password" name="password" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Confirmer</label>
                            <input type="password" name="password_confirm" class="form-control bg-light border-0" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fs-5 mt-3 shadow-sm">S'inscrire</button>
                </form>
            </div>
            <div class="card-footer text-center bg-light py-3">
                <span class="text-muted">Déjà un compte ?</span> <a href="login.php" class="fw-bold">Se connecter</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>