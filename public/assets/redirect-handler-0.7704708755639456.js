var isValidRedirectUrl = (e) =>
    !![
      "pumble-dev://pumble.com",
      "pumble-stage://pumble.com",
      "pumble://pumble.com",
    ].includes(e) ||
    !(
      !isValidLoginUrl(e) ||
      (function() {
        try {
          const url = new URL(e);
          return !url.host.startsWith("localhost:");
        } catch (err) {
          return true;
        }
      })()
    ),
  isValidLoginUrl = (e) => {
    let r;
    try {
      r = new URL(e);
    } catch (e) {
      return !1;
    }
    return "http:" === r.protocol || "https:" === r.protocol;
  },
  getRedirectUrl = () => {
    var e =
      new URL(window.location).searchParams.get("redirp") ||
      sessionStorage.getItem("redirp");
    return isValidRedirectUrl(e) ? e : "";
  },
  getCallsRedirectUrl = () => {
    var e = new URL(window.location).searchParams.get("callsRedirect");
    return isValidRedirectUrl(e) ? e : "";
  },
  cleanupSessionStorage = () => {
    sessionStorage.removeItem("CAKE_ACCESS_TOKEN"),
      sessionStorage.removeItem("logins"),
      sessionStorage.removeItem("pumbleVerificationData");
  };
