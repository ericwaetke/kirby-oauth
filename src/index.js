import InstallationView from "./components/InstallationView.vue";
import OauthLoginForm from "./components/OauthLoginForm.vue";

panel.plugin("thathoff/oauth", {
  components: {
    "k-installation-view": InstallationView,
    "k-oauth-login-form": OauthLoginForm
  }
});
