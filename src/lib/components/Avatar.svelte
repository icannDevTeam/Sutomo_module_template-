<script lang="ts">
  import { initials } from '$lib/data';
  type Props = { name: string; size?: 'xs'|'sm'|'md'|'lg'|'xl' };
  let { name, size = 'sm' }: Props = $props();
</script>

<div class="av av-{size}">{initials(name)}</div>
