<?php
// /admin/edit_event.php
require '../includes/db.php';
require 'auth_check.php';
include '../includes/header.php';

// 1. Vérifier si on a un ID
if (!isset($_GET['id'])) {
    die("<div class='alert alert-danger'>ID manquant.</div>");
}
$id = $_GET['id'];

// 2. Récupérer les infos actuelles de l'événement
$stmt = $pdo->prepare("SELECT * FROM evenements WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    die("<div class='alert alert-danger'>Événement introuvable.</div>");
}

// 3. Récupérer les catégories (pour le menu déroulant)
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();

// 4. Traitement du formulaire de modification
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $desc  = trim($_POST['description']);
    $date  = $_POST['date']; // Format datetime-local
    $lieu  = trim($_POST['lieu']);
    $cat_id = $_POST['categorie_id'];
    $max   = $_POST['nb_max_participants'];

    if (!empty($titre) && !empty($date) && !empty($lieu)) {
        // La requête UPDATE
        $sql = "UPDATE evenements 
                SET titre=?, description=?, date_evenement=?, lieu=?, categorie_id=?, nb_max_participants=? 
                WHERE id=?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$titre, $desc, $date, $lieu, $cat_id, $max, $id])) {
            // Succès : Retour au tableau de bord
            header("Location: index.php?msg=updated");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Erreur lors de la mise à jour.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Veuillez remplir les champs obligatoires.</div>";
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Modifier l'événement</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?= $message ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Titre *</label>
                            <input type="text" name="titre" class="form-control" required 
                                   value="<?= htmlspecialchars($event['titre']) ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date et Heure *</label>
                                <input type="datetime-local" name="date" class="form-control" required
                                       value="<?= date('Y-m-d\TH:i', strtotime($event['date_evenement'])) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Catégorie</label>
                                <select name="categorie_id" class="form-select">
                                    <?php foreach($cats as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $event['categorie_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Lieu *</label>
                            <input type="text" name="lieu" class="form-control" required
                                   value="<?= htmlspecialchars($event['lieu']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre max de participants</label>
                            <input type="number" name="nb_max_participants" class="form-control"
                                   value="<?= htmlspecialchars($event['nb_max_participants']) ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($event['description']) ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-warning px-5">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>