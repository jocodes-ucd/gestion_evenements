<?php
// admin/create_event.php
require '../includes/db.php';
require 'auth_check.php';

// 1. TRAITEMENT DU FORMULAIRE (AVANT LE HTML)
$message = "";
// On récupère les catégories pour le menu déroulant
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des données
    $titre = trim($_POST['titre']);
    $desc  = trim($_POST['description']);
    $date  = $_POST['date'];
    $lieu  = trim($_POST['lieu']);
    $cat_id = $_POST['categorie_id'];
    $max   = $_POST['nb_max_participants'];

    if (!empty($titre) && !empty($date) && !empty($lieu)) {
        $sql = "INSERT INTO evenements (titre, description, date_evenement, lieu, categorie_id, nb_max_participants) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$titre, $desc, $date, $lieu, $cat_id, $max])) {
            // Succès : La redirection marche car aucun HTML n'a été affiché avant
            header("Location: index.php?msg=created");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Erreur lors de l'enregistrement.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Veuillez remplir les champs obligatoires.</div>";
    }
}

// 2. INCLURE LE DESIGN (SEULEMENT APRÈS LA LOGIQUE)
include '../includes/header.php'; 
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-custom p-4 shadow-sm">
                <div class="mb-4 border-bottom pb-2">
                    <h4 class="mb-0 text-primary"><i class="bi bi-plus-circle"></i> Créer un nouvel événement</h4>
                </div>
                
                <?= $message ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">TITRE DE L'ÉVÉNEMENT *</label>
                        <input type="text" name="titre" class="form-control bg-light border-0" required placeholder="Ex: Conférence Tech 2025">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">DATE ET HEURE *</label>
                            <input type="datetime-local" name="date" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">CATÉGORIE</label>
                            <select name="categorie_id" class="form-select bg-light border-0">
                                <?php foreach($cats as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">LIEU *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" name="lieu" class="form-control bg-light border-0" required placeholder="Ex: Salle de réunion A">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">NOMBRE MAX DE PARTICIPANTS</label>
                        <input type="number" name="nb_max_participants" class="form-control bg-light border-0" value="50">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">DESCRIPTION COMPLÈTE</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="5" placeholder="Détails de l'événement..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="index.php" class="text-decoration-none text-muted fw-bold">Annuler</a>
                        <button type="submit" class="btn btn-gradient px-5 py-2 rounded-pill shadow-sm">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>