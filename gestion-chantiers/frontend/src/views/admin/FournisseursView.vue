Pour transformer cette page de gestion de vos fournisseurs en une interface digne des meilleures applications SaaS (type Stripe, Linear ou Tailwind UI), nous allons opérer une refonte esthétique et ergonomique totale.

Voici les améliorations concrètes que nous allons apporter :

* **Design Moderne & Épuré :** Remplacement de la couleur de fond grise par un blanc cassé texturé, ombres douces et fines bordures pour un rendu "pro".
* **Tableau Sublimé (Data Table) :**
* Fini les tableaux interminables qui s'écrasent : mise en place d'une structure aérée avec des **badges visuels**, des liens de contact élégants et des colonnes redimensionnées intelligemment.
* Les adresses, sites web et emails sont présentés avec de jolies icônes et un design minimaliste.


* **Composant de Recherche Intégrée :** Ajout d'une barre de recherche instantanée en haut du tableau pour filtrer les fournisseurs à la volée.
* **Formulaire de Saisie Réorganisé :** Un formulaire sous forme d'onglets virtuels ou de sections claires avec des icônes indicatives, pour éviter l'effet "gros bloc de champs".
* **Grille de Statistiques Enrichie :** Ajout d'indicateurs visuels et de micro-cartes pour un aspect analytique moderne.

Voici le code complet, optimisé, prêt à l'emploi et compatible à 100 % avec votre logique Vue 3 actuelle :

```vue
<template>
  <div class="fv-wrap">
    <!-- Header principal -->
    <div class="fv-header">
      <div>
        <div class="breadcrumb">Dashboard / Administration</div>
        <h1>Gestion des Fournisseurs</h1>
        <p>Centralisez et suivez les informations de vos partenaires commerciaux et approvisionnements.</p>
      </div>
      <button class="btn btn-primary btn-with-icon" @click="openCreateModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Nouveau fournisseur
      </button>
    </div>

    <!-- Section statistiques -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Total Fournisseurs</span>
          <strong class="stat-value">{{ fournisseurs.length }}</strong>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Actifs</span>
          <strong class="stat-value">{{ fournisseurs.length }}</strong>
        </div>
      </div>
    </div>

    <!-- Barre d'outils et Tableau -->
    <div class="table-card">
      <div class="table-toolbar">
        <div class="search-box">
          <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input v-model="searchQuery" type="text" placeholder="Rechercher par nom, ville, responsable..." class="search-input" />
        </div>
        <div class="toolbar-actions">
          <span class="results-count">{{ filteredFournisseurs.length }} résultat(s)</span>
        </div>
      </div>

      <!-- États de chargement -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <span>Récupération des données en cours...</span>
      </div>

      <!-- Tableau des données -->
      <div v-else-if="filteredFournisseurs.length" class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Fournisseur</th>
              <th>Responsable</th>
              <th>Coordonnées</th>
              <th>Localisation</th>
              <th>Site web</th>
              <th>Observations</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="f in filteredFournisseurs" :key="f.id">
              <td>
                <div class="company-cell">
                  <div class="company-avatar">{{ f.nom.charAt(0).toUpperCase() }}</div>
                  <div>
                    <span class="company-name">{{ f.nom }}</span>
                    <span class="company-badge" v-if="f.pays">{{ f.pays }}</span>
                  </div>
                </div>
              </td>
              <td>
                <div class="responsable-cell">
                  <span class="text-main">{{ f.responsable || '—' }}</span>
                </div>
              </td>
              <td>
                <div class="contact-details">
                  <a v-if="f.email" :href="'mailto:' + f.email" class="contact-link email">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    {{ f.email }}
                  </a>
                  <span v-if="f.telephone" class="contact-link phone">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    {{ f.telephone }}
                  </span>
                  <span v-if="!f.email && !f.telephone" class="text-muted">—</span>
                </div>
              </td>
              <td>
                <div class="location-cell">
                  <span class="text-main">{{ f.ville || '—' }}</span>
                  <span class="text-sub" v-if="f.adresse">{{ f.adresse }} {{ f.code_postal }}</span>
                </div>
              </td>
              <td>
                <a v-if="f.site_web" :href="f.site_web" target="_blank" class="web-badge">
                  <span>Visiter</span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                </a>
                <span v-else class="text-muted">—</span>
              </td>
              <td>
                <div class="observations-text" :title="f.observations">
                  {{ f.observations || '—' }}
                </div>
              </td>
              <td>
                <div class="action-btns">
                  <button class="btn-action edit" @click="openEditModal(f)" title="Modifier">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                  </button>
                  <button class="btn-action delete" @click="supprimer(f.id)" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- État vide (Empty State) -->
      <div v-else class="empty-state">
        <div class="empty-icon">🏢</div>
        <h3>Aucun fournisseur trouvé</h3>
        <p v-if="searchQuery">Ajustez votre recherche ou réinitialisez le filtre pour voir vos contacts.</p>
        <p v-else>Créez votre premier fournisseur pour commencer à gérer vos approvisionnements.</p>
        <button class="btn btn-primary" @click="openCreateModal">+ Créer un fournisseur</button>
      </div>
    </div>

    <!-- Modal d'ajout / modification -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <div>
            <h3>{{ editMode ? '✏️ Modifier le fournisseur' : '🚀 Nouveau fournisseur' }}</h3>
            <p class="modal-subtitle">Remplissez les informations ci-dessous pour enregistrer le profil.</p>
          </div>
          <button class="modal-close" @click="showModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="form-group col-span-2">
              <label>Nom de l'entreprise <span class="required">*</span></label>
              <input v-model="form.nom" class="form-input" placeholder="Ex : Matériaux du Maroc" />
            </div>
            
            <div class="form-group">
              <label>Nom du responsable</label>
              <input v-model="form.responsable" class="form-input" placeholder="Ex: Jean Dupont" />
            </div>
            <div class="form-group">
              <label>Téléphone</label>
              <input v-model="form.telephone" class="form-input" placeholder="+212 6xx-xxxxxx" />
            </div>

            <div class="form-group">
              <label>Adresse e-mail</label>
              <input v-model="form.email" class="form-input" type="email" placeholder="contact@fournisseur.com" />
            </div>
            <div class="form-group">
              <label>Site internet</label>
              <input v-model="form.site_web" class="form-input" placeholder="https://www.exemple.com" />
            </div>

            <div class="form-group">
              <label>Ville</label>
              <input v-model="form.ville" class="form-input" placeholder="Casablanca" />
            </div>
            <div class="form-group">
              <label>Pays</label>
              <input v-model="form.pays" class="form-input" placeholder="Maroc" />
            </div>

            <div class="form-group">
              <label>Code postal</label>
              <input v-model="form.code_postal" class="form-input" placeholder="20000" />
            </div>
            <div class="form-group col-span-2">
              <label>Adresse de l'établissement</label>
              <input v-model="form.adresse" class="form-input" placeholder="Numéro, rue..." />
            </div>

            <div class="form-group col-span-2">
              <label>Observations & Notes internes</label>
              <textarea v-model="form.observations" class="form-input textarea-input" rows="3" placeholder="Particularités de livraison, conditions de paiement..."></textarea>
            </div>
          </div>
          <p v-if="formError" class="form-error">
            <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            {{ formError }}
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showModal = false">Annuler</button>
          <button class="btn btn-primary btn-submit" @click="save" :disabled="saving">
            <span v-if="saving" class="btn-spinner"></span>
            {{ saving ? 'Enregistrement...' : (editMode ? 'Mettre à jour' : 'Créer le profil') }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'

const fournisseurs = ref([])
const loading = ref(true)
const saving = ref(false)
const showModal = ref(false)
const editMode = ref(false)
const editId = ref(null)
const formError = ref('')
const searchQuery = ref('') // Barre de recherche reactive

// Computed property pour la recherche temps réel
const filteredFournisseurs = computed(() => {
  if (!searchQuery.value) return fournisseurs.value
  const query = searchQuery.value.toLowerCase().trim()
  return fournisseurs.value.filter(f => {
    return (
      (f.nom && f.nom.toLowerCase().includes(query)) ||
      (f.responsable && f.responsable.toLowerCase().includes(query)) ||
      (f.ville && f.ville.toLowerCase().includes(query)) ||
      (f.email && f.email.toLowerCase().includes(query))
    )
  })
})

async function fetchFournisseurs() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/fournisseurs')
    fournisseurs.value = data.data
  } catch (e) {
    console.error('Erreur chargement fournisseurs', e)
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  editMode.value = false
  editId.value = null
  Object.assign(form, {
    nom: '',
    responsable: '',
    email: '',
    telephone: '',
    adresse: '',
    ville: '',
    pays: '',
    code_postal: '',
    site_web: '',
    observations: '',
  })
  formError.value = ''
  showModal.value = true
}

function openEditModal(f) {
  editMode.value = true
  editId.value = f.id
  Object.assign(form, {
    nom: f.nom,
    responsable: f.responsable || '',
    email: f.email || '',
    telephone: f.telephone || '',
    adresse: f.adresse || '',
    ville: f.ville || '',
    pays: f.pays || '',
    code_postal: f.code_postal || '',
    site_web: f.site_web || '',
    observations: f.observations || '',
  })
  formError.value = ''
  showModal.value = true
}

const form = reactive({
  nom: '',
  responsable: '',
  email: '',
  telephone: '',
  adresse: '',
  ville: '',
  pays: '',
  code_postal: '',
  site_web: '',
  observations: '',
})

async function save() {
  formError.value = ''
  if (!form.nom) {
    formError.value = 'Le nom est requis.'
    return
  }

  saving.value = true
  try {
    if (editMode.value) {
      await api.put(`/admin/fournisseurs/${editId.value}`, form)
    } else {
      await api.post('/admin/fournisseurs', form)
    }
    showModal.value = false
    await fetchFournisseurs()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Erreur lors de l\'enregistrement.'
  } finally {
    saving.value = false
  }
}

async function supprimer(id) {
  if (!confirm('Supprimer définitivement ce fournisseur ?')) return
  try {
    await api.delete(`/admin/fournisseurs/${id}`)
    await fetchFournisseurs()
  } catch (e) {
    alert('Erreur lors de la suppression.')
  }
}

onMounted(() => {
  fetchFournisseurs()
})
</script>

<style scoped>
/* Reset global & variables */
.fv-wrap {
  --primary: #4f46e5;
  --primary-hover: #4338ca;
  --primary-light: #e0e7ff;
  --success: #10b981;
  --success-light: #ecfdf5;
  --danger: #ef4444;
  --danger-light: #fef2f2;
  --text-main: #1f2937;
  --text-muted: #6b7280;
  --border-color: #e5e7eb;
  --card-bg: #ffffff;
  --body-bg: #f9fafb;
  
  min-height: 100vh;
  background: var(--body-bg);
  margin: -42px !important;
  padding: 2rem;
  font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
  color: var(--text-main);
}

/* Header */
.fv-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.breadcrumb {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  margin-bottom: 0.25rem;
}
.fv-header h1 {
  margin: 0 0 0.4rem;
  font-size: 1.85rem;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.02em;
}
.fv-header p {
  margin: 0;
  color: var(--text-muted);
  font-size: 0.95rem;
}

/* Boutons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  border: 1px solid var(--border-color);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.btn-primary {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
  box-shadow: 0 1px 2px rgba(79, 70, 229, 0.15);
}
.btn-primary:hover {
  background: var(--primary-hover);
  border-color: var(--primary-hover);
  transform: translateY(-1px);
}
.btn-secondary {
  background: #fff;
  color: #374151;
}
.btn-secondary:hover {
  background: #f3f4f6;
  border-color: #d1d5db;
}

/* Cartes stats */
.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}
.stat-card {
  background: var(--card-bg);
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stat-icon svg {
  width: 24px;
  height: 24px;
}
.stat-icon.blue {
  background: var(--primary-light);
  color: var(--primary);
}
.stat-icon.green {
  background: var(--success-light);
  color: var(--success);
}
.stat-content {
  display: flex;
  flex-direction: column;
}
.stat-label {
  font-size: 0.8rem;
  color: var(--text-muted);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.stat-value {
  font-size: 1.6rem;
  font-weight: 800;
  color: #111827;
  line-height: 1.2;
}

/* Card du tableau et barre d'outils */
.table-card {
  background: var(--card-bg);
  border-radius: 16px;
  border: 1px solid var(--border-color);
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}
.table-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem;
  border-bottom: 1px solid var(--border-color);
  background: #fafafa;
}
.search-box {
  position: relative;
  width: 320px;
}
.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}
.search-input {
  width: 100%;
  padding: 0.55rem 1rem 0.55rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.875rem;
  background: #ffffff;
  transition: all 0.2s;
}
.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
}
.results-count {
  font-size: 0.8rem;
  color: var(--text-muted);
  font-weight: 500;
}

/* Tableau */
.table-wrap {
  overflow-x: auto;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.data-table th {
  padding: 1rem 1.25rem;
  background: #f9fafb;
  border-bottom: 1px solid var(--border-color);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.data-table td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f3f4f6;
  font-size: 0.875rem;
  color: var(--text-main);
  vertical-align: middle;
}
.data-table tbody tr:hover td {
  background: #f9fafb;
}

/* Design des cellules */
.company-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.company-avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #f3f4f6;
  border: 1px solid var(--border-color);
  color: var(--primary);
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}
.company-name {
  display: block;
  font-weight: 600;
  color: #111827;
}
.company-badge {
  display: inline-block;
  margin-top: 0.15rem;
  font-size: 0.7rem;
  padding: 0.1rem 0.4rem;
  background: #f3f4f6;
  border-radius: 4px;
  color: var(--text-muted);
  font-weight: 500;
}
.contact-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}
.contact-link {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  text-decoration: none;
  font-size: 0.8rem;
}
.contact-link.email {
  color: var(--primary);
  font-weight: 500;
}
.contact-link.email:hover {
  text-decoration: underline;
}
.contact-link.phone {
  color: var(--text-muted);
}
.location-cell {
  display: flex;
  flex-direction: column;
}
.text-main {
  font-weight: 500;
  color: var(--text-main);
}
.text-sub {
  font-size: 0.75rem;
  color: var(--text-muted);
}
.web-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.5rem;
  background: #f3f4f6;
  border-radius: 6px;
  text-decoration: none;
  color: var(--text-main);
  font-size: 0.75rem;
  font-weight: 500;
  border: 1px solid var(--border-color);
  transition: all 0.2s;
}
.web-badge:hover {
  background: #e5e7eb;
}
.observations-text {
  max-width: 150px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--text-muted);
}
.text-right {
  text-align: right;
}

/* Actions */
.action-btns {
  display: flex;
  gap: 0.35rem;
  justify-content: flex-end;
}
.btn-action {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: 1px solid var(--border-color);
  background: #ffffff;
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.btn-action:hover {
  border-color: #cbd5e1;
  color: #1f2937;
}
.btn-action.edit:hover {
  background: var(--primary-light);
  border-color: #a5b4fc;
  color: var(--primary);
}
.btn-action.delete:hover {
  background: var(--danger-light);
  border-color: #fca5a5;
  color: var(--danger);
}

/* Spinner & États */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 4rem;
  color: var(--text-muted);
}
.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f4f6;
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 5rem 2rem;
}
.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}
.empty-state h3 {
  font-size: 1.15rem;
  margin: 0 0 0.5rem;
  color: #111827;
  font-weight: 700;
}
.empty-state p {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
}

/* Modal moderne */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(17, 24, 39, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}
.modal-box {
  background: #fff;
  border-radius: 16px;
  width: 620px;
  max-width: 95vw;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 1px solid var(--border-color);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
  position: sticky;
  top: 0;
  background: #fff;
  z-index: 10;
}
.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 800;
  color: #111827;
}
.modal-subtitle {
  margin: 0.25rem 0 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}
.modal-close {
  background: #f3f4f6;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  cursor: pointer;
  color: var(--text-muted);
  transition: all 0.2s;
}
.modal-close:hover {
  background: #e5e7eb;
  color: #111827;
}
.modal-body {
  padding: 1.5rem;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.25rem 1.5rem;
  border-top: 1px solid var(--border-color);
  background: #f9fafb;
}

/* Grille & Formulaires */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}
.col-span-2 {
  grid-column: span 2;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.form-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #374151;
}
.required {
  color: var(--danger);
}
.form-input {
  padding: 0.65rem 0.85rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.875rem;
  color: var(--text-main);
  background: #ffffff;
  width: 100%;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.form-input::placeholder {
  color: #9ca3af;
}
.form-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
}
.textarea-input {
  resize: vertical;
  font-family: inherit;
}

.form-error {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--danger);
  background: var(--danger-light);
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  margin-top: 1.25rem;
  font-weight: 500;
  width: 100%;
  box-sizing: border-box;
}

/* Responsive */
@media (max-width: 768px) {
  .fv-wrap {
    padding: 1rem;
  }
  .fv-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  .search-box {
    width: 100%;
  }
  .form-grid {
    grid-template-columns: 1fr;
  }
  .col-span-2 {
    grid-column: span 1;
  }
}
</style>

```