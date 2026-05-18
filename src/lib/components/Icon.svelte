<script lang="ts" module>
  // Explicit named imports keep the bundle small (vs `import { icons }` which
  // pulls in ALL of lucide-svelte → ~8 MB). Add new icons as the app grows.
  import {
    UserRound, Briefcase, School, UsersRound, GraduationCap, Wallet, Landmark, ShieldCheck,
    LayoutDashboard, BarChart3, Kanban, ClipboardCheck, Database, Settings, Bell, Send,
    ListChecks, Folder, CalendarClock, FileWarning,
    ChevronLeft, ChevronRight, ChevronsUpDown, ArrowRight, ArrowLeft, Plus, Download,
    MoreHorizontal, Menu, Search, Eye, Filter, Mail, Edit3, HelpCircle, Video, Star,
    Clock, CheckCircle2, Undo2, Timer, Construction, Circle,
  } from 'lucide-svelte';

  export const ICONS: Record<string, any> = {
    'user-round': UserRound, 'briefcase': Briefcase, 'school': School, 'users-round': UsersRound,
    'graduation-cap': GraduationCap, 'wallet': Wallet, 'landmark': Landmark, 'shield-check': ShieldCheck,
    'layout-dashboard': LayoutDashboard, 'bar-chart-3': BarChart3, 'kanban': Kanban,
    'clipboard-check': ClipboardCheck, 'database': Database, 'settings': Settings, 'bell': Bell,
    'send': Send, 'list-checks': ListChecks, 'folder': Folder,
    'calendar-clock': CalendarClock, 'file-warning': FileWarning,
    'chevron-left': ChevronLeft, 'chevron-right': ChevronRight, 'chevrons-up-down': ChevronsUpDown,
    'arrow-right': ArrowRight, 'arrow-left': ArrowLeft, 'plus': Plus, 'download': Download,
    'more-horizontal': MoreHorizontal, 'menu': Menu, 'search': Search, 'eye': Eye,
    'filter': Filter, 'mail': Mail, 'edit-3': Edit3, 'help-circle': HelpCircle, 'video': Video,
    'star': Star, 'clock': Clock, 'check-circle-2': CheckCircle2, 'undo-2': Undo2,
    'timer': Timer, 'construction': Construction, 'circle': Circle,
  };
</script>

<script lang="ts">
  type Props = { name: string; class?: string };
  let { name, class: klass = 'w-4 h-4' }: Props = $props();
  const Cmp = $derived(ICONS[name] ?? Circle);
</script>

<Cmp class={klass} strokeWidth={2} />
