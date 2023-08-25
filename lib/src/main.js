import App from './App.svelte';

const app = new App({
	target: document.getElementById('theme-docs'),
	props: {
		files: td.files, 
	}
});

export default app;