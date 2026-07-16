<template>
  <div class="auth-layout">
    <div class="auth-card">

      <div class="auth-header-container">
        <div class="brand-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 21h18M3 7l9-4 9 4M4 7v14M20 7v14M9 21V11h6v10"/>
          </svg>
        </div>
        <h1 class="brand-title">GesChantier</h1>
        <p class="brand-tagline"></p>
        <h2 class="auth-title">Créer un compte</h2>
        <p class="auth-subtitle">Rejoignez notre plateforme professionnelle.</p>
      </div>

      <div v-if="errorMessage" class="alert alert-error">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
        </svg>
        <span>{{ errorMessage }}</span>
      </div>

      <form @submit.prevent="handleRegister" novalidate>

        <div class="row-2">
          <div class="field" :class="{ error: errors.prenom }">
            <label for="prenom">Prénom</label>
            <input id="prenom" v-model="form.prenom" type="text" placeholder="Jean" :disabled="loading" />
            <span v-if="errors.prenom" class="field-error">{{ errors.prenom[0] }}</span>
          </div>
          <div class="field" :class="{ error: errors.nom }">
            <label for="nom">Nom</label>
            <input id="nom" v-model="form.nom" type="text" placeholder="Dupont" :disabled="loading" />
            <span v-if="errors.nom" class="field-error">{{ errors.nom[0] }}</span>
          </div>
        </div>

        <div class="field" :class="{ error: errors.email }">
          <label for="email">Adresse email</label>
          <input id="email" v-model="form.email" type="email" placeholder="jean.dupont@email.com" autocomplete="email" :disabled="loading" />
          <span v-if="errors.email" class="field-error">{{ errors.email[0] }}</span>
        </div>

        <div class="row-2">
          <div class="field" :class="{ error: errors.telephone_pro }">
            <label for="tel_pro">Tél. professionnel</label>
            <input id="tel_pro" v-model="form.telephone_pro" type="tel" placeholder="+212 6XX XXX XXX" :disabled="loading" />
            <span v-if="errors.telephone_pro" class="field-error">{{ errors.telephone_pro[0] }}</span>
          </div>
          <div class="field" :class="{ error: errors.telephone_mobile }">
            <label for="tel_mob">Tél. mobile</label>
            <input id="tel_mob" v-model="form.telephone_mobile" type="tel" placeholder="+212 6XX XXX XXX" :disabled="loading" />
            <span v-if="errors.telephone_mobile" class="field-error">{{ errors.telephone_mobile[0] }}</span>
          </div>
        </div>

        <div class="field" :class="{ error: errors.role }">
          <label>Rôle</label>
          <div class="role-group">
            <label
              v-for="r in roles"
              :key="r.value"
              class="role-card"
              :class="{ selected: form.role === r.value }"
            >
              <input type="radio" v-model="form.role" :value="r.value" hidden />
              <div class="role-icon" v-html="r.icon" />
              <span class="role-label">{{ r.label }}</span>
            </label>
          </div>
          <span v-if="errors.role" class="field-error">{{ errors.role[0] }}</span>
        </div>

        <div class="field" :class="{ error: errors.password }">
          <label for="password">Mot de passe</label>
          <div class="input-wrapper">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="8 caractères minimum"
              autocomplete="new-password"
              :disabled="loading"
            />
            <button type="button" class="toggle-eye" @click="showPassword = !showPassword" tabindex="-1">
              <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.373l1.091 1.092a4 4 0 00-5.557-5.557z" clip-rule="evenodd"/><path d="M10.748 13.93l2.523 2.523a10.003 10.003 0 01-6.27 0l2.523-2.523a4.001 4.001 0 001.224 0z"/></svg>
            </button>
          </div>
          <span v-if="errors.password" class="field-error">{{ errors.password[0] }}</span>
        </div>

        <div class="field" :class="{ error: errors.password_confirmation }">
          <label for="password_conf">Confirmer le mot de passe</label>
          <input
            id="password_conf"
            v-model="form.password_confirmation"
            type="password"
            placeholder="••••••••"
            autocomplete="new-password"
            :disabled="loading"
          />
          <span v-if="errors.password_confirmation" class="field-error">{{ errors.password_confirmation[0] }}</span>
        </div>

        <button type="submit" class="btn-primary" :disabled="loading">
          <span v-if="loading" class="spinner" />
          <span>{{ loading ? 'Création du compte...' : 'Créer mon compte' }}</span>
        </button>
      </form>

      <p class="footer-link">
        Déjà un compte ?
        <RouterLink to="/login">Se connecter</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth   = useAuthStore()

const form = reactive({
  nom: '', prenom: '', email: '',
  telephone_pro: '', telephone_mobile: '',
  role: '', password: '', password_confirmation: '',
})

const errors       = ref({})
const errorMessage = ref('')
const loading      = ref(false)
const showPassword = ref(false)

const roles = [
  
  {
    value: 'responsable',
    label: 'Responsable',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="8" r="4"/>
      <path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/>
    </svg>`,
  },
  {
    value: 'chef_projet',
    label: 'Chef de Projet',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
    </svg>`,
  },
  {
    value: 'magasinier',
    label: 'Magasinier',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
    </svg>`,
  },
]

async function handleRegister() {
  errors.value       = {}
  errorMessage.value = ''
  loading.value      = true

  try {
    const user = await auth.register(form)
    router.push(user.redirect_to)
  } catch (err) {
    if (err.errors) {
      errors.value = err.errors
    } else {
      errorMessage.value = err.message ?? 'Une erreur est survenue.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Calqué exactement sur le Login */
.auth-layout { min-height:100vh; display:flex; align-items:center; justify-content:center; background-color:#f8fafc; background-image:radial-gradient(at 0% 0%,rgba(37,99,235,.04) 0px,transparent 50%),radial-gradient(at 100% 100%,rgba(245,158,11,.03) 0px,transparent 50%); padding:2rem 1.5rem; font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
.auth-card { background:#fff; border-radius:20px; padding:3rem 2.5rem; width:100%; max-width:500px; border:1px solid #e2e8f0; box-shadow:0 20px 25px -5px rgba(0,0,0,.03),0 10px 10px -5px rgba(0,0,0,.02); }
.auth-header-container { display:flex; flex-direction:column; align-items:center; text-align:center; margin-bottom:2.25rem; }
.brand-icon { width:44px; height:44px; background:#eff6ff; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#2563eb; border:1px solid #dbeafe; margin-bottom:.75rem; }
.brand-icon svg { width:24px; height:24px; }
.brand-title { font-size:1.5rem; font-weight:700; color:#0f172a; margin:0; letter-spacing:-.02em; }
.brand-tagline { font-size:.75rem; color:#64748b; margin:.15rem 0 1.5rem; text-transform:uppercase; letter-spacing:.05em; font-weight:600; }
.auth-title { font-size:1.35rem; font-weight:600; color:#0f172a; margin:0 0 .35rem; }
.auth-subtitle { font-size:.875rem; color:#64748b; margin:0; }
.alert { display:flex; align-items:flex-start; gap:.75rem; padding:.875rem 1rem; border-radius:10px; font-size:.875rem; margin-bottom:1.5rem; background:#fef2f2; color:#991b1b; border:1px solid #fee2e2; line-height:1.4; }
.alert svg { width:18px; height:18px; color:#ef4444; flex-shrink:0; margin-top:1px; }

/* Grille 2 colonnes spécifique */
.row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

.field { margin-bottom: 1.25rem; }
.field label { display:block; font-size:.875rem; font-weight:500; color:#334155; margin-bottom:.5rem; }
.field input { width:100%; padding:.75rem 1rem; border:1px solid #cbd5e1; border-radius:10px; font-size:.95rem; color:#0f172a; background:#fff; box-sizing:border-box; outline:none; transition:all .2s cubic-bezier(.4,0,.2,1); }
.field input::placeholder { color:#94a3b8; }
.field input:hover:not(:disabled) { border-color:#94a3b8; }
.field input:focus { border-color:#2563eb; box-shadow:0 0 0 4px rgba(37,99,235,.08); }
.field.error input { border-color:#f43f5e; background:#fff8f8; }
.field-error { display:block; font-size:.8rem; color:#e11d48; margin-top:.4rem; font-weight:500; }

/* Section des rôles harmonisée au thème bleu */
.role-group { display: flex; gap: 0.6rem; }
.role-card {
  flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
  padding: 0.85rem 0.5rem;
  border: 1px solid #cbd5e1; border-radius: 10px;
  cursor: pointer; text-align: center;
  transition: all 0.2s;
  background: #fff;
}
.role-card:hover { border-color: #94a3b8; background: #f8fafc; }
.role-card.selected { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 4px rgba(37,99,235,.08); }
.role-icon { color: #64748b; display: flex; }
.role-card.selected .role-icon { color: #2563eb; }
.role-icon :deep(svg) { width: 22px; height: 22px; }
.role-label { font-size: 0.75rem; font-weight: 600; color: #475569; line-height: 1.2; }
.role-card.selected .role-label { color: #2563eb; }

.input-wrapper { position:relative; }
.input-wrapper input { padding-right:3rem; }
.toggle-eye { position:absolute; right:.85rem; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#64748b; padding:4px; display:flex; border-radius:6px; transition:all .15s; }
.toggle-eye svg { width:18px; height:18px; }
.toggle-eye:hover { color:#0f172a; background:#f1f5f9; }

.btn-primary { width:100%; padding:.85rem; background:#2563eb; color:#fff; border:none; border-radius:10px; font-size:.95rem; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:.5rem; margin-top:1.75rem; transition:all .15s ease; box-shadow:0 4px 6px -1px rgba(37,99,235,.12); }
.btn-primary:hover:not(:disabled) { background:#1d4ed8; box-shadow:0 10px 15px -3px rgba(37,99,235,.2); }
.btn-primary:active:not(:disabled) { transform:scale(.99); }
.btn-primary:disabled { opacity:.6; cursor:not-allowed; }

.spinner { width:18px; height:18px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .6s linear infinite; flex-shrink:0; }
@keyframes spin { to { transform:rotate(360deg); } }

.footer-link { text-align:center; margin-top:2rem; font-size:.875rem; color:#64748b; }
.footer-link a { color:#2563eb; font-weight:600; text-decoration:none; margin-left:.25rem; transition:color .15s; }
.footer-link a:hover { color:#1d4ed8; text-decoration:underline; }

@media (max-width:480px) { .auth-layout { background:#fff; align-items:flex-start; padding-top:2rem; } .auth-card { border:none; box-shadow:none; padding:1rem; } .row-2 { grid-template-columns: 1fr; gap: 0; } }
</style>
