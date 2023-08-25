<script>
  export let files;
  export let loadNewFile;

  function toggle(e) {
    e.preventDefault();
    e.target.nextElementSibling.classList.toggle('open');
  }

</script>

<style lang="scss">
  ul {
    margin: 0;
    padding: 0 0 0 1em;
    font-size: 1rem;
  }
  li {
    padding-bottom: .5rem;
    margin: 0;
    svg {
      height: 1em;
      width: 1em;
      transform: translateY(2px);
      fill: currentColor;
    }
  }
  h2 {
    font-size: 1rem;
    margin: 0;
    font-weight: normal;
    &:hover {
      cursor: pointer;
    }
  }
  a {
    text-decoration: none;
    color: #000;
    &:hover {
      text-decoration: underline;
    }
  }
  ul {
    :global(ul) {
      max-height: 0;
      overflow: hidden;
    }
    :global(.open) {
      max-height: none;
      margin-top: .5rem;
      margin-bottom: -.5rem;
    }
  }
</style>

<ul>
  {#each Object.entries(files) as [i,file]}
		{#if !isNaN(i)}
		<li>
			<a 
        href={file.filepath} 
        on:click|preventDefault={loadNewFile(file.filepath)}
      >
        <i>
          <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 24 24">
            <path d="M 6 2 C 4.9057453 2 4 2.9057453 4 4 L 4 20 C 4 21.094255 4.9057453 22 6 22 L 18 22 C 19.094255 22 20 21.094255 20 20 L 20 8 L 14 2 L 6 2 z M 6 4 L 13 4 L 13 9 L 18 9 L 18 20 L 6 20 L 6 4 z M 8 12 L 8 14 L 16 14 L 16 12 L 8 12 z M 8 16 L 8 18 L 16 18 L 16 16 L 8 16 z"></path>
          </svg>
        </i>
        {file.name.replace(/\.[^/.]+$/, "")}
      </a>
		</li>
		{:else}
    <li>
      <a href="#" on:click={toggle} on:keypress={toggle} class="folder">
        <i>
          <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 24 24">
            <path d="M 4 4 C 2.9057453 4 2 4.9057453 2 6 L 2 18 C 2 19.094255 2.9057453 20 4 20 L 20 20 C 21.094255 20 22 19.094255 22 18 L 22 8 C 22 6.9057453 21.094255 6 20 6 L 12 6 L 10 4 L 4 4 z M 4 6 L 9.171875 6 L 11.171875 8 L 20 8 L 20 18 L 4 18 L 4 6 z"></path>
          </svg>
        </i>
        {i}
      </a>
      <svelte:self files={JSON.parse(file)} {loadNewFile} />
    </li>
		{/if}
	{/each}
</ul>