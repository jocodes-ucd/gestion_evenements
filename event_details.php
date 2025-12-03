<?php
// event_details.php
require 'includes/db.php';
include 'includes/header.php';

// 1. Vérification ID
if (!isset($_GET['id'])) { 
    die("<div class='container mt-5'><div class='alert alert-danger'>ID manquant.</div></div>"); 
}
$id = $_GET['id'];

// 2. Récupérer l'événement
$stmt = $pdo->prepare("SELECT e.*, c.nom as cat_nom FROM evenements e LEFT JOIN categories c ON e.categorie_id = c.id WHERE e.id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) { 
    die("<div class='container mt-5'><div class='alert alert-danger'>Événement introuvable.</div></div>"); 
}

// 3. Traitement du formulaire d'inscription
$message = "";

// On ne traite le formulaire que si c'est un POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // SÉCURITÉ BACKEND : On vérifie si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        die("Erreur : Vous devez être connecté pour vous inscrire.");
    }

    // On récupère les infos directement de la SESSION (Impossible à falsifier par l'utilisateur)
    $nom = $_SESSION['nom'];
    $email = $_SESSION['email']; // Si login.php n'est pas corrigé, ceci sera vide !

    // Vérif places
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE evenement_id = ?");
    $countStmt->execute([$id]);
    $actuel = $countStmt->fetchColumn();

    if ($actuel < $event['nb_max_participants']) {
        // Vérif doublon
        $check = $pdo->prepare("SELECT id FROM inscriptions WHERE evenement_id = ? AND email_participant = ?");
        $check->execute([$id, $email]);
        
        if ($check->rowCount() == 0) {
            // Insertion BDD
            $pdo->prepare("INSERT INTO inscriptions (evenement_id, nom_participant, email_participant) VALUES (?, ?, ?)")
                ->execute([$id, $nom, $email]);
            
            // --- GESTION EMAIL (SIMULATION LOCALHOST) ---
            $subject = "Confirmation : " . $event['titre'];
            $body = "Bonjour $nom,\n\nVotre inscription est validée pour l'événement :\n" . $event['titre'] . "\n\nDate : " . date('d/m/Y H:i', strtotime($event['date_evenement'])) . "\nLieu : " . $event['lieu'];
            $headers = "From: no-reply@eventmanager.com";

            // 1. Écriture dans un fichier texte (Pour le test)
            $log_content = "-----------------------------------\n";
            $log_content .= "DATE : " . date('Y-m-d H:i:s') . "\n";
            $log_content .= "A : $email\n";
            $log_content .= "SUJET : $subject\n";
            $log_content .= "MESSAGE :\n$body\n";
            $log_content .= "-----------------------------------\n\n";
            
            // On écrit dans 'emails_envoyes.txt' à la racine
            file_put_contents('emails_envoyes.txt', $log_content, FILE_APPEND);

            // 2. Tentative d'envoi réel (Optionnel)
            @mail($email, $subject, $body, $headers);
            
            $message = "<div class='alert alert-success border-0 shadow-sm'><i class='bi bi-check-circle-fill'></i> Inscription réussie ! Un email de confirmation a été généré.</div>";
        } else {
            $message = "<div class='alert alert-warning border-0 shadow-sm'><i class='bi bi-exclamation-triangle-fill'></i> Vous êtes déjà inscrit.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger border-0 shadow-sm'>Complet ! Désolé.</div>";
    }
}

// Calcul des places restantes pour l'affichage (Barre de progression)
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE evenement_id = ?");
$countStmt->execute([$id]);
$inscrits = $countStmt->fetchColumn();
$restant = $event['nb_max_participants'] - $inscrits;
// Évite la division par zéro
$percent = ($event['nb_max_participants'] > 0) ? ($inscrits / $event['nb_max_participants']) * 100 : 100;
?>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex align-items-center mb-3">
                <span class="badge bg-primary badge-custom me-2"><?= htmlspecialchars($event['cat_nom']) ?></span>
                <small class="text-muted"><i class="bi bi-clock"></i> Publié le <?= date('d/m/Y', strtotime($event['created_at'])) ?></small>
            </div>
            
            <h1 class="display-5 fw-bold text-dark mb-4"><?= htmlspecialchars($event['titre']) ?></h1>
            
            <div class="d-flex mb-4 text-secondary">
                <div class="me-4">
                    <i class="bi bi-calendar-check fs-4 text-primary"></i>
                    <div class="fw-bold">Date</div>
                    <div><?= date('d/m/Y à H:i', strtotime($event['date_evenement'])) ?></div>
                </div>
                <div>
                    <i class="bi bi-geo-alt fs-4 text-danger"></i>
                    <div class="fw-bold">Lieu</div>
                    <div><?= htmlspecialchars($event['lieu']) ?></div>
                </div>
            </div>

            <hr class="my-4" style="opacity: 0.1;">
            
            <h5 class="fw-bold"><i class="bi bi-file-text me-2"></i>À propos de cet événement</h5>
            <p class="lead text-muted mt-3" style="line-height: 1.8;">
                <?= nl2br(htmlspecialchars($event['description'])) ?>
            </p>
        </div>
        
        <a href="index.php" class="btn btn-light text-muted"><i class="bi bi-arrow-left"></i> Retour à la liste</a>
    </div>

    <div class="col-lg-4">
        <div class="card card-custom shadow-lg border-0 sticky-top" style="top: 20px; z-index: 100;">
            <div class="card-header-custom text-center">
                <h4 class="mb-0 text-white"><i class="bi bi-ticket-perforated"></i> Réservation</h4>
            </div>
            <div class="card-body p-4">
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold text-muted">Places occupées</span>
                        <span class="fw-bold text-primary"><?= $inscrits ?> / <?= $event['nb_max_participants'] ?></span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar bg-gradient" role="progressbar" style="width: <?= $percent ?>%; background: linear-gradient(90deg, #00d2ff 0%, #3a7bd5 100%);"></div>
                    </div>
                </div>

                <?= $message ?>

                <?php if($restant > 0): ?>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label text-muted fw-bold">Votre Nom</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control bg-light border-0 py-2" 
                                           value="<?= htmlspecialchars($_SESSION['nom'] ?? '') ?>" readonly>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted fw-bold">Votre Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control bg-light border-0 py-2" 
                                           value="<?= htmlspecialchars($_SESSION['email'] ?? 'Erreur: Email manquant (Relancez Login)') ?>" readonly>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-gradient w-100 py-2 fs-5 shadow-sm">
                                Je confirme ma place <i class="bi bi-check-circle ms-2"></i>
                            </button>
                        </form>

                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-lock-fill fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">Connectez-vous pour réserver</h5>
                            <p class="small text-secondary">La réservation est réservée à nos membres.</p>
                            <a href="login.php" class="btn btn-outline-primary w-100">Se connecter</a>
                            <a href="register.php" class="btn btn-link mt-2">Créer un compte</a>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <button class="btn btn-secondary w-100 py-2" disabled>Complet</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>