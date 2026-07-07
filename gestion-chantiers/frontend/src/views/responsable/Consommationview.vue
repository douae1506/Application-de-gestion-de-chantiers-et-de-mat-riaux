<template>
  <div class="page" v-if="!loading && data">
    <button class="back-btn" @click="$router.push(`/responsable/projets/${data.projet.id}`)">← Retour au projet</button>

    <div class="header-card">
      <div>
        <span class="reference">{{ data.projet.reference }}</span>
        <h1>Consommation — {{ data.projet.nom }}</h1>
      </div>
    </div>

    <div class="stats-row">
      <div class="stat"><span class="stat-label">Budget du projet</span><span class="stat-value">{{ formatMoney(data.projet.budget) }}</span></div>
      <div class="stat"><span class="stat-label">Matériaux consommés</span><span class="stat-value">{{ formatMoney(data.resume.total_materiaux) }}</span></div>
      <div class="stat"><span class="stat-label">Dépenses annexes</span><span class="stat-value">{{ formatMoney(data.resume.total_depenses) }}</span></div>
      <div class="stat highlight"><span class="stat-label">Total consommé</span><span class="stat-value">{{ formatMoney(data.resume.total_consommation) }}</span></div>
    </div>

    <div class="progress-card">
      <div class="progress-header">
        <span>Budget consommé</span>
        <span class="pct" :class="{ over: data.resume.pct_consomme >= 100 }">{{ data.resume.pct_consomme }}%</span>
      </div>
      <div class="progress-bar-lg"><div class="progress-fill-lg" :class="{ over: data.resume.pct_consomme >= 100 }" :style="{ width: Math.min(data.resume.pct_consomme, 100) + '%' }" /></div>
      <p class="meta">Budget restant : {{ formatMoney(data.resume.budget_restant) }}</p>
    </div>

    <div class="two-col">
      <div class="card">
        <div class="card-header"><h2>Matériaux utilisés</h2></div>
        <div v-if="data.materiaux.length === 0" class="empty">Aucun matériau consommé pour ce projet.</div>
        <table v-else class="table">
          <thead>
            <tr><th>Produit</th><th>Qté</th><th>P.U.</th><th>Total</th><th>Date</th></tr>
          </thead>
          <tbody>
            <tr v-for="m in data.materiaux" :key="m.id">
              <td>{{ m.produit }}<span class="cat">{{ m.categorie }}</span></td>
              <td>{{ m.quantite }} {{ m.unite }}</td>
              <td>{{ formatMoney(m.prix_unitaire) }}</td>
              <td class="strong">{{ formatMoney(m.cout_total) }}</td>
              <td>{{ m.date_sortie }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="card">
        <div class="card-header"><h2>Dépenses annexes</h2></div>
        <div v-if="data.depenses.length === 0" class="empty">Aucune dépense enregistrée pour ce projet.</div>
        <table v-else class="table">
          <thead>
            <tr><th>Libellé</th><th>Montant</th><th>Date</th></tr>
          </thead>
          <tbody>
            <tr v-for="d in data.depenses" :key="d.id">
              <td>{{ d.nom }}<span class="cat" v-if="d.description">{{ d.description }}</span></td>
              <td class="strong">{{ formatMoney(d.montant) }}</td>
              <td>{{ d.date }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div v-else-if="loading" class="loading">Chargement...</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import responsableService from '@/services/responsableService'
import { useToast } from '@/composables/useToast'

const route  = useRoute()
const router = useRouter()
const toast  = useToast()

const loading = ref(true)
const data    = ref(null)

function formatMoney(v) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD', maximumFractionDigits: 0 }).format(v || 0)
}

onMounted(async () => {
  try {
    const res = await responsableService.getConsommation(route.params.id)
    data.value = res.data.data
  } catch (e) {
    toast.error("Impossible de charger la consommation de ce projet.")
    router.push('/responsable/projets')
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.back-btn { background: none; border: none; color: #0284c7; font-size: 0.85rem; cursor: pointer; margin-bottom: 1rem; padding: 0; font-weight: 500; }
.back-btn:hover { text-decoration: underline; }

.header-card { background: #fff; border: 1px solid #e0f2fe; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; }
.reference { font-size: 0.75rem; color: #639fab; font-weight: 600; }
h1 { font-size: 1.3rem; font-weight: 700; color: #0a2540; margin: 0.25rem 0; }

.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat { background: #f4f9fd; border: 1px solid #e0f2fe; border-radius: 10px; padding: 0.85rem 1rem; display: flex; flex-direction: column; gap: 0.25rem; }
.stat.highlight { background: #e0f2fe; border-color: #0284c7; }
.stat-label { font-size: 0.75rem; color: #639fab; }
.stat-value { font-size: 1rem; font-weight: 700; color: #0a2540; }

.progress-card { background: #fff; border: 1px solid #e0f2fe; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; }
.progress-header { display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; color: #0a2540; margin-bottom: 0.5rem; }
.pct.over { color: #dc2626; }
.progress-bar-lg { height: 10px; background: #e0f2fe; border-radius: 5px; overflow: hidden; }
.progress-fill-lg { height: 100%; background: #0284c7; border-radius: 5px; }
.progress-fill-lg.over { background: #dc2626; }
.meta { font-size: 0.8rem; color: #639fab; margin: 0.5rem 0 0; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
@media (max-width: 900px) { .two-col { grid-template-columns: 1fr; } }

.card { background: #fff; border: 1px solid #e0f2fe; border-radius: 12px; overflow: hidden; }
.card-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e0f2fe; }
.card-header h2 { font-size: 1rem; font-weight: 700; color: #0a2540; margin: 0; }
.empty { padding: 2rem; text-align: center; color: #639fab; font-size: 0.85rem; }

.table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.table th { text-align: left; padding: 0.6rem 1.25rem; color: #639fab; font-weight: 600; font-size: 0.72rem; text-transform: uppercase; border-bottom: 1px solid #f0f9ff; }
.table td { padding: 0.7rem 1.25rem; border-bottom: 1px solid #f0f9ff; color: #0a2540; }
.table tr:last-child td { border-bottom: none; }
.cat { display: block; font-size: 0.72rem; color: #639fab; }
.strong { font-weight: 700; }
</style>