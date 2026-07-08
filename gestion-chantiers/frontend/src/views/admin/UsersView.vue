<template>
  <div class="users-page">

    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Gestion des utilisateurs</h1>
        <p>{{ filteredUsers.length }} utilisateur{{ filteredUsers.length > 1 ? 's' : '' }} trouvé{{ filteredUsers.length > 1 ? 's' : '' }}</p>
      </div>
      <button class="btn-primary" @click="openModal('create')">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
        Ajouter un utilisateur
      </button>
    </div>

    <!-- Stats -->
    <div class="stats-bar">
      <div class="stat-pill stat-pill--all" :class="{ active: filterRole === '' }" @click="filterRole = ''">
        <span class="stat-dot"></span> Tous <strong>{{ users.length }}</strong>
      </div>
      <div class="stat-pill stat-pill--admin" :class="{ active: filterRole === 'admin' }" @click="filterRole = 'admin'">
        <span class="stat-dot"></span> Admins <strong>{{ countByRole('admin') }}</strong>
      </div>
      <div class="stat-pill stat-pill--responsable" :class="{ active: filterRole === 'responsable' }" @click="filterRole = 'responsable'">
        <span class="stat-dot"></span> Responsables <strong>{{ countByRole('responsable') }}</strong>
      </div>
      <div class="stat-pill stat-pill--chef" :class="{ active: filterRole === 'chef_projet' }" @click="filterRole = 'chef_projet'">
        <span class="stat-dot"></span> Chefs projet <strong>{{ countByRole('chef_projet') }}</strong>
      </div>
      <div class="stat-pill stat-pill--mag" :class="{ active: filterRole === 'magasinier' }" @click="filterRole = 'magasinier'">
        <span class="stat-dot"></span> Magasiniers <strong>{{ countByRole('magasinier') }}</strong>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="search-box">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
        <input v-model="search" type="text" placeholder="Rechercher par nom, email..." />
        <button v-if="search" class="clear-btn" @click="search = ''">×</button>
      </div>
      <div class="toolbar-right">
        <select v-model="filterStatus" class="filter-select">
          <option value="">Tous les statuts</option>
          <option value="true">Actifs</option>
          <option value="false">Inactifs</option>
        </select>
        <div class="view-toggle">
          <button :class="{ active: view === 'table' }" @click="view = 'table'">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
          </button>
          <button :class="{ active: view === 'cards' }" @click="view = 'cards'">
            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- TABLE VIEW -->
    <div v-if="view === 'table'" class="table-card">
      <table class="users-table">
        <thead>
          <tr>
            <th><input type="checkbox" @change="toggleAll" :checked="allSelected" /></th>
            <th>Utilisateur</th>
            <th>Rôle</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Dernière connexion</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in paginatedUsers" :key="u.id" @click="openViewModal(u)" :class="{ selected: selected.includes(u.id) }">
            <td><input type="checkbox" :value="u.id" v-model="selected" /></td>
            <td>
              <div class="user-cell">
                <div class="avatar" :style="{ background: avatarColor(u.role) }">
                  <img v-if="u.photo_profil" :src="getPhotoUrl(u.photo_profil)" alt="photo profil" />
                  <span v-else>{{ initials(u) }}</span>
                </div>
                <div>
                  <p class="user-name">{{ u.prenom }} {{ u.nom }}</p>
                  <p class="user-email">{{ u.email }}</p>
                </div>
              </div>
            </td>
            <td><span class="role-badge" :class="'role-' + u.role">{{ roleLabel(u.role) }}</span></td>
            <td><span class="email-cell">{{ u.email }}</span></td>
            <td>
              <p class="contact-line" v-if="u.telephone_mobile">📱 {{ u.telephone_mobile }}</p>
              <p class="contact-line muted" v-else>—</p>
            </td>
            <td>
              <span class="date-text">{{ getLastLogin(u) }}</span></td>
            <td>
              <button class="status-toggle" :class="u.est_actif ? 'active' : 'inactive'" @click.stop="toggleStatus(u)">
                <span class="status-dot"></span>
                {{ u.est_actif ? 'Actif' : 'Inactif' }}
              </button>
            </td>
            <td>
              <div class="action-btns">
                <button class="action-btn edit" @click.stop="openModal('edit', u)" title="Modifier">
                  <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                </button>
                <button class="action-btn delete" @click.stop="confirmDelete(u)" title="Supprimer">
                  <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredUsers.length === 0">
            <td colspan="8" class="empty-row">
              <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                <p>Aucun utilisateur trouvé</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="pagination">
        <span class="page-info">{{ (currentPage-1)*perPage+1 }}–{{ Math.min(currentPage*perPage, filteredUsers.length) }} sur {{ filteredUsers.length }}</span>
        <div class="page-btns">
          <button @click="currentPage--" :disabled="currentPage === 1">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
          </button>
          <button v-for="p in totalPages" :key="p" :class="{ current: p === currentPage }" @click="currentPage = p">{{ p }}</button>
          <button @click="currentPage++" :disabled="currentPage === totalPages">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- CARDS VIEW -->
    <div v-else class="user-cards">
      <div class="user-card" v-for="u in paginatedUsers" :key="u.id" @click="openViewModal(u)">
        <div class="uc-header" :style="{ background: cardGradient(u.role) }">
          <div class="uc-avatar">
            <img v-if="u.photo_profil" :src="getPhotoUrl(u.photo_profil)" alt="photo profil" />
            <span v-else>{{ initials(u) }}</span>
          </div>
          <span class="uc-status" :class="u.est_actif ? 'active' : 'inactive'">{{ u.est_actif ? 'Actif' : 'Inactif' }}</span>
        </div>
        <div class="uc-body">
          <h4>{{ u.prenom }} {{ u.nom }}</h4>
          <p class="uc-email">{{ u.email }}</p>
          <span class="role-badge" :class="'role-' + u.role">{{ roleLabel(u.role) }}</span>
          <div class="uc-actions">
            <button class="action-btn edit" @click.stop="openModal('edit', u)"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg></button>
            <button class="action-btn delete" @click.stop="confirmDelete(u)"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg></button>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         MODAL CREATE / EDIT
    ══════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <!-- Modal header -->
          <div class="modal-header">
            <div class="modal-title-area">
              <div class="modal-icon" :style="{ background: modalMode === 'create' ? 'linear-gradient(135deg,#6366f1,#8b5cf6)' : 'linear-gradient(135deg,#f857a6,#ff5858)' }">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path v-if="modalMode === 'create'" fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  <path v-else d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                </svg>
              </div>
              <div>
                <h2>{{ modalMode === 'create' ? 'Ajouter un utilisateur' : 'Modifier l\'utilisateur' }}</h2>
                <p>{{ modalMode === 'create' ? 'Créez un nouveau compte utilisateur' : `Modifier le profil de ${form.prenom} ${form.nom}` }}</p>
              </div>
            </div>
            <button class="modal-close" @click="closeModal">
              <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
          </div>

          <!-- Modal body -->
          <div class="modal-body">
            <div v-if="formError" class="form-alert">⚠ {{ formError }}</div>

            <!-- Nom / Prénom -->
            <div class="form-row">
              <div class="form-field" :class="{ error: formErrors.prenom }">
                <label>Prénom <span class="req">*</span></label>
                <input v-model="form.prenom" type="text" placeholder="Jean" />
                <span v-if="formErrors.prenom" class="fe">{{ formErrors.prenom }}</span>
              </div>
              <div class="form-field" :class="{ error: formErrors.nom }">
                <label>Nom <span class="req">*</span></label>
                <input v-model="form.nom" type="text" placeholder="Dupont" />
                <span v-if="formErrors.nom" class="fe">{{ formErrors.nom }}</span>
              </div>
            </div>

            <!-- Photo -->
            <div class="form-field">
              <label>Photo professionnelle</label>
              <input type="file" accept="image/*" @change="handleImage" />
            </div>

            <!-- Email -->
            <div class="form-field" :class="{ error: formErrors.email }">
              <label>Adresse email <span class="req">*</span></label>
              <input v-model="form.email" type="email" placeholder="jean.dupont@email.com" />
              <span v-if="formErrors.email" class="fe">{{ formErrors.email }}</span>
            </div>

            <!-- Téléphones -->
            <div class="form-row">
              <div class="form-field">
                <label>Tél. professionnel</label>
                <input v-model="form.telephone_pro" type="tel" placeholder="+212 5XX XXX XXX" />
              </div>
              <div class="form-field">
                <label>Tél. mobile</label>
                <input v-model="form.telephone_mobile" type="tel" placeholder="+212 6XX XXX XXX" />
              </div>
            </div>

            <!-- Rôle -->
            <div class="form-field" :class="{ error: formErrors.role }">
              <label>Rôle <span class="req">*</span></label>
              <div class="role-selector">
                <label v-for="r in roleOptions" :key="r.value" class="role-opt" :class="{ selected: form.role === r.value }">
                  <input type="radio" v-model="form.role" :value="r.value" hidden />
                  <div class="role-opt-icon" :style="{ background: r.bg, color: r.color }">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path :d="r.icon"/></svg>
                  </div>
                  <span>{{ r.label }}</span>
                </label>
              </div>
              <span v-if="formErrors.role" class="fe">{{ formErrors.role }}</span>
            </div>

            <!-- Mot de passe (création) -->
            <div v-if="modalMode === 'create'" class="form-row">
              <div class="form-field" :class="{ error: formErrors.password }">
                <label>Mot de passe <span class="req">*</span></label>
                <input v-model="form.password" type="password" placeholder="8 caractères min." />
                <span v-if="formErrors.password" class="fe">{{ formErrors.password }}</span>
              </div>
              <div class="form-field">
                <label>Confirmation <span class="req">*</span></label>
                <input v-model="form.password_confirmation" type="password" placeholder="••••••••" />
              </div>
            </div>

            <!-- Statut (modification) -->
            <div v-if="modalMode === 'edit'" class="form-field">
              <label>Statut du compte</label>
              <div class="toggle-row">
                <button type="button" class="toggle-switch" :class="{ on: form.est_actif }" @click="form.est_actif = !form.est_actif">
                  <span class="toggle-knob"></span>
                </button>
                <span class="toggle-label">{{ form.est_actif ? 'Compte actif' : 'Compte inactif' }}</span>
              </div>
            </div>
          </div>

          <!-- Modal footer -->
          <div class="modal-footer">
            <button class="btn-cancel" @click="closeModal">Annuler</button>
            <button class="btn-save" @click="saveUser" :disabled="saving">
              <span v-if="saving" class="mini-spinner"></span>
              {{ saving ? 'Enregistrement...' : (modalMode === 'create' ? 'Créer l\'utilisateur' : 'Enregistrer les modifications') }}
            </button>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════
           MODALE DE VISUALISATION DU PROFIL (améliorée)
      ══════════════════════════════════════════════════════════════ -->
      <div v-if="showViewModal" class="modal-overlay" @click.self="closeViewModal">
        <div class="modal profile-modal">
          <!-- En‑tête de la modale -->
          <div class="modal-header">
            <div class="modal-title-area">
              <div class="modal-icon" style="background:linear-gradient(135deg,#0f172a,#1e293b)">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
              </div>
              <div>
                <h2>Profil utilisateur</h2>
                <p>{{ viewUser?.prenom }} {{ viewUser?.nom }}</p>
              </div>
            </div>
            <button class="modal-close" @click="closeViewModal">
              <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
          </div>

          <!-- Corps de la modale avec le profil amélioré -->
          <div class="modal-body profile-body">
            <div v-if="viewUser" class="profile-container">

              <!-- Bloc d'identité -->
              <div class="profile-identity">
                <div class="profile-avatar">
                  <img v-if="viewUser.photo_profil" :src="getPhotoUrl(viewUser.photo_profil)" alt="Profil" />
                  <span v-else>{{ initials(viewUser) }}</span>
                </div>
                <div class="profile-name-wrapper">
                  <div class="profile-name-row">
                    <h2>{{ viewUser.prenom }} {{ viewUser.nom }}</h2>
                    <span class="status-pill" :class="viewUser.est_actif ? 'badge-active' : 'badge-inactive'">
                      {{ viewUser.est_actif ? 'Actif' : 'Inactif' }}
                    </span>
                  </div>
                  <p class="profile-role">{{ roleLabel(viewUser.role) }}</p>
                </div>
              </div>

              <!-- Grille d'informations -->
              <div class="profile-grid">
                <!-- Colonne principale -->
                <div class="profile-main">
                  <div class="profile-section">
                    <h3 class="section-title">Informations du compte</h3>
                    <div class="info-grid">
                      <div class="info-item">
                        <span class="info-label">Adresse e‑mail</span>
                        <span class="info-value email-link">{{ viewUser.email }}</span>
                      </div>
                      <div class="info-item">
                        <span class="info-label">Rôle</span>
                        <span class="info-value font-medium">{{ roleLabel(viewUser.role) }}</span>
                      </div>
                      <div class="info-item">
                        <span class="info-label">Téléphone pro</span>
                        <span class="info-value">{{ viewUser.telephone_pro || '—' }}</span>
                      </div>
                      <div class="info-item">
                        <span class="info-label">Téléphone mobile</span>
                        <span class="info-value">{{ viewUser.telephone_mobile || '—' }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Colonne latérale : activité -->
                <div class="profile-sidebar">
                  <div class="profile-section">
                    <h3 class="section-title">Sécurité & activité</h3>
                    <div class="timeline">
                      <div class="timeline-item">
                        <div class="timeline-marker" :class="{ 'update-marker': viewUser.est_actif }"></div>
                        <div class="timeline-content">
                          <span class="timeline-label">Dernière connexion</span>
                          <span class="timeline-value">{{ formatDate(viewUser.derniere_connexion_at) }}</span>
                        </div>
                      </div>
                      <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                          <span class="timeline-label">Statut d'accès</span>
                          <span class="timeline-value">{{ viewUser.est_actif ? 'Autorisé' : 'Révoqué' }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════
           CONFIRMATION DE SUPPRESSION
      ══════════════════════════════════════════════════════════════ -->
      <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
        <div class="confirm-modal">
          <div class="confirm-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          </div>
          <h3>Supprimer l'utilisateur ?</h3>
          <p>Êtes-vous sûr de vouloir supprimer <strong>{{ deleteTarget?.prenom }} {{ deleteTarget?.nom }}</strong> ? Cette action est irréversible.</p>
          <div class="confirm-actions">
            <button class="btn-cancel" @click="showDeleteConfirm = false">Annuler</button>
            <button class="btn-delete" @click="doDelete">Oui, supprimer</button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import userService from '@/services/userService'

// ── Data ──────────────────────────────────────────────────────────
const users = ref([])

async function loadUsers() {
  try {
    const response = await userService.getUsers()
    users.value = response.data.data
  } catch (error) {
    console.log(error)
  }
}

onMounted(loadUsers)

// ── Filters & search ──────────────────────────────────────────
const search       = ref('')
const filterRole   = ref('')
const filterStatus = ref('')
const view         = ref('table')
const currentPage  = ref(1)
const perPage      = 6

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const q = search.value.toLowerCase()
    const matchSearch = !q || `${u.prenom} ${u.nom} ${u.email}`.toLowerCase().includes(q)
    const matchRole   = !filterRole.value || u.role === filterRole.value
    const matchStatus = filterStatus.value === '' || String(u.est_actif) === filterStatus.value
    return matchSearch && matchRole && matchStatus
  })
})
function getLastLogin(user) {
  // Essaye plusieurs noms de champ possibles
  const date = user.derniere_connexion_at || user.derniere_connexion || user.last_login_at || user.last_login;
  return formatDate(date);
}
const totalPages    = computed(() => Math.max(1, Math.ceil(filteredUsers.value.length / perPage)))
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredUsers.value.slice(start, start + perPage)
})

const countByRole = (role) => users.value.filter(u => u.role === role).length

// ── Selection ──────────────────────────────────────────────────
const selected   = ref([])
const allSelected = computed(() => paginatedUsers.value.every(u => selected.value.includes(u.id)))
function toggleAll(e) {
  if (e.target.checked) selected.value = paginatedUsers.value.map(u => u.id)
  else selected.value = []
}

// ── View Modal (profil) ──────────────────────────────────────
const showViewModal = ref(false)
const viewUser = ref(null)

function openViewModal(user) {
  viewUser.value = user
  showViewModal.value = true
}

function closeViewModal() {
  showViewModal.value = false
  viewUser.value = null
}

// ── Create / Edit Modal ──────────────────────────────────────
const showModal = ref(false)
const modalMode = ref('create')
const saving    = ref(false)
const formError = ref('')
const formErrors = reactive({})

const form = reactive({
  id: null,
  prenom: '',
  nom: '',
  photo_profil: null,
  email: '',
  telephone_pro: '',
  telephone_mobile: '',
  role: '',
  password: '',
  password_confirmation: '',
  est_actif: true
})

const roleOptions = [
  { value:'admin', label:'Administrateur', bg:'rgba(99,102,241,0.12)', color:'#6366f1', icon:'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' },
  { value:'responsable', label:'Responsable', bg:'#ECFDF5', color:'#059669', icon:'M10 2a4 4 0 100 8 4 4 0 000-8z...' },
  { value:'chef_projet', label:'Chef de Projet', bg:'rgba(248,87,166,0.12)', color:'#f857a6', icon:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
  { value:'magasinier', label:'Magasinier', bg:'rgba(251,146,60,0.12)', color:'#fb923c', icon:'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4' },
]

function openModal(mode, user = null) {
  modalMode.value = mode
  formError.value = ''
  Object.keys(formErrors).forEach(k => delete formErrors[k])
  if (mode === 'create') {
    Object.assign(form, { id:null, prenom:'', nom:'', photo_profil:null, email:'', telephone_pro:'', telephone_mobile:'', role:'', password:'', password_confirmation:'', est_actif:true })
  } else {
    Object.assign(form, {
      ...user,
      photo_profil: null,
      password: '',
      password_confirmation: ''
    })
  }
  showModal.value = true
}

function closeModal() { showModal.value = false }

// ── Validation & sauvegarde ──────────────────────────────────
function validate() {
  Object.keys(formErrors).forEach(k => delete formErrors[k])
  if (!form.prenom.trim()) formErrors.prenom = 'Le prénom est requis.'
  if (!form.nom.trim())    formErrors.nom    = 'Le nom est requis.'
  if (!form.email.trim())  formErrors.email  = 'L\'email est requis.'
  if (!form.role)          formErrors.role   = 'Sélectionnez un rôle.'
  if (modalMode.value === 'create') {
    if (!form.password || form.password.length < 8) formErrors.password = 'Minimum 8 caractères.'
  }
  return Object.keys(formErrors).length === 0
}

async function saveUser() {
  if (!validate()) return
  saving.value = true
  try {
    const formData = new FormData()
    formData.append('nom', form.nom)
    formData.append('prenom', form.prenom)
    formData.append('email', form.email)
    formData.append('telephone_pro', form.telephone_pro || '')
    formData.append('telephone_mobile', form.telephone_mobile || '')
    formData.append('role', form.role)
    if (modalMode.value === 'create') {
      formData.append('password', form.password)
      formData.append('password_confirmation', form.password_confirmation)
    } else {
      formData.append('est_actif', form.est_actif ? 1 : 0)
    }
    if (form.photo_profil) {
      formData.append('photo_profil', form.photo_profil)
    }

    if (modalMode.value === 'create') {
      await userService.createUser(formData)
    } else {
      await userService.updateUser(form.id, formData)
    }
    await loadUsers()
    closeModal()
  } catch (error) {
    console.error(error.response?.data)
  } finally {
    saving.value = false
  }
}

// ── Status toggle ─────────────────────────────────────────────
async function toggleStatus(user) {
  try {
    await userService.toggleStatus(user.id)
    await loadUsers()
  } catch (error) {
    console.log(error)
  }
}

// ── Delete ────────────────────────────────────────────────────
const showDeleteConfirm = ref(false)
const deleteTarget = ref(null)

function confirmDelete(u) { deleteTarget.value = u; showDeleteConfirm.value = true }

async function doDelete() {
  try {
    await userService.deleteUser(deleteTarget.value.id)
    await loadUsers()
  } catch (error) {
    console.log(error)
  }
  showDeleteConfirm.value = false
}

// ── Helpers ───────────────────────────────────────────────────
function initials(u) {
  return `${u.prenom?.[0]??''}${u.nom?.[0]??''}`.toUpperCase()
}

function roleLabel(r) {
  return { admin:'Admin', responsable:'Responsable', chef_projet:'Chef Projet', magasinier:'Magasinier' }[r] ?? r
}

function avatarColor(role) {
  return { admin:'linear-gradient(135deg,#6366f1,#8b5cf6)', responsable:'linear-gradient(135deg,#10b981,#059669)', chef_projet:'linear-gradient(135deg,#f857a6,#e11d48)', magasinier:'linear-gradient(135deg,#fb923c,#f97316)' }[role] ?? '#94a3b8'
}

function cardGradient(role) {
  return { admin:'linear-gradient(135deg,#6366f1,#4f46e5)', responsable:'linear-gradient(135deg,#10b981,#059669)', chef_projet:'linear-gradient(135deg,#f857a6,#e11d48)', magasinier:'linear-gradient(135deg,#fb923c,#f97316)' }[role] ?? '#94a3b8'
}

function getPhotoUrl(path) {
  if (!path) return null
  return `http://127.0.0.1:8000/storage/${path}`
}

function formatDate(dateString) {
  if (!dateString) return 'Jamais'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function handleImage(event) {
  form.photo_profil = event.target.files[0]
}
</script>

<style scoped>
/* ── Page header ──────────────────────────────── */
.page-header { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem;gap:1rem;flex-wrap:wrap; }
.page-header h1 { font-size:1.4rem;font-weight:700;color:#1e2a4a;margin:0 0 .2rem; }
.page-header p { font-size:.875rem;color:#8a94b2;margin:0; }
.btn-primary { display:flex;align-items:center;gap:.4rem;padding:.6rem 1.1rem;background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;border-radius:10px;color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;box-shadow:0 4px 12px rgba(99,102,241,.3);transition:opacity .15s;white-space:nowrap; }
.btn-primary svg { width:15px;height:15px; }
.btn-primary:hover { opacity:.9; }

/* ── Stats bar ────────────────────────────────── */
.stats-bar { display:flex;gap:.6rem;margin-bottom:1rem;flex-wrap:wrap; }
.stat-pill { display:flex;align-items:center;gap:.5rem;padding:.45rem 1rem;border-radius:20px;border:1.5px solid #e4e9f2;background:#fff;font-size:.82rem;font-weight:500;color:#8a94b2;cursor:pointer;transition:all .15s; }
.stat-pill strong { color:#1e2a4a;margin-left:.25rem; }
.stat-pill .stat-dot { width:8px;height:8px;border-radius:50%; }
.stat-pill--all    .stat-dot { background:#6366f1; }
.stat-pill--admin  .stat-dot { background:#6366f1; }
.stat-pill--chef   .stat-dot { background:#f857a6; }
.stat-pill--mag    .stat-dot { background:#fb923c; }
.stat-pill--responsable .stat-dot { background:#059669; }
.stat-pill.active  { border-color:#6366f1;background:#eff6ff;color:#4f46e5; }
.stat-pill--chef.active { border-color:#f857a6;background:#fff0f6;color:#f857a6; }
.stat-pill--mag.active  { border-color:#fb923c;background:#fff7ed;color:#ea580c; }
.stat-pill--responsable.active { border-color:#059669;background:#ecfdf5;color:#065f46; }

/* ── Toolbar ──────────────────────────────────── */
.toolbar { display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;gap:.75rem;flex-wrap:wrap; }
.search-box { display:flex;align-items:center;gap:.5rem;background:#fff;border:1.5px solid #e4e9f2;border-radius:10px;padding:.5rem .85rem;flex:1;max-width:360px;transition:border-color .2s; }
.search-box:focus-within { border-color:#6366f1; }
.search-box svg { width:16px;height:16px;color:#8a94b2;flex-shrink:0; }
.search-box input { border:none;outline:none;font-size:.875rem;color:#1e2a4a;flex:1;background:none; }
.search-box input::placeholder { color:#94a3b8; }
.clear-btn { background:none;border:none;font-size:1.1rem;color:#94a3b8;cursor:pointer;line-height:1; }
.toolbar-right { display:flex;align-items:center;gap:.6rem; }
.filter-select { padding:.5rem .85rem;border:1.5px solid #e4e9f2;border-radius:10px;font-size:.875rem;color:#1e2a4a;background:#fff;cursor:pointer;outline:none; }
.view-toggle { display:flex;background:#f0f2f8;border-radius:8px;padding:3px;gap:2px; }
.view-toggle button { width:32px;height:32px;border:none;background:none;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#8a94b2;transition:all .15s; }
.view-toggle button svg { width:16px;height:16px; }
.view-toggle button.active { background:#fff;color:#6366f1;box-shadow:0 1px 3px rgba(0,0,0,.08); }

/* ── Table ────────────────────────────────────── */
.table-card { background:#fff;border-radius:16px;border:1px solid #e4e9f2;box-shadow:0 1px 4px rgba(0,0,0,.04);overflow:hidden; }
.users-table { width:100%;border-collapse:collapse; }
.users-table thead { background:#f8fafc; }
.users-table th { padding:.85rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:#8a94b2;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #e4e9f2;white-space:nowrap; }
.users-table td { padding:.9rem 1rem;border-bottom:1px solid #f0f2f8;font-size:.875rem;color:#1e2a4a;vertical-align:middle; }
.users-table tr:last-child td { border-bottom:none; }
.users-table tr.selected td { background:#f5f3ff; }
.users-table tr:hover td { background:#fafbff; }
.users-table input[type=checkbox] { width:15px;height:15px;accent-color:#6366f1;cursor:pointer; }

.user-cell { display:flex;align-items:center;gap:.75rem; }
.avatar { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;flex-shrink:0;overflow:hidden; }
.avatar img { width:100%;height:100%;object-fit:cover;border-radius:50%; }
.user-name  { font-weight:600;color:#1e2a4a;margin:0; }
.user-email { font-size:.78rem;color:#8a94b2;margin:.1rem 0 0; }

.role-badge { display:inline-flex;align-items:center;padding:.25rem .75rem;border-radius:20px;font-size:.72rem;font-weight:700;white-space:nowrap; }
.role-admin       { background:rgba(99,102,241,.1);color:#4f46e5; }
.role-chef_projet { background:rgba(248,87,166,.1);color:#e11d48; }
.role-magasinier  { background:rgba(251,146,60,.1);color:#ea580c; }
.role-responsable { background:#d1fae5; color:#065f46; }
.contact-line { font-size:.82rem;color:#475569;margin:0; }
.contact-line.muted { color:#94a3b8; }
.date-text { font-size:.8rem;color:#64748b; }

.status-toggle { display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .75rem;border-radius:20px;border:none;font-size:.75rem;font-weight:600;cursor:pointer;transition:all .15s; }
.status-toggle .status-dot { width:6px;height:6px;border-radius:50%; }
.status-toggle.active   { background:rgba(34,197,94,.1);color:#15803d; }
.status-toggle.active .status-dot { background:#22c55e; }
.status-toggle.inactive { background:rgba(148,163,184,.12);color:#64748b; }
.status-toggle.inactive .status-dot { background:#94a3b8; }

.action-btns { display:flex;gap:.4rem; }
.action-btn { width:30px;height:30px;border-radius:8px;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s; }
.action-btn svg { width:14px;height:14px; }
.action-btn.edit   { background:rgba(99,102,241,.08);color:#6366f1; }
.action-btn.edit:hover { background:rgba(99,102,241,.18); }
.action-btn.delete { background:rgba(239,68,68,.08);color:#ef4444; }
.action-btn.delete:hover { background:rgba(239,68,68,.18); }

.empty-row { text-align:center;padding:3rem!important; }
.empty-state { display:flex;flex-direction:column;align-items:center;gap:.75rem;color:#94a3b8; }
.empty-state svg { width:48px;height:48px; }
.empty-state p { font-size:.875rem; }

/* Pagination */
.pagination { display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.25rem;border-top:1px solid #f0f2f8; }
.page-info { font-size:.8rem;color:#8a94b2; }
.page-btns { display:flex;gap:.3rem; }
.page-btns button { width:30px;height:30px;border-radius:7px;border:1.5px solid #e4e9f2;background:#fff;color:#475569;font-size:.8rem;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s; }
.page-btns button svg { width:14px;height:14px; }
.page-btns button:hover:not(:disabled) { border-color:#6366f1;color:#6366f1; }
.page-btns button:disabled { opacity:.4;cursor:not-allowed; }
.page-btns button.current { background:#6366f1;border-color:#6366f1;color:#fff; }

/* ── Cards view ────────────────────────────────── */
.user-cards { display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem; }
.user-card { background:#fff;border-radius:16px;border:1px solid #e4e9f2;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.04);cursor:pointer;transition:transform .15s; }
.user-card:hover { transform:translateY(-2px); }
.uc-header { padding:1.5rem 1rem 3rem;position:relative;display:flex;justify-content:flex-end; }
.uc-avatar { position:absolute;bottom:-20px;left:50%;transform:translateX(-50%);width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,.25);backdrop-filter:blur(4px);border:3px solid #fff;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;color:#fff;overflow:hidden; }
.uc-avatar img { width:100%;height:100%;object-fit:cover; }
.uc-status { display:inline-flex;align-items:center;padding:.2rem .6rem;border-radius:10px;font-size:.68rem;font-weight:700;background:rgba(255,255,255,.2);color:#fff; }
.uc-status.active { background:rgba(34,197,94,.4); }
.uc-status.inactive { background:rgba(148,163,184,.4); }
.uc-body { padding:1.5rem 1rem 1rem;text-align:center; }
.uc-body h4 { font-size:.95rem;font-weight:700;color:#1e2a4a;margin:0 0 .2rem; }
.uc-email { font-size:.75rem;color:#8a94b2;margin:0 0 .75rem; }
.uc-actions { display:flex;gap:.5rem;justify-content:center;margin-top:.85rem; }

/* ── Modal communes ─────────────────────────────── */
.modal-overlay { position:fixed;inset:0;background:rgba(15,23,42,.5);display:flex;align-items:center;justify-content:center;z-index:1000;padding:1rem;backdrop-filter:blur(4px); }
.modal { background:#fff;border-radius:20px;width:100%;max-width:540px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px rgba(0,0,0,.25); }

.modal-header { display:flex;align-items:flex-start;justify-content:space-between;padding:1.5rem;border-bottom:1px solid #f0f2f8; }
.modal-title-area { display:flex;align-items:flex-start;gap:.9rem; }
.modal-icon { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0; }
.modal-icon svg { width:20px;height:20px; }
.modal-title-area h2 { font-size:1rem;font-weight:700;color:#1e2a4a;margin:0 0 .2rem; }
.modal-title-area p { font-size:.8rem;color:#8a94b2;margin:0; }
.modal-close { background:none;border:none;cursor:pointer;color:#8a94b2;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:8px;transition:background .15s; }
.modal-close:hover { background:#f0f2f8;color:#1e2a4a; }
.modal-close svg { width:18px;height:18px; }

.modal-body { padding:1.5rem;display:flex;flex-direction:column;gap:1rem; }
.form-alert { background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:.75rem 1rem;font-size:.85rem; }

.form-row { display:grid;grid-template-columns:1fr 1fr;gap:.9rem; }
.form-field { display:flex;flex-direction:column;gap:.35rem; }
.form-field label { font-size:.82rem;font-weight:600;color:#374151; }
.req { color:#f857a6; }
.form-field input { padding:.65rem .9rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:.875rem;color:#1e2a4a;outline:none;background:#f9fafb;transition:all .2s; }
.form-field input:focus { border-color:#6366f1;background:#fff;box-shadow:0 0 0 4px rgba(99,102,241,.08); }
.form-field.error input { border-color:#ef4444; }
.fe { font-size:.75rem;color:#dc2626;font-weight:500; }

.role-selector { display:flex;gap:.5rem;flex-wrap:wrap; }
.role-opt { flex:1;display:flex;flex-direction:column;align-items:center;gap:.4rem;padding:.75rem .5rem;border:1.5px solid #e2e8f0;border-radius:12px;cursor:pointer;text-align:center;font-size:.72rem;font-weight:600;color:#64748b;transition:all .15s;background:#f9fafb;min-width:80px; }
.role-opt.selected { border-color:#6366f1;background:#eff6ff;color:#4f46e5; }
.role-opt-icon { width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center; }
.role-opt-icon svg { width:18px;height:18px; }

.toggle-row { display:flex;align-items:center;gap:.75rem; }
.toggle-switch { width:44px;height:24px;border-radius:12px;background:#e2e8f0;border:none;cursor:pointer;position:relative;transition:background .2s;padding:0; }
.toggle-switch.on { background:linear-gradient(135deg,#22c55e,#16a34a); }
.toggle-knob { width:18px;height:18px;border-radius:50%;background:#fff;position:absolute;top:3px;left:3px;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2); }
.toggle-switch.on .toggle-knob { transform:translateX(20px); }
.toggle-label { font-size:.875rem;color:#475569;font-weight:500; }

.modal-footer { display:flex;justify-content:flex-end;gap:.75rem;padding:1.25rem 1.5rem;border-top:1px solid #f0f2f8; }
.btn-cancel { padding:.6rem 1.25rem;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;color:#475569;font-size:.875rem;font-weight:600;cursor:pointer; }
.btn-save { display:flex;align-items:center;gap:.4rem;padding:.6rem 1.25rem;background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;border-radius:10px;color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;box-shadow:0 4px 12px rgba(99,102,241,.3); }
.btn-save:disabled { opacity:.65;cursor:not-allowed; }
.mini-spinner { width:15px;height:15px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0; }
@keyframes spin { to { transform:rotate(360deg); } }

/* Confirm modal */
.confirm-modal { background:#fff;border-radius:16px;padding:2rem;max-width:380px;width:100%;text-align:center;box-shadow:0 25px 50px rgba(0,0,0,.25); }
.confirm-icon { width:60px;height:60px;border-radius:50%;background:rgba(239,68,68,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#ef4444; }
.confirm-icon svg { width:28px;height:28px; }
.confirm-modal h3 { font-size:1.1rem;font-weight:700;color:#1e2a4a;margin:0 0 .5rem; }
.confirm-modal p { font-size:.875rem;color:#64748b;line-height:1.6;margin:0 0 1.5rem; }
.confirm-actions { display:flex;gap:.75rem;justify-content:center; }
.btn-delete { padding:.6rem 1.5rem;background:linear-gradient(135deg,#ef4444,#dc2626);border:none;border-radius:10px;color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;box-shadow:0 4px 12px rgba(239,68,68,.3); }

/* ──── MODALE PROFIL AMÉLIORÉE ──── */
.profile-modal {
  max-width: 820px;
  width: 95%;
  border-radius: 24px;
  background: #ffffff;
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.15);
}

.profile-modal .modal-body {
  padding: 0;
  gap: 0;
}

.profile-container {
  padding: 1.75rem 2rem 2rem;
  background: #f8fafc;
}

/* Identité */
.profile-identity {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #eef2f6;
  margin-bottom: 1.75rem;
}

.profile-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0f172a, #1e293b);
  color: #fff;
  font-size: 28px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
}

.profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-name-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.profile-name-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.profile-name-row h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.profile-role {
  font-size: 0.95rem;
  color: #64748b;
  font-weight: 500;
  margin: 0;
}

/* Badges de statut */
.status-pill {
  display: inline-block;
  padding: 0.2rem 0.8rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-active {
  background: #ecfdf5;
  color: #065f46;
}
.badge-inactive {
  background: #fef2f2;
  color: #991b1b;
}

/* Grille profil */
.profile-grid {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 1.5rem;
}

@media (max-width: 700px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
}

.profile-section {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #eef2f6;
  padding: 1.25rem 1.5rem 1.5rem;
}

.section-title {
  font-size: 0.85rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin: 0 0 1.25rem 0;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

@media (max-width: 480px) {
  .info-grid {
    grid-template-columns: 1fr;
  }
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.info-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-value {
  font-size: 0.9rem;
  color: #0f172a;
}

.font-medium {
  font-weight: 500;
}

.email-link {
  color: #2563eb;
  font-weight: 500;
  text-decoration: none;
  transition: color 0.2s;
}
.email-link:hover {
  color: #1d4ed8;
  text-decoration: underline;
}

/* Timeline */
.timeline {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.timeline-item {
  display: flex;
  gap: 1rem;
  position: relative;
}

.timeline-item:not(:last-child)::after {
  content: '';
  position: absolute;
  left: 5px;
  top: 14px;
  bottom: -10px;
  width: 2px;
  background-color: #eef2f6;
}

.timeline-marker {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #d1d5db;
  margin-top: 2px;
  flex-shrink: 0;
  border: 2px solid #fff;
  box-shadow: 0 0 0 1px #d1d5db;
}

.timeline-marker.update-marker {
  background: #059669;
  box-shadow: 0 0 0 1px #059669;
}

.timeline-content {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.timeline-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.timeline-value {
  font-size: 0.9rem;
  color: #0f172a;
  font-weight: 500;
}

/* Responsive fine */
@media (max-width: 640px) {
  .form-row { grid-template-columns:1fr; }
  .role-selector { flex-wrap:wrap; }
  .profile-identity {
    flex-direction: column;
    text-align: center;
  }
  .profile-name-row {
    justify-content: center;
  }
  .profile-modal { max-width: 100%; border-radius: 16px; }
  .profile-container { padding: 1rem; }
}
</style>