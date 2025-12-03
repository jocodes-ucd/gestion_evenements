<?php
// admin/edit_event.php
require '../includes/db.php';
require 'auth_check.php';

// 1. LOGIQUE PHP (AVANT LE HTML)

// Vérifier ID
if (!isset($_GET['id'])) { die("ID manquant."); }
$id = $_GET['id'];

$message = "";

// A. Si le formulaire est soumis (UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $desc  = trim($_POST['description']);
    $date  = $_POST['date'];
    $lieu  = trim($_POST['lieu']);
    $cat_id = $_POST['categorie_id'];
    $max   = $_POST['nb_max_participants'];

    if (!empty($titre) && !empty($date) && !empty($lieu)) {
        $sql = "UPDATE evenements 
                SET titre=?, description=?, date_evenement=?, lieu=?, categorie_id=?, nb_max_participants=? 
                WHERE id=?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$titre, $desc, $date, $lieu, $cat_id, $max, $id])) {
            // REDIRECTION AVANT HTML
            header("Location: index.php?msg=updated");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Erreur lors de la mise à jour.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Champs obligatoires manquants.</div>";
    }
}

// B. Récupérer les infos actuelles (Pour remplir le formulaire)
$stmt = $pdo->prepare("SELECT * FROM evenements WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) { die("Événement introuvable."); }

// C. Récupérer catégories
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();

// 2. INCLURE LE DESIGN
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-custom p-4 shadow">
                <div class="mb-4 border-bottom pb-2">
                    <h4 class="mb-0 text-warning"><i class="bi bi-pencil-square"></i> Modifier l'événement</h4>
                </div>
                
                <?= $message ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">TITRE</label>
                        <input type="text" name="titre" class="form-control bg-light border-0" required 
                               value="<?= htmlspecialchars($event['titre']) ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">DATE</label>
                            <input type="datetime-local" name="date" class="form-control bg-light border-0" required
                                   value="<?= date('Y-m-d\TH:i', strtotime($event['date_evenement'])) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">CATÉGORIE</label>
                            <select name="categorie_id" class="form-select bg-light border-0">
                                <?php foreach($cats as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $event['categorie_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">LIEU</label>
                        <input type="text" name="lieu" class="form-control bg-light border-0" required
                               value="<?= htmlspecialchars($event['lieu']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">PARTICIPANTS MAX</label>
                        <input type="number" name="nb_max_participants" class="form-control bg-light border-0"
                               value="<?= htmlspecialchars($event['nb_max_participants']) ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">DESCRIPTION</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="5"><?= htmlspecialchars($event['description']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="index.php" class="text-decoration-none text-muted fw-bold">Annuler</a>
                        <button type="submit" class="btn btn-warning fw-bold px-5 py-2 rounded-pill shadow-sm">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>