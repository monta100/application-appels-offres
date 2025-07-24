<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat de Prestation de Service</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #333;
        }

        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 8px;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-weight: bold;
            font-size: 16px;
            background-color: #f0f0f0;
            padding: 6px 10px;
            border-left: 4px solid #2c3e50;
            margin-bottom: 5px;
        }

        .box {
            border: 1px solid #ccc;
            padding: 12px;
            border-radius: 5px;
        }

        .label {
            font-weight: bold;
        }

        .signatures {
            margin-top: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .signature-block {
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 220px;
            margin: 10px auto 5px;
        }

        .signature-img {
            height: 50px;
            margin-bottom: 5px;
        }

    </style>
</head>
<body>
    <h1>Contrat de Prestation de Service</h1>

    <p>
        Ce contrat est généré automatiquement suite à la sélection de votre soumission. Il sera en vigueur après signature et restera valide pour la durée de la mission. Un duplicata signé sera envoyé sous 72 heures.
    </p>

    <!-- Soumissionnaire -->
    <div class="section">
        <div class="section-title">Soumissionnaire</div>
        <div class="box">
            <p><span class="label">Nom :</span> {{ $soumission->user->nom }}</p>
            <p><span class="label">Email :</span> {{ $soumission->user->email }}</p>
            <p><span class="label">Société :</span> {{ $soumission->user->nomSociete }}</p>
        </div>
    </div>

    <!-- Appel d'offre -->
    <div class="section">
        <div class="section-title">Appel d'offre</div>
        <div class="box">
            <p><span class="label">Titre :</span> {{ $soumission->appelOffre->titre }}</p>
            <p><span class="label">Description :</span> {{ $soumission->appelOffre->description }}</p>
            <p><span class="label">Budget :</span> {{ $soumission->appelOffre->budget }} TND</p>
            <p><span class="label">Date de début :</span> {{ $soumission->appelOffre->date_debut->format('d/m/Y') }}</p>
            <p><span class="label">Date de fin :</span> {{ $soumission->appelOffre->date_fin->format('d/m/Y') }}</p>
        </div>
    </div>

    <!-- Soumission -->
    <div class="section">
        <div class="section-title">Détails de la soumission</div>
        <div class="box">
            <p><span class="label">Prix proposé :</span> {{ $soumission->prixPropose }} TND</p>
            <p><span class="label">Délai :</span> {{ $soumission->temps_realisation }} jours</p>
            <p><span class="label">Description :</span> {{ $soumission->description }}</p>
        </div>
    </div>

    <!-- Conditions -->
    <div class="section">
        <div class="section-title">Conditions et obligations</div>
        <div class="box">
            <p>
                Le prestataire s'engage à respecter les délais indiqués, à livrer un travail de qualité, et à maintenir une communication régulière avec le représentant de l'appel d'offre.
                L'entreprise, quant à elle, s'engage à fournir les informations nécessaires et à effectuer le paiement selon les modalités convenues.
            </p>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-block">
            <div class="signature-line"></div>
            <strong>Signature du Prestataire</strong>
        </div>

        <div class="signature-block">
            <div class="signature-line"></div>
            <strong>Signature de l'Entreprise</strong>
        </div>
    </div>
</body>
</html>
