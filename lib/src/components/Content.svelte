<script>
  import SvelteMarkdown from 'svelte-markdown';
  export let source;
</script>

<style lang="scss">
  .markdown-content {
    padding: 0 1em;
    img {
      max-width: 100%;
    }
  }
</style>

<div class="markdown-content">
  <SvelteMarkdown {source} />
</div>