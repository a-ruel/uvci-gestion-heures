# Guide utilisateur – Application UVCI

## Connexion
- Rendez-vous sur `/login.php`.
- Utilisez vos identifiants (fournis par l’administrateur).

## Rôles
- **Administrateur** : gère les utilisateurs, années académiques, paramètres généraux, départements, grades, statuts, niveaux. Peut aussi consulter les logs et sauvegarder la base.
- **Secrétaire principal** : gère les enseignants, les cours, les séquences, les ressources, saisit et valide les activités, consulte les volumes horaires et génère des rapports.
- **Enseignant** : consulte son tableau de bord, ses activités, ses volumes horaires, télécharge sa fiche récapitulative (PDF).

## Actions principales
- **Ajouter un enseignant** (Secrétaire) : remplir le formulaire. Un compte utilisateur peut être créé automatiquement (si la version améliorée est active).
- **Saisir une activité** : sélectionner enseignant, cours, séquence, type (conception/mise à jour), niveau de complexité → calcul automatique des heures.
- **Valider des activités** (Secrétaire) : cocher les activités en attente et valider en bloc.
- **Générer un rapport** : choisir le type et le format (HTML, PDF, Excel). Le fichier est téléchargé.
- **Sauvegarder la base** (Admin) : aller dans "Sauvegarde" et télécharger le fichier SQL.
- **Consulter les logs** (Admin) : voir l’historique des actions (connexions, créations, modifications, etc.).

## Problèmes fréquents
- *"Votre compte n'est pas lié à un enseignant"* : contactez l’admin pour lier votre compte utilisateur à une fiche enseignant.
- *"Accès interdit"* : vérifiez que vous êtes connecté avec le bon rôle.
- *Le PDF ne se génère pas* : vérifiez que `vendor/dompdf` est installé.

