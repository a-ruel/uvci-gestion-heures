<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

$users = $pdo->query("SELECT id, nom_utilisateur, role, actif, dernier_acces FROM utilisateurs ORDER BY id")->fetchAll();
$html = '<h1>Liste des utilisateurs</h1><table border="1" cellpadding="5"><thead><tr><th>ID</th><th>Email</th><th>Rôle</th><th>Actif</th><th>Dernier accès</th></tr></thead><tbody>';
foreach ($users as $u) {
    $html .= '<tr>';
    $html .= '<td>' . $u['id'] . '</td>';
    $html .= '<td>' . htmlspecialchars($u['nom_utilisateur']) . '</td>';
    $html .= '<td>' . strtoupper($u['role']) . '</td>';
    $html .= '<td>' . ($u['actif'] ? 'Oui' : 'Non') . '</td>';
    $html .= '<td>' . ($u['dernier_acces'] ?: 'Jamais') . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("utilisateurs.pdf", ['Attachment' => true]);
