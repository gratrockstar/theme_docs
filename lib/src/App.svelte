<script>
	export let files;
	import Menu from './components/Menu.svelte';
	import SvelteMarkdown from 'svelte-markdown';

	let source = `Click an item in the menu to load documentation.`;

	let activeLink = '';

	function loadNewFile(url) {
		activeLink = url;
		const promise = fetch(url)
		.then(response => response.text())
		.then((results) => {
			source = results;
		});
	}

</script>

<style lang="scss">
	.theme-docs-container {
		display: flex;
		background-color: #fff;
		width: 95%;
		padding: 2%;
	}
	.theme-docs-menu {
		min-width: 300px;
		max-height: 50rem;
    	overflow: scroll;
	}
	.theme-docs-content {
		width: 100%;
		padding: 0 1em;
		position: sticky;
		top: 0;
		align-self: flex-start;

		:global(*:not(h1, h2, h3, h4, h5, h6)) {
			font-size: 1rem;
		}
		:global(img) {
			max-width: 80%;
			margin: 0 auto 1rem;
			display: block;
		}
		:global(table) {
			border: 1px solid #000;
			border-collapse: collapse;
		}
		:global(th) {
			border: 1px solid #000;
			text-align: left;
			padding: .5rem 1rem;
			background: rgba(#000, .1);
		}
		:global(td) {
			border: 1px solid #000;
			text-align: left;
			padding: .5rem 1rem;
		}
	}
</style>

<div class="theme-docs-container">
	<aside class="theme-docs-menu">
		<Menu {files} {activeLink} {loadNewFile} />
	</aside>
	<div class="theme-docs-content">
		<SvelteMarkdown {source} />
	</div>
</div>