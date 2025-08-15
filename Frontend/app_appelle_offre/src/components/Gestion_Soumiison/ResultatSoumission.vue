<template>
  <navbar />
  <div class="container py-4">
    <!-- Titre -->
    <div class="header-row">
      <div class="title-wrap">
        <i class="bi bi-bar-chart-line-fill"></i>
        <h2 class="page-title">Analyse IA des Soumissions</h2>
      </div>
      <div class="meta" v-if="!loading && !error">
        <span class="meta-pill">{{ items.length }} résultat(s)</span>
      </div>
    </div>

    <!-- Bandeau Transparence -->
    <div class="info-banner" v-if="appel">
      <div class="ib-left">
        <i class="bi bi-shield-check"></i>
      </div>
      <div class="ib-right">
        <p class="ib-title">
          Cette page présente, en toute transparence, l’ensemble des soumissions déposées
          par les prestataires pour la société
          <strong class="text-orange">{{appel.user?.nomSociete }}</strong>.
        </p>
        <p class="mb-1">
          Les critères et explications affichés reflètent le processus d’évaluation appliqué.
        </p>
    <p class="ib-help">
  En cas d’absence d’explication pour une soumission, merci de contacter le représentant de l’entreprise :
  <strong>{{ appel.user?.nom }} {{ appel.user?.prenom }}</strong>
  (<a :href="`mailto:${appel.user?.email}`" class="text-orange">{{ appel.user?.email }}</a>)
  ou via notre 
  <router-link :to="{ name: 'Chat' }" class="text-orange fw-bold">chat en ligne</router-link>.
  <br />
  <span class="fst-italic text-muted">
    ℹ️ Les explications affichées peuvent être générées automatiquement par notre système d’analyse IA
    ou rédigées manuellement par le représentant.
  </span>
</p>


      </div>
    </div>

    <!-- Erreur -->
    <div v-if="error" class="state state-error">
      <i class="bi bi-exclamation-triangle"></i>
      <div>
        <h5>Impossible de charger les résultats</h5>
        <p class="mb-0">{{ error }}</p>
      </div>
      <button class="btn-ghost-orange" @click="fetchAll">Réessayer</button>
    </div>

    <!-- Skeleton -->
    <div v-else-if="loading" class="timeline">
      <div class="timeline-item" v-for="n in 3" :key="n">
        <div class="timeline-dot skeleton-dot"></div>
        <div class="timeline-content skeleton-card">
          <div class="skeleton skeleton-title"></div>
          <div class="skeleton skeleton-line"></div>
          <div class="skeleton skeleton-line"></div>
          <div class="skeleton skeleton-chip"></div>
        </div>
      </div>
    </div>

    <!-- Vide -->
    <div v-else-if="!items.length" class="state state-empty">
      <i class="bi bi-inbox"></i>
      <div>
        <h5>Aucune analyse disponible</h5>
        <p class="mb-0">Vous n’avez pas encore de soumissions analysées pour cet appel.</p>
      </div>
    </div>

    <!-- Timeline -->
    <div v-else class="timeline">
      <div v-for="s in items" :key="s.idSoumission" class="timeline-item">
        <!-- Point -->
        <div class="timeline-dot" :class="s.verdict === 'acceptée' ? 'dot-green' : 'dot-red'"></div>

        <!-- Carte -->
        <div class="timeline-content card-ia">
          <div class="card-head">
            <div class="title">
              <span class="hash">#{{ s.idSoumission }}</span>
              <span class="card-title">Soumission</span>
            </div>
            <span class="badge-verd" :class="s.verdict === 'acceptée' ? 'ok' : 'ko'">
              <i :class="s.verdict === 'acceptée' ? 'bi bi-check-circle' : 'bi bi-x-circle'"></i>
              {{ capitalize(s.verdict) }}
            </span>
          </div>

          <div class="kv">
            <div class="kv-row">
              <i class="bi bi-cash-coin"></i>
              <span class="kv-label">Prix proposé</span>
              <span class="kv-value">{{ formatDT(s.prix) }}</span>
            </div>
            <div class="kv-row">
              <i class="bi bi-hourglass-split"></i>
              <span class="kv-label">Délai</span>
              <span class="kv-value">{{ formatDays(s.delai) }}</span>
            </div>
          </div>

          <div class="chips-block">
            <span class="chips-label">Raisons</span>
            <div class="chips">
              <span v-if="!s.categories || !s.categories.length" class="chip chip-muted">aucune</span>
              <span v-else v-for="(c, idx) in s.categories" :key="idx" class="chip">
                {{ prettyReason(c) }}
              </span>
            </div>
          </div>

          <blockquote v-if="s.public_phrase" class="public-phrase">
            “{{ s.public_phrase }}”
          </blockquote>
        </div>
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/Http/api";
import Footer from "../Footer.vue";
import Navbar from "../Navbar.vue";
const props = defineProps({ idAppel: { type: Number, required: true } });

const appel = ref(null);      // <-- infos appel (société + représentant)
const items = ref([]);
const loading = ref(true);
const error = ref("");

const capitalize = (v) => (v ? v.charAt(0).toUpperCase() + v.slice(1) : v);
const formatDT = (n) => (n || n === 0) ? `${Number(n).toLocaleString('fr-FR')} DT` : '—';
const formatDays = (n) => (n || n === 0) ? `${Number(n).toLocaleString('fr-FR')} jour${Number(n) > 1 ? 's' : ''}` : '—';
const prettyReason = (r) => ({ budget_over:'Dépassement du budget', incomplete_file:'Dossier incomplet', timing_mismatch:'Délai non adapté' }[r] || r);

async function fetchAppel() {
  // essaie /appels/{id} ; sinon /appels/show/{id} selon ton API
  const res = await api.get(`/appels/${props.idAppel}`);
  // attendu: { idAppel, titre, budget, nomSociete? , user:{ nom, prenom, nomSociete } ... }
  appel.value = res.data;
}

async function fetchItems() {
  const res = await api.get(`/explications/appel/${props.idAppel}`);
  const list = Array.isArray(res.data) ? res.data : (res.data?.data ?? []);
  items.value = list.map(s => ({
    ...s,
    categories: Array.isArray(s.categories)
      ? s.categories
      : (() => { try { return JSON.parse(s.categories); } catch { return []; } })()
  }));
}

async function fetchAll() {
  loading.value = true;
  error.value = "";
  try {
    await Promise.all([fetchAppel(), fetchItems()]);
  } catch (e) {
    error.value = e?.response?.data?.message || e?.message || "Erreur inconnue";
  } finally {
    loading.value = false;
  }
}

onMounted(fetchAll);
</script>

<style scoped>
/* ====== Header ====== */
.header-row{ display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom: 14px; }
.title-wrap{ display:flex; align-items:center; gap:10px; }
.page-title{ color:#111827; font-weight:800; letter-spacing:.3px; margin:0; }
.title-wrap i{ font-size: 28px; color:#f97316; }
.meta{ display:flex; align-items:center; gap:8px; }
.meta-pill{ display:inline-block; padding:6px 10px; border-radius:999px; font-size:12px; background:#fff1e9; color:#9a3412; border:1px solid #ffd5bf; }
.text-orange{ color:#f97316; }

/* ====== Info banner ====== */
.info-banner{
  display:flex; gap:12px; border:1px solid #ffe1cc; background:#fff7ed;
  padding:14px; border-radius:14px; box-shadow:0 6px 22px rgba(16,24,40,.05); margin-bottom:16px;
}
.ib-left i{ font-size:24px; color:#f97316; }
.ib-title{ margin-bottom:6px; font-weight:600; }
.ib-help{ color:#4b5563; font-style:italic; margin-bottom:0; }

/* ====== States ====== */
.state{ display:flex; align-items:center; gap:14px; border:1px solid #f1f5f9; background:#fff; padding:16px; border-radius:14px; box-shadow:0 6px 22px rgba(16,24,40,.06); }
.state i{ font-size:22px; }
.state-empty i{ color:#475569; }
.state-error{ border-color:#fee2e2; background:#fff7f7; }
.state-error i{ color:#ef4444; }
.btn-ghost-orange{ margin-left:auto; border:1.5px solid #f97316; color:#f97316; background:#fff; border-radius: 999px; padding:8px 14px; font-weight:600; }
.btn-ghost-orange:hover{ background:#f97316; color:#fff; box-shadow:0 10px 18px rgba(249,115,22,.18); }

/* ====== Timeline ====== */
.timeline{ position:relative; margin-left:20px; padding-left:20px; }
.timeline::before{ content:""; position:absolute; left:0; top:0; bottom:0; width:3px; background: linear-gradient(#e5e7eb, #f3f4f6); border-radius: 3px; }
.timeline-item{ position:relative; margin-bottom:22px; }
.timeline-dot{ position:absolute; left:-11px; top:12px; width:18px; height:18px; border-radius:50%; border:3px solid #fff; box-shadow:0 0 0 3px #e5e7eb; }
.dot-green{ background:#16a34a; box-shadow:0 0 0 3px #22c55e44; }
.dot-red{ background:#ef4444; box-shadow:0 0 0 3px #ef444444; }

/* ====== Card ====== */
.card-ia{ background:#fff; padding:16px; border-radius:16px; box-shadow:0 8px 28px rgba(16,24,40,.06); border:1px solid #f1f5f9; transition: transform .18s ease, box-shadow .18s ease; }
.card-ia:hover{ transform: translateY(-2px); box-shadow:0 12px 30px rgba(16,24,40,.08); }

.card-head{ display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px; }
.title{ display:flex; align-items:baseline; gap:10px; }
.card-title{ font-weight:700; color:#111827; }
.hash{ display:inline-block; padding:2px 8px; border-radius:999px; font-size:12px; background:#f3f4f6; color:#374151; border:1px solid #e5e7eb; }

.badge-verd{ display:inline-flex; align-items:center; gap:6px; font-weight:700; font-size:12px; padding:6px 10px; border-radius:999px; letter-spacing:.3px; }
.badge-verd.ok{ background:#dcffe6; color:#166534; border:1px solid #b7f7c4; }
.badge-verd.ko{ background:#ffe3e3; color:#991b1b; border:1px solid #ffc7c7; }

/* key-value */
.kv{ display:grid; grid-template-columns:1fr 1fr; gap:8px 16px; margin:10px 0 6px; }
.kv-row{ display:flex; align-items:center; gap:8px; }
.kv i{ color:#f97316; }
.kv-label{ color:#6b7280; }
.kv-value{ font-weight:700; color:#111827; }

/* Chips */
.chips-block{ margin-top:8px; }
.chips-label{ font-weight:700; font-size:.95rem; margin-right:8px; }
.chips{ display:flex; flex-wrap:wrap; gap:8px; margin-top:6px; }
.chip{ display:inline-block; padding:6px 10px; border-radius:999px; font-size:12px; background:#f6f7fb; border:1px solid #e5e7eb; color:#1f2937; }
.chip-muted{ background:#fafafa; color:#6b7280; }

/* Phrase publique */
.public-phrase{ margin:10px 0 0; background:#fff7ed; border-left:4px solid #f97316; padding:10px 12px; border-radius:10px; color:#0f172a; font-style:italic; }

/* ====== Skeleton ====== */
.skeleton-card{ padding:16px; border-radius:16px; background:#fff; box-shadow:0 8px 28px rgba(16,24,40,.06); }
.skeleton-dot{ animation: pulse 1.2s infinite ease-in-out; background:#e5e7eb; }
.skeleton{ position:relative; overflow:hidden; background:#f3f4f6; border-radius:8px; }
.skeleton::after{ content:""; position:absolute; inset:0; background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.6) 50%, rgba(255,255,255,0) 100%); transform: translateX(-100%); animation: shimmer 1.4s infinite; }
.skeleton-title{ height:20px; width:40%; margin-bottom:10px; }
.skeleton-line{ height:14px; width:70%; margin-bottom:8px; }
.skeleton-chip{ height:28px; width:120px; border-radius:999px; }
@keyframes shimmer { 100% { transform: translateX(100%); } }
@keyframes pulse { 0%,100%{opacity:.6} 50%{opacity:1} }
@media (max-width: 640px){ .kv{ grid-template-columns:1fr; } }
</style>
