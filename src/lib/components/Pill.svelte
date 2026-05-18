<script lang="ts">
  type Tone = 'gray'|'green'|'amber'|'red'|'blue'|'violet'|'brown'|'gold'|'maroon';
  type Props = { tone?: Tone; dot?: boolean; children?: any };
  let { tone = 'gray', dot = false, children }: Props = $props();
</script>

<span class="pill pill-{tone}">
  {#if dot}<span class="pill-dot"></span>{/if}
  {@render children?.()}
</span>
