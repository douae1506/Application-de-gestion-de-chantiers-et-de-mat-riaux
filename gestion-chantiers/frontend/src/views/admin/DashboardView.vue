<template>
  <div class="dashboard">
    <div class="page-header">
      <div>
        <h1>Tableau de bord</h1>
        <p>Bienvenue, {{ auth.user?.prenom }} — Aperçu général du mois en cours</p>
      </div>
     
    </div>

    
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
const auth = useAuthStore()

</script>

<style scoped>
.page-header { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem; }
.page-header h1 { font-size:1.4rem;font-weight:700;color:#1e2a4a;margin:0 0 .2rem; }
.page-header p { font-size:.875rem;color:#8a94b2;margin:0; }
</style>
