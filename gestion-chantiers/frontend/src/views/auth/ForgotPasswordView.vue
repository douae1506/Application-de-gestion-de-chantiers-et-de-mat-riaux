<template>
  <div class="auth-layout">
    <div class="auth-card">

      <!-- En-tête -->
      <div class="auth-header-container">
        <div class="brand-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 21h18M3 7l9-4 9 4M4 7v14M20 7v14M9 21V11h6v10"/>
          </svg>
        </div>
        <h1 class="brand-title">GesChantier</h1>
        <p class="brand-tagline"></p>

        <!-- Titre dynamique selon l'étape -->
        <h2 class="auth-title">{{ steps[currentStep].title }}</h2>
        <p class="auth-subtitle">{{ steps[currentStep].subtitle }}</p>
      </div>

      <!-- Indicateur de progression -->
      <div class="progress-steps">
        <div
          v-for="(step, i) in steps"
          :key="i"
          class="step-item"
          :class="{ active: i === currentStep, done: i < currentStep }"
        >
          <div class="step-dot">
            <svg v-if="i < currentStep" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
            </svg>
            <span v-else>{{ i + 1 }}</span>
          </div>
          <span class="step-label">{{ step.label }}</span>
        </div>
        <div class="step-connector" />
      </div>

      <!-- Alerte globale -->
      <div v-if="errorMessage" class="alert alert-error">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
        </svg>
        <span>{{ errorMessage }}</span>
      </div>

      <!-- ═══════════════════════════════════════════
           ÉTAPE 0 — Saisir l'email
      ═══════════════════════════════════════════ -->
      <form v-if="currentStep === 0" @submit.prevent="handleSendOtp" novalidate>
        <div class="field" :class="{ error: errors.email }">
          <label for="email">Adresse email professionnelle</label>
          <div class="input-icon-wrapper">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z"/>
              <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z"/>
            </svg>
            <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="nom@entreprise.com"
              autocomplete="email"
              :disabled="loading"
            />
          </div>
          <span v-if="errors.email" class="field-error">{{ errors.email[0] }}</span>
        </div>

        <button type="submit" class="btn-primary" :disabled="loading">
          <span v-if="loading" class="spinner" />
          <span>{{ loading ? 'Envoi en cours...' : 'Envoyer le code de vérification' }}</span>
        </button>
      </form>

      <!-- ═══════════════════════════════════════════
           ÉTAPE 1 — Saisir le code OTP
      ═══════════════════════════════════════════ -->
      <div v-if="currentStep === 1">
        <!-- Info email -->
        <div class="info-banner">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/>
          </svg>
          <span>Code envoyé à <strong>{{ form.email }}</strong></span>
        </div>

        <!-- Timer -->
        <div class="timer-block" :class="{ warning: timerSeconds < 60, expired: timerSeconds === 0 }">
          <div class="timer-circle">
            <svg viewBox="0 0 36 36" class="timer-svg">
              <path class="timer-track" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
              <path
                class="timer-progress"
                :stroke-dasharray="`${timerPercent}, 100`"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <span class="timer-text">{{ formattedTimer }}</span>
          </div>
          <div class="timer-info">
            <p v-if="timerSeconds > 0">Votre code expire dans <strong>{{ formattedTimer }}</strong></p>
            <p v-else class="expired-text">⛔ Code expiré — Renvoyez un nouveau code</p>
          </div>
        </div>

        <form @submit.prevent="handleVerifyOtp" novalidate>
          <!-- Saisie OTP à 6 chiffres -->
          <div class="field" :class="{ error: errors.code }">
            <label>Code de vérification à 6 chiffres</label>
            <div class="otp-inputs">
              <input
                v-for="(_, i) in 6"
                :key="i"
                :ref="el => otpRefs[i] = el"
                v-model="otpDigits[i]"
                type="text"
                inputmode="numeric"
                maxlength="1"
                class="otp-digit"
                :class="{ filled: otpDigits[i], error: errors.code }"
                :disabled="loading || timerSeconds === 0"
                @input="onOtpInput(i, $event)"
                @keydown.backspace="onOtpBackspace(i)"
                @paste.prevent="onOtpPaste($event)"
              />
            </div>
            <span v-if="errors.code" class="field-error">{{ errors.code[0] }}</span>
          </div>

          <button type="submit" class="btn-primary" :disabled="loading || otpCode.length < 6 || timerSeconds === 0">
            <span v-if="loading" class="spinner" />
            <span>{{ loading ? 'Vérification...' : 'Vérifier le code' }}</span>
          </button>
        </form>

        <!-- Renvoyer le code -->
        <div class="resend-block">
          <span>Vous n'avez pas reçu le code ?</span>
          <button
            class="btn-link"
            :disabled="resendCooldown > 0"
            @click="handleResend"
          >
            {{ resendCooldown > 0 ? `Renvoyer (${resendCooldown}s)` : 'Renvoyer le code' }}
          </button>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════
           ÉTAPE 2 — Nouveau mot de passe
      ═══════════════════════════════════════════ -->
      <form v-if="currentStep === 2" @submit.prevent="handleResetPassword" novalidate>

        <div class="field" :class="{ error: errors.password }">
          <label for="password">Nouveau mot de passe</label>
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
          <!-- Indicateur de force -->
          <div class="password-strength" v-if="form.password">
            <div class="strength-bars">
              <span v-for="n in 4" :key="n" class="bar" :class="{ active: passwordStrength >= n, [strengthColor]: passwordStrength >= n }" />
            </div>
            <span class="strength-label" :class="strengthColor">{{ strengthLabel }}</span>
          </div>
          <span v-if="errors.password" class="field-error">{{ errors.password[0] }}</span>
        </div>

        <div class="field" :class="{ error: errors.password_confirmation }">
          <label for="password_conf">Confirmer le nouveau mot de passe</label>
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
          <span>{{ loading ? 'Enregistrement...' : 'Enregistrer le nouveau mot de passe' }}</span>
        </button>
      </form>

      <!-- ═══════════════════════════════════════════
           ÉTAPE 3 — Succès
      ═══════════════════════════════════════════ -->
      <div v-if="currentStep === 3" class="success-block">
        <div class="success-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/>
          </svg>
        </div>
        <h3>Mot de passe mis à jour !</h3>
        <p>Votre mot de passe a été réinitialisé avec succès. Vous allez être redirigé vers la page de connexion.</p>
        <div class="redirect-bar">
          <div class="redirect-progress" :style="{ width: redirectPercent + '%' }" />
        </div>
      </div>

      <!-- Lien retour connexion -->
      <p v-if="currentStep < 3" class="footer-link">
        <RouterLink to="/login">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd"/></svg>
          Retour à la connexion
        </RouterLink>
      </p>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const API_BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'

// ── State ──────────────────────────────────────────────────────
const currentStep  = ref(0)
const loading      = ref(false)
const errorMessage = ref('')
const errors       = ref({})
const showPassword = ref(false)

const form = reactive({
  email: '', password: '', password_confirmation: '',
})

// OTP — tableau de 6 cases
const otpDigits = reactive(Array(6).fill(''))
const otpRefs   = ref([])
const otpCode   = computed(() => otpDigits.join(''))

// Token de session renvoyé après vérification OTP
const resetToken = ref('')

// Timer (15 min = 900s)
const TIMER_TOTAL  = 15 * 60
const timerSeconds = ref(TIMER_TOTAL)
let timerInterval  = null

const timerPercent  = computed(() => (timerSeconds.value / TIMER_TOTAL) * 100)
const formattedTimer = computed(() => {
  const m = Math.floor(timerSeconds.value / 60).toString().padStart(2, '0')
  const s = (timerSeconds.value % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})

// Cooldown renvoi
const resendCooldown = ref(0)
let resendInterval   = null

// Redirection succès
const redirectPercent = ref(100)
let redirectInterval  = null

// ── Steps config ───────────────────────────────────────────────
const steps = [
  { label: 'Email',      title: 'Mot de passe oublié',           subtitle: 'Saisissez votre email pour recevoir un code de vérification.' },
  { label: 'Code OTP',   title: 'Vérification',                  subtitle: 'Entrez le code à 6 chiffres reçu par email.' },
  { label: 'Nouveau mdp', title: 'Nouveau mot de passe',          subtitle: 'Choisissez un mot de passe sécurisé pour votre compte.' },
  { label: 'Succès',     title: 'Réinitialisation réussie', subtitle: '' },
]

// ── Password strength ──────────────────────────────────────────
const passwordStrength = computed(() => {
  const p = form.password
  let score = 0
  if (p.length >= 8)          score++
  if (/[A-Z]/.test(p))       score++
  if (/[0-9]/.test(p))       score++
  if (/[^A-Za-z0-9]/.test(p)) score++
  return score
})
const strengthLabel = computed(() => ['Très faible', 'Faible', 'Moyen', 'Fort', 'Très fort'][passwordStrength.value])
const strengthColor = computed(() => ['red', 'red', 'orange', 'green', 'green'][passwordStrength.value])

// ── Helpers ────────────────────────────────────────────────────
function clearState() {
  errorMessage.value = ''
  errors.value       = {}
}

async function apiFetch(path, body) {
  const res  = await fetch(`${API_BASE}${path}`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body:    JSON.stringify(body),
  })
  const data = await res.json()
  if (!res.ok) throw data
  return data
}

function startTimer(seconds = TIMER_TOTAL) {
  clearInterval(timerInterval)
  timerSeconds.value = seconds
  timerInterval = setInterval(() => {
    if (timerSeconds.value > 0) timerSeconds.value--
    else clearInterval(timerInterval)
  }, 1000)
}

function startResendCooldown(seconds = 60) {
  clearInterval(resendInterval)
  resendCooldown.value = seconds
  resendInterval = setInterval(() => {
    if (resendCooldown.value > 0) resendCooldown.value--
    else clearInterval(resendInterval)
  }, 1000)
}

// ── OTP input handlers ─────────────────────────────────────────
function onOtpInput(index, event) {
  const val = event.target.value.replace(/\D/g, '')
  otpDigits[index] = val.slice(-1)
  if (val && index < 5) otpRefs.value[index + 1]?.focus()
}

function onOtpBackspace(index) {
  if (!otpDigits[index] && index > 0) {
    otpDigits[index - 1] = ''
    otpRefs.value[index - 1]?.focus()
  }
}

function onOtpPaste(event) {
  const pasted = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6)
  pasted.split('').forEach((char, i) => { otpDigits[i] = char })
  otpRefs.value[Math.min(pasted.length, 5)]?.focus()
}

// ── API actions ────────────────────────────────────────────────
async function handleSendOtp() {
  clearState()
  loading.value = true
  try {
    const data = await apiFetch('/auth/password/send-otp', { email: form.email })
    startTimer(data.expires_in ?? TIMER_TOTAL)
    startResendCooldown(60)
    currentStep.value = 1
    // Focus premier champ OTP après transition
    setTimeout(() => otpRefs.value[0]?.focus(), 100)
  } catch (err) {
    if (err.errors) errors.value = err.errors
    else errorMessage.value = err.message ?? 'Erreur lors de l\'envoi.'
  } finally {
    loading.value = false
  }
}

async function handleResend() {
  clearState()
  otpDigits.fill('')
  loading.value = true
  try {
    const data = await apiFetch('/auth/password/send-otp', { email: form.email })
    startTimer(data.expires_in ?? TIMER_TOTAL)
    startResendCooldown(60)
    setTimeout(() => otpRefs.value[0]?.focus(), 100)
  } catch (err) {
    if (err.retry_after) startResendCooldown(err.retry_after)
    errorMessage.value = err.message ?? 'Erreur lors du renvoi.'
  } finally {
    loading.value = false
  }
}

async function handleVerifyOtp() {
  clearState()
  loading.value = true
  try {
    const data = await apiFetch('/auth/password/verify-otp', {
      email: form.email,
      code:  otpCode.value,
    })
    resetToken.value  = data.reset_token
    clearInterval(timerInterval)
    currentStep.value = 2
  } catch (err) {
    if (err.errors) errors.value = err.errors
    else errorMessage.value = err.message ?? 'Code invalide ou expiré.'
  } finally {
    loading.value = false
  }
}

async function handleResetPassword() {
  clearState()
  loading.value = true
  try {
    await apiFetch('/auth/password/reset', {
      reset_token:           resetToken.value,
      password:              form.password,
      password_confirmation: form.password_confirmation,
    })
    currentStep.value = 3
    startRedirectCountdown()
  } catch (err) {
    if (err.errors) errors.value = err.errors
    else errorMessage.value = err.message ?? 'Erreur lors de la réinitialisation.'
  } finally {
    loading.value = false
  }
}

function startRedirectCountdown() {
  const DURATION = 4000
  const start    = Date.now()
  redirectInterval = setInterval(() => {
    const elapsed = Date.now() - start
    redirectPercent.value = Math.max(0, 100 - (elapsed / DURATION) * 100)
    if (elapsed >= DURATION) {
      clearInterval(redirectInterval)
      router.push('/login')
    }
  }, 50)
}

onUnmounted(() => {
  clearInterval(timerInterval)
  clearInterval(resendInterval)
  clearInterval(redirectInterval)
})
</script>

<style scoped>
/* ── Layout ─────────────────────────────────────────────── */
.auth-layout {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8fafc;
  background-image:
    radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.04) 0px, transparent 50%),
    radial-gradient(at 100% 100%, rgba(245, 158, 11, 0.03) 0px, transparent 50%);
  padding: 2rem 1.5rem;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.auth-card {
  background: #fff;
  border-radius: 20px;
  padding: 2.5rem 2.25rem;
  width: 100%;
  max-width: 460px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.04), 0 10px 10px -5px rgba(0,0,0,0.02);
}

/* ── Header ─────────────────────────────────────────────── */
.auth-header-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-bottom: 1.75rem;
}
.brand-icon {
  width: 44px; height: 44px;
  background: #eff6ff;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  color: #2563eb; border: 1px solid #dbeafe;
  margin-bottom: 0.75rem;
}
.brand-icon svg { width: 24px; height: 24px; }
.brand-title { font-size: 1.4rem; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: -0.02em; }
.brand-tagline { font-size: 0.75rem; color: #64748b; margin: 0.15rem 0 1.25rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
.auth-title { font-size: 1.2rem; font-weight: 600; color: #0f172a; margin: 0 0 0.3rem; }
.auth-subtitle { font-size: 0.875rem; color: #64748b; margin: 0; }

/* ── Progress steps ─────────────────────────────────────── */
.progress-steps {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  margin-bottom: 1.75rem;
  padding: 0 0.25rem;
}
.step-connector {
  position: absolute;
  top: 14px;
  left: 12%;
  right: 12%;
  height: 2px;
  background: #e2e8f0;
  z-index: 0;
}
.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.4rem;
  z-index: 1;
  flex: 1;
}
.step-dot {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: #f1f5f9;
  border: 2px solid #cbd5e1;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.75rem; font-weight: 700; color: #94a3b8;
  transition: all 0.3s;
}
.step-dot svg { width: 14px; height: 14px; }
.step-item.active .step-dot { background: #2563eb; border-color: #2563eb; color: #fff; }
.step-item.done   .step-dot { background: #22c55e; border-color: #22c55e; color: #fff; }
.step-label { font-size: 0.7rem; color: #94a3b8; font-weight: 500; white-space: nowrap; }
.step-item.active .step-label { color: #2563eb; font-weight: 600; }
.step-item.done   .step-label { color: #22c55e; }

/* ── Alert ──────────────────────────────────────────────── */
.alert {
  display: flex; align-items: flex-start; gap: 0.75rem;
  padding: 0.875rem 1rem; border-radius: 10px;
  font-size: 0.875rem; margin-bottom: 1.25rem;
  background: #fef2f2; color: #991b1b;
  border: 1px solid #fee2e2; line-height: 1.4;
}
.alert svg { width: 18px; height: 18px; color: #ef4444; flex-shrink: 0; margin-top: 1px; }

/* ── Fields ─────────────────────────────────────────────── */
.field { margin-bottom: 1.25rem; }
.field label { display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.5rem; }
.field input {
  width: 100%; padding: 0.72rem 1rem;
  border: 1px solid #cbd5e1; border-radius: 10px;
  font-size: 0.95rem; color: #0f172a; background: #fff;
  box-sizing: border-box; outline: none;
  transition: all 0.2s;
}
.field input:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.08); }
.field.error input { border-color: #f43f5e; }
.field-error { display: block; font-size: 0.8rem; color: #e11d48; margin-top: 0.4rem; font-weight: 500; }

/* Input avec icône */
.input-icon-wrapper { position: relative; }
.input-icon { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8; }
.input-icon-wrapper input { padding-left: 2.5rem; }

/* ── Info banner ─────────────────────────────────────────── */
.info-banner {
  display: flex; align-items: center; gap: 0.6rem;
  background: #eff6ff; border: 1px solid #bfdbfe;
  border-radius: 10px; padding: 0.75rem 1rem;
  font-size: 0.875rem; color: #1e40af;
  margin-bottom: 1.25rem;
}
.info-banner svg { width: 18px; height: 18px; flex-shrink: 0; }

/* ── Timer ───────────────────────────────────────────────── */
.timer-block {
  display: flex; align-items: center; gap: 1rem;
  background: #f8fafc; border: 1px solid #e2e8f0;
  border-radius: 12px; padding: 0.9rem 1rem;
  margin-bottom: 1.25rem; transition: all 0.3s;
}
.timer-block.warning { background: #fffbeb; border-color: #fde68a; }
.timer-block.expired { background: #fef2f2; border-color: #fecaca; }

.timer-circle { position: relative; width: 56px; height: 56px; flex-shrink: 0; }
.timer-svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.timer-track { fill: none; stroke: #e2e8f0; stroke-width: 3.5; }
.timer-progress {
  fill: none; stroke: #2563eb; stroke-width: 3.5;
  stroke-linecap: round;
  transition: stroke-dasharray 1s linear;
}
.timer-block.warning .timer-progress { stroke: #f59e0b; }
.timer-block.expired .timer-progress  { stroke: #ef4444; }
.timer-text {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  font-size: 0.7rem; font-weight: 700; color: #0f172a;
  font-variant-numeric: tabular-nums;
}
.timer-info p { margin: 0; font-size: 0.875rem; color: #475569; }
.expired-text { color: #dc2626 !important; font-weight: 600; }

/* ── OTP inputs ──────────────────────────────────────────── */
.otp-inputs { display: flex; gap: 0.5rem; }
.otp-digit {
  flex: 1; height: 52px;
  border: 1.5px solid #cbd5e1;
  border-radius: 10px;
  text-align: center;
  font-size: 1.4rem; font-weight: 700; color: #0f172a;
  background: #f8fafc; outline: none;
  transition: all 0.15s;
  padding: 0;
  font-variant-numeric: tabular-nums;
  caret-color: transparent;
}
.otp-digit:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,0.08); }
.otp-digit.filled { background: #eff6ff; border-color: #93c5fd; }
.otp-digit.error  { border-color: #f43f5e; background: #fff8f8; }

/* ── Password strength ───────────────────────────────────── */
.password-strength { display: flex; align-items: center; gap: 0.6rem; margin-top: 0.5rem; }
.strength-bars { display: flex; gap: 3px; }
.bar { width: 28px; height: 4px; border-radius: 2px; background: #e2e8f0; transition: background 0.25s; }
.bar.active.red    { background: #ef4444; }
.bar.active.orange { background: #f59e0b; }
.bar.active.green  { background: #22c55e; }
.strength-label { font-size: 0.78rem; font-weight: 600; }
.strength-label.red    { color: #ef4444; }
.strength-label.orange { color: #f59e0b; }
.strength-label.green  { color: #22c55e; }

/* ── Input wrapper (password eye) ────────────────────────── */
.input-wrapper { position: relative; }
.input-wrapper input { padding-right: 3rem; }
.toggle-eye {
  position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; color: #64748b;
  padding: 4px; display: flex; border-radius: 6px; transition: all 0.15s;
}
.toggle-eye svg { width: 18px; height: 18px; }
.toggle-eye:hover { color: #0f172a; background: #f1f5f9; }

/* ── Buttons ─────────────────────────────────────────────── */
.btn-primary {
  width: 100%; padding: 0.85rem;
  background: #2563eb; color: #fff;
  border: none; border-radius: 10px;
  font-size: 0.95rem; font-weight: 600; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  gap: 0.5rem; margin-top: 0.5rem;
  transition: all 0.15s ease;
  box-shadow: 0 4px 6px -1px rgba(37,99,235,0.12);
}
.btn-primary:hover:not(:disabled) { background: #1d4ed8; box-shadow: 0 10px 15px -3px rgba(37,99,235,0.2); }
.btn-primary:active:not(:disabled) { transform: scale(0.99); }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
  border-radius: 50%; animation: spin 0.6s linear infinite; flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Resend ──────────────────────────────────────────────── */
.resend-block {
  text-align: center; margin-top: 1.25rem;
  font-size: 0.875rem; color: #64748b;
  display: flex; align-items: center; justify-content: center; gap: 0.35rem;
}
.btn-link {
  background: none; border: none; cursor: pointer;
  color: #2563eb; font-weight: 600; font-size: 0.875rem;
  padding: 0; text-decoration: none; transition: color 0.15s;
}
.btn-link:hover:not(:disabled) { color: #1d4ed8; text-decoration: underline; }
.btn-link:disabled { color: #94a3b8; cursor: not-allowed; }

/* ── Success ─────────────────────────────────────────────── */
.success-block { text-align: center; padding: 0.5rem 0; }
.success-icon { width: 64px; height: 64px; margin: 0 auto 1rem; color: #22c55e; }
.success-icon svg { width: 100%; height: 100%; }
.success-block h3 { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem; }
.success-block p  { font-size: 0.875rem; color: #64748b; margin: 0 0 1.5rem; line-height: 1.6; }

.redirect-bar {
  background: #e2e8f0; border-radius: 4px; height: 4px; overflow: hidden;
}
.redirect-progress {
  height: 100%; background: #22c55e; border-radius: 4px;
  transition: width 0.05s linear;
}

/* ── Footer link ─────────────────────────────────────────── */
.footer-link { text-align: center; margin-top: 1.75rem; font-size: 0.875rem; }
.footer-link a {
  display: inline-flex; align-items: center; gap: 0.3rem;
  color: #2563eb; font-weight: 600; text-decoration: none;
  transition: color 0.15s;
}
.footer-link a svg { width: 16px; height: 16px; }
.footer-link a:hover { color: #1d4ed8; text-decoration: underline; }

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 480px) {
  .auth-layout { background: #fff; align-items: flex-start; padding-top: 2rem; }
  .auth-card   { border: none; box-shadow: none; padding: 1rem; }
  .otp-digit   { height: 46px; font-size: 1.2rem; }
}
</style>
