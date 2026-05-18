/* Global app state via Svelte 5 runes — mirrors STATE from hr/data.js */
import type { Role } from './data';

export const state = $state({
  role: 'hr' as Role,
  sidebarOpen: false,
  notifOpen: false,
  candidateTab: 'overview',
  activeCandidateId: 'C-1001',
  activeVacancyId: 'V-2026-014',
  activeTeacherId: 'TCH-2024-018',
  masterFilter: { q:'', dept:'all', campus:'all', status:'all', employment:'all', sort:'name' },
});

export function setRole(r: Role) {
  state.role = r;
  state.sidebarOpen = false;
}
