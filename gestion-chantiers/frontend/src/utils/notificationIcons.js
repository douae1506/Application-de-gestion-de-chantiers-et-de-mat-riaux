// Mapping "type de notification" → icône professionnelle + couleur.
// Remplace les emojis envoyés par le backend (🔔🚧✅…) par de vraies
// icônes vectorielles (lucide-vue-next), avec un dégradé de fond cohérent
// par catégorie (info / succès / attention / danger / transfert).

import {
  Building2,
  FolderKanban,
  PackagePlus,
  PackageMinus,
  ArrowLeftRight,
  TriangleAlert,
  PackageX,
  CircleCheckBig,
  CircleX,
  UserPlus,
  UserMinus,
  Pencil,
  Trash2,
  Clock,
  Bell,
} from 'lucide-vue-next'

// Palette par "ton" — dégradés doux, cohérents avec le thème bleu/dynamique.
export const NOTIF_TONES = {
  blue:   { bg: 'linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%)', fg: '#1d4ed8' },
  green:  { bg: 'linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%)', fg: '#15803d' },
  amber:  { bg: 'linear-gradient(135deg, #fef3c7 0%, #fde68a 100%)', fg: '#b45309' },
  red:    { bg: 'linear-gradient(135deg, #fee2e2 0%, #fecaca 100%)', fg: '#b91c1c' },
  purple: { bg: 'linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%)', fg: '#6d28d9' },
  slate:  { bg: 'linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%)', fg: '#475569' },
}

const NOTIF_ICON_MAP = {
  chantier_created: { icon: Building2, tone: 'blue' },
  chantier_updated: { icon: Pencil, tone: 'amber' },
  chantier_deleted: { icon: Trash2, tone: 'red' },
  chantier_termine: { icon: CircleCheckBig, tone: 'green' },
  chantier_retard:  { icon: Clock, tone: 'red' },
  chantier_echeance: { icon: Clock, tone: 'amber' },

  projet_created: { icon: FolderKanban, tone: 'blue' },
  projet_updated: { icon: Pencil, tone: 'amber' },
  projet_termine: { icon: CircleCheckBig, tone: 'green' },
  projet_retard:  { icon: Clock, tone: 'red' },
  projet_echeance: { icon: Clock, tone: 'amber' },

  stock_entree:    { icon: PackagePlus, tone: 'green' },
  stock_sortie:    { icon: PackageMinus, tone: 'blue' },
  stock_transfert: { icon: ArrowLeftRight, tone: 'purple' },
  stock_seuil:     { icon: TriangleAlert, tone: 'amber' },
  stock_rupture:   { icon: PackageX, tone: 'red' },

  materiel_valide:  { icon: CircleCheckBig, tone: 'green' },
  demande_annulee:  { icon: CircleX, tone: 'red' },

  user_created: { icon: UserPlus, tone: 'blue' },
  user_deleted: { icon: UserMinus, tone: 'red' },
}

const DEFAULT_ICON = { icon: Bell, tone: 'slate' }

export function getNotifIcon(type) {
  return NOTIF_ICON_MAP[type] || DEFAULT_ICON
}

export function getNotifToneStyle(type) {
  const { tone } = getNotifIcon(type)
  return NOTIF_TONES[tone] || NOTIF_TONES.slate
}
