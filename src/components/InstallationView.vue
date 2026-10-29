<template>
	<k-panel-outside class="k-installation-view">
		<h1 class="sr-only">
			{{ $t("installation") }}
		</h1>

		<!-- installation complete -->
		<k-stack v-if="isComplete">
			<p>{{ $t("installation.completed") }}.</p>

			<k-button
				:text="$t('login')"
				icon="check"
				link="/login"
				size="lg"
				theme="positive"
				variant="filled"
			/>
		</k-stack>

		<!-- ready to be installed -->
		<div v-else-if="isReady || showOauth" class="k-stack">
			<form v-if="isReady" class="k-stack" @submit.prevent="install">
				<k-fieldset :fields="fields" :value="user" @input="user = $event" />
				<k-button
					:text="$t('install')"
					icon="check"
					size="lg"
					theme="positive"
					type="submit"
					variant="filled"
				/>
			</form>

			<!-- oauth provider buttons -->
			<OAuth v-if="showOauth" :providers="providers" :error="oauthError" />
		</div>

		<!-- not meeting requirements -->
		<k-stack v-else>
			<k-stack gap="var(--spacing-3)">
				<k-headline>
					{{ $t("installation.issues.headline") }}
				</k-headline>

				<k-checklist theme="negative" class="k-installation-issues">
					<li v-for="issue in issues" :key="issue">
						<span v-safe-html="issue" />
					</li>
				</k-checklist>
			</k-stack>

			<k-button
				:text="$t('retry')"
				icon="refresh"
				size="lg"
				theme="positive"
				variant="filled"
				@click="$panel.reload"
			/>
		</k-stack>
	</k-panel-outside>
</template>

<script>
import OAuth from "./OAuth.vue";

/**
 * Installation view with the OAuth provider buttons added
 * to the core installation screen of Kirby 6.
 */
export default {
	components: {
		OAuth
	},
	props: {
		isInstallable: Boolean,
		isInstalled: Boolean,
		isOk: Boolean,
		requirements: Object,
		translations: Array
	},
	data() {
		return {
			user: {
				name: "",
				email: "",
				language: this.$panel.translation.code,
				password: "",
				role: "admin"
			},
			oauthSettings: {},
			oauthError: null
		};
	},
	computed: {
		fields() {
			return {
				email: {
					label: this.$t("email"),
					type: "email",
					link: false,
					autofocus: true,
					required: true
				},
				password: {
					label: this.$t("password"),
					type: "password",
					placeholder: this.$t("password") + " …",
					required: true
				},
				language: {
					label: this.$t("language"),
					type: "select",
					options: this.translations,
					icon: "translate",
					empty: false,
					required: true
				}
			};
		},
		isComplete() {
			return this.isOk && this.isInstalled;
		},
		isReady() {
			return this.isOk && this.isInstallable;
		},
		issues() {
			const issues = [];

			if (this.isInstallable === false) {
				issues.push(this.$th("installation.disabled"));
			}

			for (const extension in this.requirements.extensions) {
				if (this.requirements.extensions[extension] === false) {
					issues.push(
						this.$th("installation.issues.extension", { extension })
					);
				}
			}

			for (const type of ["accounts", "content", "media", "sessions"]) {
				if (this.requirements[type] === false) {
					issues.push(this.$th("installation.issues." + type));
				}
			}

			return issues;
		},
		providers() {
			return Object.values(this.oauthSettings.providers ?? []);
		},
		showOauth() {
			return (
				this.oauthSettings.enabled === true && this.providers.length > 0
			);
		}
	},
	mounted() {
		this.loadOauth();
	},
	methods: {
		async install() {
			try {
				await this.$api.system.install(this.user);
				await this.$panel.reload({
					globals: ["system", "translation"]
				});

				this.$panel.notification.success({
					message: this.$t("welcome") + "!",
					icon: "smile"
				});
			} catch (error) {
				this.$panel.error(error);
			}
		},
		async loadOauth() {
			this.oauthSettings = await this.$api.get("oauth/settings");
			this.oauthError = (await this.$api.get("oauth/oauthError")).msg;
		}
	}
};
</script>

<style>
.k-installation-view > .k-stack {
	max-width: 25rem;
	margin: 0 auto;
	gap: var(--spacing-6);
	padding: var(--spacing-6);
	background: light-dark(var(--color-white), var(--color-gray-950));
	border-radius: var(--rounded);
}

.k-installation-issues li {
	--checklist-marker: "⚠";
	padding: var(--spacing-2);
	color: var(--theme-color-text-highlight);
	background: var(--theme-color-back);
	border-radius: var(--rounded);
}
.k-checklist li::before {
	color: inherit;
}
.k-installation-issues li code {
	font: inherit;
	font-weight: var(--font-bold);
}
</style>
