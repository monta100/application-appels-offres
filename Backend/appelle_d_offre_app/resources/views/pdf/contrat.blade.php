<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Contrat de Prestation de Service</title>
  <style>
    /* ====== BASE ====== */
    @page { margin: 28mm 22mm; }
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12.5px; line-height: 1.55; color: #2b2b2b;
      background: #fff;
    }
    h1,h2,h3 { margin: 0 0 8px; }
    h1 { font-size: 22px; letter-spacing:.3px; }
    h2 { font-size: 15.5px; text-transform: uppercase; letter-spacing:.6px; color:#222; }
    p { margin: 0 0 8px; }
    .muted { color: #6b7280; }
    .small { font-size: 11.5px; }

    /* Accent & tokens */
    :root{
      --accent:#ff6600;
      --ink:#1f2937;
      --line:#e9edf2;
      --card:#fafbfc;
      --chip:#f6f7fb;
    }

    /* ====== HEADER ====== */
    .header {
      width:100%; margin-bottom: 16px; border-bottom: 2px solid var(--accent); padding-bottom: 10px;
    }
    .header-grid{
      display: table; width: 100%;
    }
    .header-col{ display: table-cell; vertical-align: middle; }
    .logo-box{
      width: 64px; height: 64px; border:1px solid var(--line); border-radius:12px;
      display:flex; align-items:center; justify-content:center; color:#9aa3af;
    }
    .company-block { padding-left: 14px; }
    .company-name { font-weight:700; font-size:16px; color:var(--ink); }
    .company-meta { font-size:11.5px; color:#6b7280; }

    .title-side { text-align:right; }
    .doc-title { font-weight:800; font-size:20px; color:var(--ink); }
    .badge {
      display:inline-block; padding:4px 10px; border-radius:999px;
      font-weight:700; letter-spacing:.2px; font-size:10.5px; color:#fff;
      background: var(--accent);
    }
    .badge.green { background:#16a34a; }
    .badge.gray { background:#6b7280; }

    /* ====== META STRIP ====== */
    .meta {
      margin: 12px 0 18px; padding:10px; border:1px solid var(--line); border-radius:12px; background:#fff;
    }
    .chips { display:flex; flex-wrap:wrap; gap:8px; }
    .chip {
      background: var(--chip); border:1px solid var(--line);
      padding:6px 10px; border-radius:999px; font-size:11.5px;
    }
    .chip b{ color:var(--ink); }

    /* ====== SECTIONS / CARDS ====== */
    .section { margin-bottom:16px; }
    .card {
      border:1px solid var(--line); border-radius:12px; background:var(--card); padding:12px 14px;
    }
    .section h2 { margin-bottom:8px; }

    .grid-2{
      display: table; width:100%;
    }
    .col{
      display: table-cell; width:50%; vertical-align: top; padding-right:10px;
    }
    .col.right{ padding-right:0; padding-left:10px; }

    .label { color:#374151; font-weight:700; }
    .divider { height:1px; background:var(--line); margin:10px 0; }

    /* ====== ARTICLES ====== */
    .article{ margin-bottom:10px; }
    .article-title{
      font-weight:700; color:var(--ink); text-decoration: underline; margin-bottom:2px;
    }

    /* ====== SIGNATURES ====== */
    .signatures{
      margin-top: 28px; display: table; width: 100%;
    }
    .sig-col{ display: table-cell; width:50%; vertical-align: bottom; text-align:center; }
    .sig-box{
      border:1px dashed #cfd3da; border-radius:10px; padding:20px 12px; min-height:110px; background:#fff;
    }
    .sig-line{ border-top:1px solid #111; width:72%; margin:14px auto 6px; }
    .sig-role{ font-weight:700; }
    .place-date{ margin-top:10px; font-style: italic; }

    /* ====== FOOT NOTES ====== */
    .footnote{ margin-top:14px; color:#6b7280; font-size: 11px; }

    /* QR placeholder */
    .qr{
      width:72px;height:72px;border:1px solid var(--line); border-radius:8px; display:inline-block;
      background: repeating-linear-gradient(45deg, #eee, #eee 3px, #fafafa 3px, #fafafa 6px);
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <div class="header">
    <div class="header-grid">
      <div class="header-col">
        <table>
          <tr>
            <td>
              <!-- Remplace par <img src="{{ public_path('logo.png') }}" height="64"> si tu as un logo -->
              <div class="logo-box">LOGO</div>
            </td>
            <td class="company-block">
              <div class="company-name">{{ $soumission->appelOffre->user->nomSociete ?? 'Votre Société' }}</div>
              <div class="company-meta">
                Adresse : Avenue Habib Bourguiba, Tunis – Tél : +216 XX XXX XXX – Email : contact@societe.com
              </div>
            </td>
          </tr>
        </table>
      </div>
      <div class="header-col title-side">
        <div class="doc-title">Contrat de prestation</div>
        <div class="small muted">Réf : CPS-{{ $soumission->idSoumission }} / {{ date('Y') }}</div>
        <div style="margin-top:6px;">
          <span class="badge {{ strtolower($soumission->appelOffre->statut) === 'publiee' ? 'green' : 'gray' }}">
            {{ strtoupper($soumission->appelOffre->statut) }}
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- META -->
  <div class="meta">
    <div class="chips">
      <div class="chip"><b>Titre</b> : {{ $soumission->appelOffre->titre }}</div>
      <div class="chip"><b>Budget</b> : {{ number_format($soumission->appelOffre->budget, 2, ',', ' ') }} TND</div>
      <div class="chip"><b>Début</b> : {{ $soumission->appelOffre->date_debut->format('d/m/Y') }}</div>
      <div class="chip"><b>Fin</b> : {{ $soumission->appelOffre->date_fin->format('d/m/Y') }}</div>
      <div class="chip"><b>Prestataire</b> : {{ $soumission->user->nom }} ({{ $soumission->user->nomSociete }})</div>
    </div>
  </div>

  <!-- PARTIES -->
  <div class="section">
    <h2>Entre les soussignés</h2>
    <div class="card">
      <div class="grid-2">
        <div class="col">
          <p class="label">Le Client</p>
          <p>{{ $soumission->appelOffre->user->nomSociete ?? '—' }}</p>
          <p class="muted small">Représenté par son responsable légal.</p>
        </div>
        <div class="col right">
          <p class="label">Le Prestataire</p>
          <p>{{ $soumission->user->nom }} – {{ $soumission->user->email }}</p>
          <p class="muted small">{{ $soumission->user->nomSociete }}</p>
        </div>
      </div>
    </div>
  </div>

  <!-- OBJET -->
  <div class="section">
    <h2>Objet du contrat</h2>
    <div class="card">
      <p>
        Le présent contrat a pour objet la réalisation de la prestation suivante :
        <b>{{ $soumission->appelOffre->titre }}</b>, telle que décrite dans l’appel d’offre.
      </p>
    </div>
  </div>

  <!-- DÉTAILS & DESCRIPTION -->
  <div class="section">
    <div class="grid-2">
      <div class="col">
        <h2>Détails de la soumission</h2>
        <div class="card">
          <p><span class="label">Prix proposé :</span> {{ number_format($soumission->prixPropose, 2, ',', ' ') }} TND</p>
          <p><span class="label">Délai :</span> {{ $soumission->temps_realisation }} jours</p>
          <div class="divider"></div>
          <p class="muted">{{ $soumission->description }}</p>
        </div>
      </div>
      <div class="col right">
        <h2>Description de l'appel d’offre</h2>
        <div class="card">
          <p class="muted">{{ $soumission->appelOffre->description }}</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ARTICLES -->
  <div class="section">
    <h2>Conditions générales</h2>
    <div class="card">
      <div class="article">
        <div class="article-title">Article 1 – Exécution</div>
        <p>Le Prestataire s’engage à exécuter la mission conformément aux règles de l’art et aux délais convenus.</p>
      </div>
      <div class="article">
        <div class="article-title">Article 2 – Paiement</div>
        <p>Le paiement du montant de <b>{{ number_format($soumission->prixPropose, 2, ',', ' ') }} TND</b> intervient selon les modalités fixées entre les parties.</p>
      </div>
      <div class="article">
        <div class="article-title">Article 3 – Confidentialité</div>
        <p>Toutes les informations échangées sont strictement confidentielles et ne sauraient être divulguées sans accord préalable.</p>
      </div>
      <div class="article">
        <div class="article-title">Article 4 – Propriété intellectuelle</div>
        <p>Les livrables restent la propriété du Client après règlement intégral, sauf stipulation contraire.</p>
      </div>
      <div class="article">
        <div class="article-title">Article 5 – Résiliation</div>
        <p>En cas de manquement grave, chacune des parties pourra résilier le présent contrat après mise en demeure restée sans effet.</p>
      </div>
      <div class="article">
        <div class="article-title">Article 6 – Droit applicable</div>
        <p>Le présent contrat est régi par le droit tunisien. Tout litige relève des tribunaux compétents de Tunis.</p>
      </div>
    </div>
  </div>

  <!-- LIEU / DATE -->
  <p class="place-date">Fait à Tunis, le {{ date('d/m/Y') }}.</p>

  <!-- SIGNATURES -->
  <div class="signatures">
    <div class="sig-col">
      <div class="sig-box">
        <div class="qr" style="float:right;"></div>
        <div class="sig-line"></div>
        <div class="sig-role">Signature du Prestataire</div>
        <div class="small muted">{{ $soumission->user->nom }} – {{ $soumission->user->nomSociete }}</div>
      </div>
    </div>
    <div class="sig-col">
      <div class="sig-box">
        <div class="qr" style="float:right;"></div>
        <div class="sig-line"></div>
        <div class="sig-role">Signature du Client</div>
        <div class="small muted">{{ $soumission->appelOffre->user->nomSociete ?? 'Société' }}</div>
      </div>
    </div>
  </div>

  <p class="footnote">
    Contrat généré automatiquement suite à la sélection de la soumission. Un duplicata signé est transmis par voie électronique.
  </p>

</body>
</html>
