<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['enseignant']);
$page_title = "Calendrier des activités";
include '../inc/enseignant_header.php';

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT enseignant_id FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]);
$enseignant_id = $stmt->fetchColumn();
if (!$enseignant_id) die("Enseignant non lié.");
?>
<div class="admin-header-bar">
    <h1>Calendrier des activités validées</h1>
</div>
<div id="calendar" style="background: white; padding: 1rem; border-radius: 24px;"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        events: 'get_events.php?enseignant_id=<?= $enseignant_id ?>'
    });
    calendar.render();
});
</script>
<?php include '../inc/enseignant_footer.php'; ?>
