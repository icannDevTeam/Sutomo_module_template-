<script lang="ts">
  import Icon from '$lib/components/Icon.svelte';
  type Props = { title: string; sub?: string; icon?: string };
  let { title, sub = 'Port in progress — coming from hr/pages.js', icon = 'construction' }: Props = $props();
</script>

<div class="card p-10 text-center">
  <div class="w-12 h-12 rounded-xl mx-auto mb-3 bg-blue-50 text-blue-700 flex items-center justify-center">
    <Icon name={icon} class="w-5 h-5" />
  </div>
  <h1 class="font-display font-bold text-xl">{title}</h1>
  <p class="text-sm text-[color:var(--ink-mute)] mt-1">{sub}</p>
  <a class="btn btn-primary btn-sm mt-4" href="/">
    <Icon name="arrow-left" class="w-3.5 h-3.5" /> Back to dashboard
  </a>
</div>
