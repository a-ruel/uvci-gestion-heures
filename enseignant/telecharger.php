<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

verifierAccesPage(['enseignant']);

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT e.*, u.nom_utilisateur FROM enseignants e JOIN utilisateurs u ON u.enseignant_id = e.id WHERE u.id = ?");
$stmt->execute([$userId]);
$enseignant = $stmt->fetch();
if (!$enseignant) die("Enseignant non trouvé.");

$stmt = $pdo->prepare("SELECT a.*, s.titre as seq_titre, c.intitule as cours, t.nom as type, aa.libelle as annee
                       FROM activites_enseignants a
                       JOIN sequences s ON a.sequence_id = s.id
                       JOIN cours c ON s.cours_id = c.id
                       JOIN types_activite t ON a.type_activite_id = t.id
                       LEFT JOIN annees_academiques aa ON c.annee_academique_id = aa.id
                       WHERE a.enseignant_id = ? AND a.valide = 1
                       ORDER BY a.date_realisation DESC");
$stmt->execute([$enseignant['id']]);
$activites = $stmt->fetchAll();

$totalHeures = array_sum(array_column($activites, 'heures_calculees'));
$seuil = $pdo->query("SELECT valeur FROM parametres_systeme WHERE cle = 'seuil_heures_complementaires'")->fetchColumn();
$seuil = $seuil ?: 192;
$complementaires = max(0, $totalHeures - $seuil);
$remuneration = $totalHeures * $enseignant['taux_horaire'];

$html = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Fiche récapitulative - '.htmlspecialchars($enseignant['nom']).'</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; margin: 40px; }
    h1, h2 { color: #2c3e50; }
    .infos { margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .total { margin-top: 20px; font-weight: bold; }
</style>
</head>
<body>
    <h1>Fiche récapitulative des heures d\'enseignement</h1>
    <div class="infos">
        <p><strong>Enseignant :</strong> '.htmlspecialchars($enseignant['nom'].' '.$enseignant['prenom']).'</p>
        <p><strong>Email :</strong> '.htmlspecialchars($enseignant['email']).'</p>
        <p><strong>Taux horaire :</strong> '.number_format($enseignant['taux_horaire'], 0).' FCFA/h</p>
        <p><strong>Date d\'embauche :</strong> '.($enseignant['date_embauche'] ?? 'Non renseignée').'</p>
    </div>
    <h2>Détail des activités validées</h2>
    <table><thead><tr><th>Cours</th><th>Séquence</th><th>Type</th><th>Niveau</th><th>Date</th><th>Heures</th><th>Année</th></tr></thead><tbody>';
foreach ($activites as $a) {
    $html .= '<tr><td>'.htmlspecialchars($a['cours']).'</td><td>'.htmlspecialchars($a['seq_titre']).'</td><td>'.htmlspecialchars($a['type']).'</td><td>'.$a['niveau_complexite'].'</td><td>'.$a['date_realisation'].'</td><td>'.number_format($a['heures_calculees'], 2).' h</td><td>'.htmlspecialchars($a['annee'] ?? 'N/A').'</td></tr>';
}
$html .= '</tbody></table>
    <div class="total">
        <p>Total heures validées : '.number_format($totalHeures, 2).' h</p>
        <p>Heures complémentaires : '.number_format($complementaires, 2).' h</p>
        <p>Rémunération totale : '.number_format($remuneration, 0).' FCFA</p>
    </div>
    <p>Document généré le '.date('d/m/Y').'</p>
</body>
</html>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("fiche_enseignant_".$enseignant['id'].".pdf", ['Attachment' => true]);
exit;
?>
