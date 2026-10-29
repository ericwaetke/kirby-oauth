<template>
	<form class="k-login-form k-oauth-login-form">
		<div v-if="redirecting" class="k-oauth-login-auto">
			{{ $t("thathoff.oauth.autoLoggingIn") }}
		</div>

		<template v-else>
			<k-headline>{{ $t("thathoff.oauth.signInWith") }}</k-headline>

			<k-button
				v-for="provider in providers"
				:key="provider.id"
				class="k-oauth-login-provider"
				:icon="provider.icon"
				:link="provider.href"
				target="_self"
				:size="'lg'"
				:text="provider.name"
				:theme="provider.theme ?? 'green'"
				variant="filled"
			/>
		</template>
	</form>
</template>

<script>
/**
 * Login form of the OAuth auth method.
 *
 * Receives the configured providers from `OauthMethod::form()`
 * and either redirects to the only provider (autoRedirect)
 * or shows one button per provider.
 */
export default {
	props: {
		/**
		 * Automatically redirect to the provider on load
		 * (only with a single provider; see the `autoRedirect` option)
		 */
		autoRedirect: Boolean,

		/**
		 * Error message from a previous OAuth attempt,
		 * e.g. access denied by the provider
		 */
		error: {
			type: String,
			default: null
		},

		/**
		 * Configured OAuth providers
		 * @value [{ id, name, href, icon, theme }]
		 */
		providers: {
			type: Array,
			default: () => []
		}
	},
	emits: ["error"],
	data() {
		return {
			redirecting: false
		};
	},
	created() {
		// show errors from the OAuth redirect
		// above the form via the login view
		if (this.error !== null && this.error !== "") {
			this.$emit("error", { message: this.error });
		}

		// hide the buttons right away when
		// there is nothing to choose from
		this.redirecting =
			this.autoRedirect === true && this.providers.length === 1;
	},
	mounted() {
		if (this.redirecting === true) {
			window.location = this.providers[0].href;
		}
	}
};
</script>

<style scoped>
.k-oauth-login-auto {
	color: var(--color-text-dimmed);
	text-align: center;
}

.k-oauth-login-provider {
	width: 100%;
}
</style>
