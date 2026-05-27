<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Paramètres système";
include '../inc/admin_header.php';

$onglet = $_GET['onglet'] ?? 'taux';

// Gestion des grades (CRUD)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_grade'])) {
    if ($_POST['action_grade'] == 'ajouter') {
        $nom = trim($_POST['nom']);
        $pdo->prepare("INSERT INTO grades (nom) VALUES (?)")->execute([$nom]);
        header('Location: parametres.php?onglet=grades&msg=grade_ajoute');
        exit;
    } elseif ($_POST['action_grade'] == 'modifier') {
        $id = (int)$_POST['id'];
        $nom = trim($_POST['nom']);
        $pdo->prepare("UPDATE grades SET nom = ? WHERE id = ?")->execute([$nom, $id]);
        header('Location: parametres.php?onglet=grades&msg=grade_modifie');
        exit;
    } elseif ($_POST['action_grade'] == 'supprimer') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM grades WHERE id = ?")->execute([$id]);
        header('Location: parametres.php?onglet=grades&msg=grade_supprime');
        exit;
    }
}

// Gestion des statuts (CRUD)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_statut'])) {
    if ($_POST['action_statut'] == 'ajouter') {
        $nom = trim($_POST['nom']);
        $pdo->prepare("INSERT INTO statuts (nom) VALUES (?)")->execute([$nom]);
        header('Location: parametres.php?onglet=statuts&msg=statut_ajoute');
        exit;
    } elseif ($_POST['action_statut'] == 'modifier') {
        $id = (int)$_POST['id'];
        $nom = trim($_POST['nom']);
        $pdo->prepare("UPDATE statuts SET nom = ? WHERE id = ?")->execute([$nom, $id]);
        header('Location: parametres.php?onglet=statuts&msg=statut_modifie');
        exit;
    } elseif ($_POST['action_statut'] == 'supprimer') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM statuts WHERE id = ?")->execute([$id]);
        header('Location: parametres.php?onglet=statuts&msg=statut_supprime');
        exit;
    }
}

// Gestion des niveaux (CRUD)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_niveau'])) {
    if ($_POST['action_niveau'] == 'ajouter') {
        $code = trim($_POST['code']);
        $libelle = trim($_POST['libelle']);
        $pdo->prepare("INSERT INTO niveaux_etudes (code, libelle) VALUES (?, ?)")->execute([$code, $libelle]);
        header('Location: parametres.php?onglet=niveaux&msg=niveau_ajoute');
        exit;
    } elseif ($_POST['action_niveau'] == 'modifier') {
        $id = (int)$_POST['id'];
        $code = trim($_POST['code']);
        $libelle = trim($_POST['libelle']);
        $pdo->prepare("UPDATE niveaux_etudes SET code = ?, libelle = ? WHERE id = ?")->execute([$code, $libelle, $id]);
        header('Location: parametres.php?onglet=niveaux&msg=niveau_modifie');
        exit;
    } elseif ($_POST['action_niveau'] == 'supprimer') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM niveaux_etudes WHERE id = ?")->execute([$id]);
        header('Location: parametres.php?onglet=niveaux&msg=niveau_supprime');
        exit;
    }
}

// Gestion des taux horaires (existante)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_taux'])) {
    if ($_POST['action_taux'] == 'ajouter') {
        $grade_id = (int)$_POST['grade_id'];
        $statut_id = (int)$_POST['statut_id'];
        $taux = (float)$_POST['taux_horaire'];
        $stmt = $pdo->prepare("INSERT INTO taux_horaires (grade_id, statut_id, taux_horaire) VALUES (?, ?, ?)
                               ON DUPLICATE KEY UPDATE taux_horaire = VALUES(taux_horaire)");
        $stmt->execute([$grade_id, $statut_id, $taux]);
        header('Location: parametres.php?onglet=taux&msg=taux_ajoute');
        exit;
    } elseif ($_POST['action_taux'] == 'modifier') {
        $id = (int)$_POST['id'];
        $grade_id = (int)$_POST['grade_id'];
        $statut_id = (int)$_POST['statut_id'];
        $taux = (float)$_POST['taux_horaire'];
        $check = $pdo->prepare("SELECT id FROM taux_horaires WHERE grade_id = ? AND statut_id = ? AND id != ?");
        $check->execute([$grade_id, $statut_id, $id]);
        if ($check->fetch()) {
            $message = "Ce couple (grade/statut) existe déjà.";
        } else {
            $stmt = $pdo->prepare("UPDATE taux_horaires SET grade_id = ?, statut_id = ?, taux_horaire = ? WHERE id = ?");
            $stmt->execute([$grade_id, $statut_id, $taux, $id]);
            $message = "Taux modifié.";
        }
        header("Location: parametres.php?onglet=taux&msg=" . urlencode($message));
        exit;
    } elseif ($_POST['action_taux'] == 'supprimer') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM taux_horaires WHERE id = ?")->execute([$id]);
        header('Location: parametres.php?onglet=taux&msg=taux_supprime');
        exit;
    }
}

// Sauvegarde (backup)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_backup'])) {
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="backup_' . date('Y-m-d_H-i-s') . '.sql"');
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "DROP TABLE IF EXISTS `$table`;\n";
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        echo $create['Create Table'] . ";\n\n";
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll();
        foreach ($rows as $row) {
            $cols = array_keys($row);
            $vals = array_map(function($v) use ($pdo) { return $pdo->quote($v); }, array_values($row));
            echo "INSERT INTO `$table` (`" . implode("`,`", $cols) . "`) VALUES (" . implode(",", $vals) . ");\n";
        }
        echo "\n";
    }
    exit;
}

// Récupération des données pour les affichages
$grades = $pdo->query("SELECT id, nom FROM grades ORDER BY nom")->fetchAll();
$statuts = $pdo->query("SELECT id, nom FROM statuts ORDER BY nom")->fetchAll();
$taux_list = $pdo->query("SELECT t.id, t.taux_horaire, t.grade_id, t.statut_id, g.nom as grade_nom, s.nom as statut_nom
                          FROM taux_horaires t
                          JOIN grades g ON t.grade_id = g.id
                          JOIN statuts s ON t.statut_id = s.id")->fetchAll();

$grades_list = $pdo->query("SELECT * FROM grades ORDER BY id")->fetchAll();
$statuts_list = $pdo->query("SELECT * FROM statuts ORDER BY id")->fetchAll();
$niveaux_list = $pdo->query("SELECT * FROM niveaux_etudes ORDER BY code")->fetchAll();

// Logs (récupération avec pagination)
$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_logs = $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();
$total_pages = ceil($total_logs / $limit);
$logs = $pdo->prepare("SELECT l.*, u.nom_utilisateur as user_nom 
                       FROM logs l 
                       LEFT JOIN utilisateurs u ON l.utilisateur_id = u.id 
                       ORDER BY l.date_creation DESC 
                       LIMIT :limit OFFSET :offset");
$logs->bindValue(':limit', $limit, PDO::PARAM_INT);
$logs->bindValue(':offset', $offset, PDO::PARAM_INT);
$logs->execute();
$logs = $logs->fetchAll();

$message = '';
if (isset($_GET['msg'])) {
    $message = htmlspecialchars(urldecode($_GET['msg']));
}
?>
<div class="admin-header-bar">
</div>

<!-- Navigation par onglets -->
<div style="display: flex; gap: 0.5rem; border-bottom: 1px solid #e2e8f0; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <a href="?onglet=taux" class="button <?= $onglet == 'taux' ? 'button-primary' : 'button-outline' ?>">Taux horaires</a>
    <a href="?onglet=grades" class="button <?= $onglet == 'grades' ? 'button-primary' : 'button-outline' ?>">Grades</a>
    <a href="?onglet=statuts" class="button <?= $onglet == 'statuts' ? 'button-primary' : 'button-outline' ?>">Statuts</a>
    <a href="?onglet=niveaux" class="button <?= $onglet == 'niveaux' ? 'button-primary' : 'button-outline' ?>">Niveaux</a>
    <a href="?onglet=sauvegarde" class="button <?= $onglet == 'sauvegarde' ? 'button-primary' : 'button-outline' ?>">Sauvegarde</a>
    <a href="?onglet=logs" class="button <?= $onglet == 'logs' ? 'button-primary' : 'button-outline' ?>">Logs</a>
</div>

<?php if ($message): ?>
    <div class="alert success"><?= $message ?></div>
<?php endif; ?>

<!-- Onglet Taux horaires -->
<?php if ($onglet == 'taux'): ?>
    <div style="margin-bottom: 1rem;">
        <button onclick="openTauxAddModal()" class="button button-success">+ Nouveau taux</button>
    </div>
    <table class="admin-table">
        <thead><tr><th>Grade</th><th>Statut</th><th>Taux horaire (FCFA/h)</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($taux_list as $t): ?>
        <tr>
            <td><?= htmlspecialchars($t['grade_nom']) ?></td>
            <td><?= htmlspecialchars($t['statut_nom']) ?></td>
            <td><?= number_format($t['taux_horaire'], 0) ?> FCFA/h</td>
            <td style="display: flex; gap: 8px;">
                <button onclick="openTauxEditModal(<?= $t['id'] ?>, <?= $t['grade_id'] ?>, <?= $t['statut_id'] ?>, <?= $t['taux_horaire'] ?>)" class="button button-small button-primary">Modifier</button>
                <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce taux ?')">
                    <input type="hidden" name="action_taux" value="supprimer">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="button button-small button-danger">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modales pour taux (ajout et modification) -->
    <div id="tauxAddModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
        <h3>Nouveau taux horaire</h3>
        <form method="post">
            <input type="hidden" name="action_taux" value="ajouter">
            <div class="admin-form-group"><label>Grade</label><select name="grade_id" required><?php foreach ($grades as $g): ?><option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nom']) ?></option><?php endforeach; ?></select></div>
            <div class="admin-form-group"><label>Statut</label><select name="statut_id" required><?php foreach ($statuts as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?></option><?php endforeach; ?></select></div>
            <div class="admin-form-group"><label>Taux horaire (FCFA)</label><input type="number" step="100" name="taux_horaire" required></div>
            <button type="submit" class="button">Enregistrer</button>
            <button type="button" onclick="closeTauxAddModal()" class="button button-outline">Annuler</button>
        </form>
    </div>
    <div id="tauxEditModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
        <h3>Modifier le taux horaire</h3>
        <form method="post">
            <input type="hidden" name="action_taux" value="modifier">
            <input type="hidden" name="id" id="editTauxId">
            <div class="admin-form-group"><label>Grade</label><select name="grade_id" id="editGradeId" required><?php foreach ($grades as $g): ?><option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nom']) ?></option><?php endforeach; ?></select></div>
            <div class="admin-form-group"><label>Statut</label><select name="statut_id" id="editStatutId" required><?php foreach ($statuts as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?></option><?php endforeach; ?></select></div>
            <div class="admin-form-group"><label>Taux horaire (FCFA)</label><input type="number" step="100" name="taux_horaire" id="editTauxValue" required></div>
            <button type="submit" class="button">Enregistrer</button>
            <button type="button" onclick="closeTauxEditModal()" class="button button-outline">Annuler</button>
        </form>
    </div>
    <script>
    function openTauxAddModal() { document.getElementById('tauxAddModal').style.display = 'block'; }
    function closeTauxAddModal() { document.getElementById('tauxAddModal').style.display = 'none'; }
    function openTauxEditModal(id, gradeId, statutId, taux) {
        document.getElementById('editTauxId').value = id;
        document.getElementById('editGradeId').value = gradeId;
        document.getElementById('editStatutId').value = statutId;
        document.getElementById('editTauxValue').value = taux;
        document.getElementById('tauxEditModal').style.display = 'block';
    }
    function closeTauxEditModal() { document.getElementById('tauxEditModal').style.display = 'none'; }
    window.onclick = function(event) {
        if (event.target == document.getElementById('tauxAddModal')) closeTauxAddModal();
        if (event.target == document.getElementById('tauxEditModal')) closeTauxEditModal();
    }
    </script>

<!-- Onglet Grades -->
<?php elseif ($onglet == 'grades'): ?>
    <div style="margin-bottom: 1rem;">
        <button onclick="openGradeAddModal()" class="button button-success">+ Nouveau grade</button>
    </div>
    <table class="admin-table">
        <thead><tr><th>ID</th><th>Nom</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($grades_list as $g): ?>
        <tr>
            <td><?= $g['id'] ?></td>
            <td><?= htmlspecialchars($g['nom']) ?></td>
            <td style="display: flex; gap: 8px;">
                <button onclick="openGradeEditModal(<?= $g['id'] ?>, '<?= htmlspecialchars($g['nom']) ?>')" class="button button-small button-primary">Modifier</button>
                <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce grade ?')">
                    <input type="hidden" name="action_grade" value="supprimer">
                    <input type="hidden" name="id" value="<?= $g['id'] ?>">
                    <button type="submit" class="button button-small button-danger">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div id="gradeAddModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
        <h3>Ajouter un grade</h3>
        <form method="post">
            <input type="hidden" name="action_grade" value="ajouter">
            <div class="admin-form-group"><label>Nom</label><input type="text" name="nom" required></div>
            <button type="submit" class="button">Enregistrer</button>
            <button type="button" onclick="closeGradeAddModal()" class="button button-outline">Annuler</button>
        </form>
    </div>
    <div id="gradeEditModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
        <h3>Modifier le grade</h3>
        <form method="post">
            <input type="hidden" name="action_grade" value="modifier">
            <input type="hidden" name="id" id="editGradeId">
            <div class="admin-form-group"><label>Nom</label><input type="text" name="nom" id="editGradeNom" required></div>
            <button type="submit" class="button">Enregistrer</button>
            <button type="button" onclick="closeGradeEditModal()" class="button button-outline">Annuler</button>
        </form>
    </div>
    <script>
    function openGradeAddModal() { document.getElementById('gradeAddModal').style.display = 'block'; }
    function closeGradeAddModal() { document.getElementById('gradeAddModal').style.display = 'none'; }
    function openGradeEditModal(id, nom) {
        document.getElementById('editGradeId').value = id;
        document.getElementById('editGradeNom').value = nom;
        document.getElementById('gradeEditModal').style.display = 'block';
    }
    function closeGradeEditModal() { document.getElementById('gradeEditModal').style.display = 'none'; }
    window.onclick = function(event) {
        if (event.target == document.getElementById('gradeAddModal')) closeGradeAddModal();
        if (event.target == document.getElementById('gradeEditModal')) closeGradeEditModal();
    }
    </script>

<!-- Onglet Statuts -->
<?php elseif ($onglet == 'statuts'): ?>
    <div style="margin-bottom: 1rem;">
        <button onclick="openStatutAddModal()" class="button button-success">+ Nouveau statut</button>
    </div>
    <table class="admin-table">
        <thead><tr><th>ID</th><th>Nom</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($statuts_list as $s): ?>
        <tr>
            <td><?= $s['id'] ?></td>
            <td><?= htmlspecialchars($s['nom']) ?></td>
            <td style="display: flex; gap: 8px;">
                <button onclick="openStatutEditModal(<?= $s['id'] ?>, '<?= htmlspecialchars($s['nom']) ?>')" class="button button-small button-primary">Modifier</button>
                <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce statut ?')">
                    <input type="hidden" name="action_statut" value="supprimer">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <button type="submit" class="button button-small button-danger">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div id="statutAddModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
        <h3>Ajouter un statut</h3>
        <form method="post">
            <input type="hidden" name="action_statut" value="ajouter">
            <div class="admin-form-group"><label>Nom</label><input type="text" name="nom" required></div>
            <button type="submit" class="button">Enregistrer</button>
            <button type="button" onclick="closeStatutAddModal()" class="button button-outline">Annuler</button>
        </form>
    </div>
    <div id="statutEditModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
        <h3>Modifier le statut</h3>
        <form method="post">
            <input type="hidden" name="action_statut" value="modifier">
            <input type="hidden" name="id" id="editStatutId">
            <div class="admin-form-group"><label>Nom</label><input type="text" name="nom" id="editStatutNom" required></div>
            <button type="submit" class="button">Enregistrer</button>
            <button type="button" onclick="closeStatutEditModal()" class="button button-outline">Annuler</button>
        </form>
    </div>
    <script>
    function openStatutAddModal() { document.getElementById('statutAddModal').style.display = 'block'; }
    function closeStatutAddModal() { document.getElementById('statutAddModal').style.display = 'none'; }
    function openStatutEditModal(id, nom) {
        document.getElementById('editStatutId').value = id;
        document.getElementById('editStatutNom').value = nom;
        document.getElementById('statutEditModal').style.display = 'block';
    }
    function closeStatutEditModal() { document.getElementById('statutEditModal').style.display = 'none'; }
    window.onclick = function(event) {
        if (event.target == document.getElementById('statutAddModal')) closeStatutAddModal();
        if (event.target == document.getElementById('statutEditModal')) closeStatutEditModal();
    }
    </script>

<!-- Onglet Niveaux -->
<?php elseif ($onglet == 'niveaux'): ?>
    <div style="margin-bottom: 1rem;">
        <button onclick="openNiveauAddModal()" class="button button-success">+ Nouveau niveau</button>
    </div>
    <table class="admin-table">
        <thead><tr><th>ID</th><th>Code</th><th>Libellé</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($niveaux_list as $n): ?>
        <tr>
            <td><?= $n['id'] ?></td>
            <td><?= htmlspecialchars($n['code']) ?></td>
            <td><?= htmlspecialchars($n['libelle']) ?></td>
            <td style="display: flex; gap: 8px;">
                <button onclick="openNiveauEditModal(<?= $n['id'] ?>, '<?= htmlspecialchars($n['code']) ?>', '<?= htmlspecialchars($n['libelle']) ?>')" class="button button-small button-primary">Modifier</button>
                <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce niveau ?')">
                    <input type="hidden" name="action_niveau" value="supprimer">
                    <input type="hidden" name="id" value="<?= $n['id'] ?>">
                    <button type="submit" class="button button-small button-danger">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div id="niveauAddModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
        <h3>Ajouter un niveau</h3>
        <form method="post">
            <input type="hidden" name="action_niveau" value="ajouter">
            <div class="admin-form-group"><label>Code (ex: L1)</label><input type="text" name="code" required></div>
            <div class="admin-form-group"><label>Libellé (ex: Licence 1)</label><input type="text" name="libelle" required></div>
            <button type="submit" class="button">Enregistrer</button>
            <button type="button" onclick="closeNiveauAddModal()" class="button button-outline">Annuler</button>
        </form>
    </div>
    <div id="niveauEditModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
        <h3>Modifier le niveau</h3>
        <form method="post">
            <input type="hidden" name="action_niveau" value="modifier">
            <input type="hidden" name="id" id="editNiveauId">
            <div class="admin-form-group"><label>Code</label><input type="text" name="code" id="editNiveauCode" required></div>
            <div class="admin-form-group"><label>Libellé</label><input type="text" name="libelle" id="editNiveauLibelle" required></div>
            <button type="submit" class="button">Enregistrer</button>
            <button type="button" onclick="closeNiveauEditModal()" class="button button-outline">Annuler</button>
        </form>
    </div>
    <script>
    function openNiveauAddModal() { document.getElementById('niveauAddModal').style.display = 'block'; }
    function closeNiveauAddModal() { document.getElementById('niveauAddModal').style.display = 'none'; }
    function openNiveauEditModal(id, code, libelle) {
        document.getElementById('editNiveauId').value = id;
        document.getElementById('editNiveauCode').value = code;
        document.getElementById('editNiveauLibelle').value = libelle;
        document.getElementById('niveauEditModal').style.display = 'block';
    }
    function closeNiveauEditModal() { document.getElementById('niveauEditModal').style.display = 'none'; }
    window.onclick = function(event) {
        if (event.target == document.getElementById('niveauAddModal')) closeNiveauAddModal();
        if (event.target == document.getElementById('niveauEditModal')) closeNiveauEditModal();
    }
    </script>

<!-- Onglet Sauvegarde -->
<?php elseif ($onglet == 'sauvegarde'): ?>
    <p>Téléchargez une sauvegarde complète de la base de données.</p>
    <form method="post">
        <input type="hidden" name="action_backup" value="1">
        <button type="submit" class="button">Télécharger la sauvegarde (SQL)</button>
    </form>

<!-- Onglet Logs -->
<?php elseif ($onglet == 'logs'): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <a href="?onglet=logs&clear=1" class="button button-danger" onclick="return confirm('Vider tous les logs ?')">Vider les logs</a>
        <div style="display: flex; gap: 0.5rem;">
            <a href="export_logs_pdf.php" class="button">PDF</a>
            <a href="export_logs_excel.php" class="button">Excel</a>
        </div>
    </div>
    <table class="admin-table">
        <thead>
            <tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Détails</th><th>IP</th></tr>
        </thead>
        <tbody>
        <?php if (count($logs) === 0): ?>
            <tr><td colspan="5">Aucun log enregistré.</td></tr>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['date_creation']) ?></td>
                <td><?= htmlspecialchars($log['user_nom'] ?? 'Système') ?> (ID <?= $log['utilisateur_id'] ?>)</td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= nl2br(htmlspecialchars($log['details'] ?? '')) ?></td>
                <td><?= htmlspecialchars($log['ip']) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <div style="margin-top: 1rem; display: flex; gap: 0.5rem; justify-content: center;">
        <?php if ($page > 1): ?><a href="?onglet=logs&page=<?= $page-1 ?>" class="button button-small">Précédent</a><?php endif; ?>
        <span>Page <?= $page ?> sur <?= $total_pages ?></span>
        <?php if ($page < $total_pages): ?><a href="?onglet=logs&page=<?= $page+1 ?>" class="button button-small">Suivant</a><?php endif; ?>
    </div>

<?php endif; ?>

<?php include '../inc/admin_footer.php'; ?>
