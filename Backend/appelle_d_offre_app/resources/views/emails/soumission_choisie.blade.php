<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Soumission Choisie</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;">
  <div style="max-width: 700px; margin: auto; background-color: #ffffff; padding: 30px; border-radius: 10px; border-top: 6px solid #ff6600; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    
    <h2 style="color: #ff6600; font-size: 26px; margin-bottom: 10px;">🎯 Félicitations !</h2>

    <p>Bonjour <strong>{{ $soumission->user->nom ?? 'Cher utilisateur' }}</strong>,</p>

    <p>Votre soumission pour l'appel d'offre <strong>« {{ $soumission->appelOffre->titre ?? 'N/A' }} »</strong> a été <span style="color: green;"><strong>choisie</strong></span>.</p>

    <div style="background: #fffbea; padding: 15px 20px; border-left: 5px solid #ff6600; margin: 20px 0; border-radius: 6px;">
      <p style="margin: 0 0 10px;"><strong>Détails de l'appel d'offre :</strong></p>
      <ul style="list-style: none; padding: 0; margin: 0;">
        <li>🔸 <strong>Domaine :</strong> {{ $soumission->appelOffre->domaine->nom ?? 'Non spécifié' }}</li>
        <li>📅 <strong>Date de début :</strong> {{ $soumission->appelOffre->date_debut->format('d/m/Y') }}</li>
        <li>📅 <strong>Date de fin :</strong> {{ $soumission->appelOffre->date_fin->format('d/m/Y') }}</li>
        <li>💰 <strong>Budget :</strong> {{ number_format($soumission->appelOffre->budget, 0, ',', ' ') }} TND</li>
      </ul>
    </div>

    <p><strong>Le représentant de l'offre est :</strong></p>
    <ul style="list-style: none; padding-left: 0;">
      <li>🏢 <strong>Société :</strong> {{ $soumission->appelOffre->user->nomSociete }}</li>
      <li>👤 <strong>Nom :</strong> {{ $soumission->appelOffre->user->prenom }} {{ $soumission->appelOffre->user->nom }}</li>
      <li>📧 <strong>Email :</strong> <a href="mailto:{{ $soumission->appelOffre->user->email }}" style="color: #007BFF;">{{ $soumission->appelOffre->user->email }}</a></li>
    </ul>

    <div style="margin-top: 30px; background-color: #e7f5e8; padding: 15px; border-radius: 6px; border-left: 5px solid green;">
      <p style="margin: 0;">📝 <strong>Un contrat officiel</strong> sera généré et vous sera envoyé à votre adresse email <strong>dans un délai de 72 heures</strong>.</p>
    </div>

    <p style="margin-top: 30px;">Merci pour votre confiance,<br><strong>— L’équipe Comport</strong></p>

    <hr style="margin-top: 40px; border: none; border-top: 1px solid #ddd;">

    <div style="text-align: center; font-size: 13px; color: #999;">
      © {{ now()->year }} Comport. Tous droits réservés.
    </div>
  </div>
</body>
</html>
