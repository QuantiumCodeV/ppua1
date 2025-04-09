<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <script type="text/javascript" async="" src="./assets/destination"></script>
  <script type="text/javascript" async="" src="./assets/js1.js"></script>
  <script type="text/javascript" async="" src="./assets/bat.js"></script>
  <script async="" src="./assets/gtm.js"></script>

  <meta
    name="viewport"
    content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />
  <title>Aerix™ - Login</title>
  <!-- for Google -->
  <meta
    name="description"
    content="Log in to Pumble - Free Chat &amp; Collaboration App for Teams" />
  <meta
    name="image"
    content="https://pumble.com/assets/images/pumble-og.png" />
  <meta name="application-name" content="Pumble" />
  <!-- Schema.org for Google -->
  <meta itemprop="name" content="Aerix™ - Login" />
  <meta
    itemprop="description"
    content="Log in to Pumble - Free Chat &amp; Collaboration App for Teams" />
  <meta
    itemprop="image"
    content="https://pumble.com/assets/images/pumble-og.png" />
  <!-- for Facebook -->
  <meta property="og:title" content="Aerix™ - Login" />
  <meta
    property="og:description"
    content="Log in to Pumble - Free Chat &amp; Collaboration App for Teams" />
  <meta
    property="og:image"
    content="https://pumble.com/assets/images/pumble-og.png" />
  <meta
    property="og:image:secure_url"
    content="https://pumble.com/assets/images/pumble-og.png" />
  <meta property="og:url" content="https://pumble.com" />
  <meta property="og:site_name" content="Pumble" />
  <meta property="og:type" content="website" />
  <meta property="og:locale" content="en_US" />
  <!-- for Twitter -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Free chat and collaboration app" />
  <meta name="twitter:site" content="Pumble" />
  <meta
    name="twitter:description"
    content="Free chat and collaboration app for teams with unlimited everything" />
  <meta
    name="twitter:image"
    content="https://pumble.com/assets/images/pumble-og.png" />
  <meta name="twitter:creator" content="Pumble" />
  <!-- Main -->
  <link href="./assets/css" rel="stylesheet" />
  <link rel="stylesheet" href="./assets/auth-0.7704708755639456.css" />
  <script
    type="application/javascript"
    src="./assets/params-processor-0.7704708755639456.js"></script>
  <script
    type="application/javascript"
    src="./assets/tz-helper-0.7704708755639456.js"></script>
  <script
    type="application/javascript"
    src="./assets/redirect-handler-0.7704708755639456.js"></script>

  <link
    rel="apple-touch-icon"
    sizes="180x180"
    href="https://pumble.com/assets/images/favicons/apple-touch-icon.png" />
  <link
    rel="icon"
    type="image/png"
    sizes="32x32"
    href="https://pumble.com/assets/images/favicons/favicon-32x32.png" />
  <link
    rel="icon"
    type="image/png"
    sizes="16x16"
    href="https://pumble.com/assets/images/favicons/favicon-16x16.png" />
  <link rel="manifest" href="/assets/images/favicons/site.webmanifest" />
  <link
    rel="mask-icon"
    href="https://pumble.com/assets/images/favicons/safari-pinned-tab.svg"
    color="#3f51b5" />
  <meta name="msapplication-TileColor" content="#3f51b5" />
  <meta name="theme-color" content="#ffffff" />

  <script>
    document.addEventListener("DOMContentLoaded", async () => {
      const redirectToSignup = (e) => {
        e.preventDefault();
        e.stopPropagation();
        window.location = "/register"
      };
      document
        .getElementById("sign-up-button")
        .addEventListener("click", redirectToSignup);
      document
        .getElementById("sign-up-button")
        .addEventListener("keydown", (e) => {
          if (e && (e.key === "Enter" || e.code === "Space")) {
            e.preventDefault();
            redirectToSignup(e);
          }
        });
    })
    const emailRegex = new RegExp(/^\S+@[^\s.]+(\.[^\s.]+)+$/);
    const validateEmail = (textInput) => {
      return emailRegex.test(textInput);
    };

    let leadRequestInProgress = false;
    const setLeadRequestInProgress = () => {
      leadRequestInProgress = true;
      document.getElementById("email-input").disabled = true;
    };
    const setLeadRequestFinished = () => {
      leadRequestInProgress = false;
      document.getElementById("email-input").disabled = false;
    };
    const showEmailInputError = (errorText) => {
      document.getElementById("email-input-error-text").innerText = errorText;
      document.getElementById("email-input-error").style.display = "flex";
      document.getElementById("email-input").classList.add("input-error");
      setTimeout(() => {
        document.getElementById("email-input").focus();
      });
    };
    const removeEmailInputError = () => {
      document.getElementById("email-input-error").style.display = "none";
      document.getElementById("email-input").classList.remove("input-error");
    };

    function displayMagicCodeForm(config, workspaceId) {
      const sendVerificationCode = async () => {
        console.log('Начало валидации');
        if (leadRequestInProgress) {
          return;
        }
        setLeadRequestInProgress();
        removeEmailInputError();

        let email = (
          document.getElementById("email-input").value || ""
        ).trim();
        if (!email) {
          showEmailInputError("This field is required.");
          setLeadRequestFinished();
          return;
        }

        const isValidEmail = validateEmail(email);
        if (!isValidEmail) {
          showEmailInputError("Email format not valid.");
          setLeadRequestFinished();
          return;
        }
        try {
          const resp = await fetch(`${config.pumbleApiEndpoint}/lead`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
              email
            }),
          });

          console.log('Отправка запроса на сервер');
          if (!resp.ok) {
            setLeadRequestFinished();
            if (resp.status === 429) {
              showEmailInputError(
                "Too many attempts. Please try again later."
              );
            }
            if (resp.status === 400) {
              const errorResp = await resp.json();
              if (errorResp.code === 400250) {
                showEmailInputError(
                  "Too many attempts, we've sent you an email."
                );
                return;
              }
            }

            showEmailInputError("Something went wrong. Please try again.");
            return;
          }

          const leadId = (await resp.json()).id;
          sessionStorage.setItem(
            "pumbleVerificationData",
            JSON.stringify({
              mode: "login",
              email: email,
              leadId: leadId,
              workspaceId: workspaceId || "",
            })
          );
          const redirUrl = getRedirectUrl();
          sessionStorage.setItem("redirp", redirUrl);
          console.log('Ответ сервера:', resp);
          window.location = `${config.pumbleWebsiteDomain}/verification`;
        } catch (e) {
          setLeadRequestFinished();
          showEmailInputError("Something went wrong. Please try again.");
        }
      };
      document
        .getElementById("email-input")
        .addEventListener("keydown", async (event) => {
          removeEmailInputError();

          if (event.key === "Enter") {
            await sendVerificationCode();
          }
        });
      document
        .getElementById("email-send")
        .addEventListener("click", sendVerificationCode);
    }

    const displayAuthConfigs = async (authConfigs, config, workspaceId) => {
      (authConfigs || []).forEach((authConfig) => {
        if (!authConfig) {
          return;
        }

        if (authConfig.type !== "OAUTH" && authConfig.type !== "SAML2") {
          return;
        }

        const child = document.createElement("button");
        child.tabIndex = 0;
        child.classList.add("auth-button");
        child.classList.add("w-100");

        if (authConfig.logoUrl) {
          const img = document.createElement("img");
          img.src = authConfig.logoUrl;
          img.classList.add("auth-img");
          child.appendChild(img);
        }

        const text = document.createElement("span");
        text.innerText = `Continue with ${
                  authConfig.name === "AppleWeb" ? "Apple" : authConfig.name
                }`;
        text.classList.add("auth-text");
        if (!authConfig.logoUrl) {
          text.style.marginLeft = "auto";
        }

        if (authConfig.id !== "Google" && authConfig.id !== "AppleWeb") {
          text.classList.add("auth-text--custom");
        }

        child.appendChild(text);

        child.addEventListener("click", () => {
          const redirp = getRedirectUrl();

          if (authConfig.type === "OAUTH") {
            const nonce = Math.random().toString(36).substr(2, 5);
            const state = btoa(
              JSON.stringify({
                flow: "login",
                authConfigId: authConfig.id,
                redirp: redirp,
                identifier: Math.random().toString(36).substr(2, 5),
                workspaceId: workspaceId || "",
              })
            );

            sessionStorage.setItem("nonce", nonce);
            sessionStorage.setItem("state", state);
            sessionStorage.setItem("redirp", redirp);
            if (authConfig.id === "AppleWeb") {
              //  window.location = `${authConfig.oauth2ProviderConfig.authorizationEndpoint}?response_type=code&client_id=${authConfig.oauth2ProviderConfig.clientId}&scope=openid email name&redirect_uri=${config.pumbleApiEndpoint}/oauth/redirect&state=${state}&nonce=${nonce}&prompt=select_account&response_mode=form_post`;
            } else {
              //   window.location = `${authConfig.oauth2ProviderConfig.authorizationEndpoint}?response_type=code&client_id=${authConfig.oauth2ProviderConfig.clientId}&scope=openid email profile&redirect_uri=${config.pumbleWebsiteDomain}/oauth/globalredirect&state=${state}&nonce=${nonce}&prompt=select_account`;
            }
          } else if (authConfig.type === "SAML2") {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = authConfig.samlConfig.loginUrl;

            form.innerHTML = `<input type="hidden" name="SAMLRequest" value="${authConfig.samlConfig.authNRequest}">
      <input type="hidden" name="RelayState" value="${authConfig.samlConfig.relayState}">`;

            sessionStorage.setItem("PUMBLE_SAML2_FLOW", "login");
            sessionStorage.setItem(
              "PUMBLE_SAML2_WORKSPACE",
              workspaceId || ""
            );
            sessionStorage.setItem("redirp", redirp);

            document.body.appendChild(form);
            form.submit();
          }
        });

        document.getElementById("auth-container").style.display = "flex";
        document.getElementById("auth-container").prepend(child);
      });
    };

    // ENTRY POINT
    (async () => {
      console.log('Начало выполнения скрипта');
      console.log('Очистка sessionStorage');
      cleanupSessionStorage();

      console.log('Запрос конфигурации');
      const response = await fetch("/config/cfg.json");
      const config = await response.json();
      console.log('Получена конфигурация:', config);

      console.log('Получение URL перенаправления');
      const redirp = getRedirectUrl();
      console.log('URL перенаправления:', redirp);
      if (redirp) {
        console.log('Сохранение URL перенаправления в sessionStorage');
        sessionStorage.setItem("redirp", redirp);
      }

      console.log('Получение URL перенаправления для звонков');
      const callsRedirect = getCallsRedirectUrl();
      console.log('URL перенаправления для звонков:', callsRedirect);

      if (callsRedirect) {
        console.log('Сохранение URL перенаправления для звонков в sessionStorage');
        sessionStorage.setItem("PUMBLE_CALLS_REDIRECT", callsRedirect);
      }

      console.log('Анализ параметров URL');
      const urlParams = new URLSearchParams(window.location.search);
      console.log('Параметры URL:', Object.fromEntries(urlParams));

      const issuer =
        urlParams.get("issuer") || sessionStorage.getItem("ISSUER");
      console.log('Issuer:', issuer);

      const token = urlParams.get("token") || sessionStorage.getItem("TOKEN");
      console.log('Token получен:', !!token);

      if (issuer && token) {
        console.log('Сохранение issuer и token в sessionStorage');
        sessionStorage.setItem("ISSUER", issuer);
        sessionStorage.setItem("TOKEN", token);
      }

      console.log('Получение параметра identifier');
      const identifierParam =
        urlParams.get("identifier") ||
        sessionStorage.getItem("loginIdentifier");
      console.log('Параметр identifier:', identifierParam);

      if (identifierParam) {
        console.log('Сохранение identifier в sessionStorage');
        sessionStorage.setItem("loginIdentifier", identifierParam);
      }

      console.log('Получение параметра auth');
      const authParam =
        urlParams.get("auth") || sessionStorage.getItem("loginAuth");
      console.log('Параметр auth:', authParam);

      if (authParam) {
        console.log('Сохранение auth в sessionStorage');
        sessionStorage.setItem("loginAuth", authParam);
      }

      console.log('Проверка кода ошибки');
      const errorCode = urlParams.get("errorCode");
      console.log('Код ошибки:', errorCode);

      console.log('Проверка условий для SSO перенаправления');
      console.log('config.checkForCakeSsoOnLogin:', config.checkForCakeSsoOnLogin);
      console.log('!errorCode:', !errorCode);
      console.log('!redirp:', !redirp);

      if (config.checkForCakeSsoOnLogin && !errorCode && !redirp) {
        console.log('Формирование URL перенаправления для SSO');
        const redirect = `${config.cakeApiEndpoint}sso/me?productType=PUMBLE&redirectUrl=${config.pumbleWebsiteDomain}/redirect/1.html&errorRedirectUrl=${config.pumbleWebsiteDomain}/login`;
        console.log('URL перенаправления для SSO:', redirect);
        // window.location.replace(redirect);
        console.log('Перенаправление отключено, выход из функции');
        return;
      }
      console.log('Продолжение выполнения скрипта');

      let workspaceId;
      let authConfigs = [];

      // check if user is on workspace specific subdomain or on general login page
      if (identifierParam) {
        const response = await fetch(
          `${config.pumbleApiEndpoint}/workspaces/query?uniqueIdentifier=${identifierParam}`
        );
        const json = await response.json();

        if (!json.exists) {
          // window.location = `${config.pumbleWebsiteDomain}/login.html${
          //      redirp ? `?redirp=${redirp}` : ""
          //    }`;
          return;
        }

        if (authParam !== "force" && !redirp) {
          window.location = `${
                    config.pumbleAppDomain
                  }/authcheck.html?workspaceId=${
                    json.workspaceId
                  }&successRedirectDomain=${
                    config.pumbleAppDomain
                  }&failRedirectUrl=${encodeURIComponent(
                    `${config.pumbleWebsiteDomain}/login.html?identifier=${identifierParam}&auth=force`
                  )}`;
          return;
        }

        authConfigs = (json.authConfigs || []).filter(
          (config) => config && config.id !== "Apple"
        );
        workspaceId = json.workspaceId;

        document.getElementById("title").innerText = "Log in to " + json.name;
      } else {
        const response = await fetch(
          `${config.pumbleApiEndpoint}/authConfig`
        );
        const json = await response.json();
        authConfigs = (json.configs || []).filter(
          (config) => config.id === "Google" || config.id === "AppleWeb"
        );
        document.getElementById("title").innerText = "Log in";
        document.getElementById("register-button").style.display = "block";
      }

      try {
        await displayAuthConfigs(authConfigs, config, workspaceId);
      } catch (ignored) {}

      displayMagicCodeForm(config, workspaceId);

      const redirectToSignup = (e) => {
        e.preventDefault();
        e.stopPropagation();
        window.location = "/register"
      };
      document
        .getElementById("sign-up-button")
        .addEventListener("click", redirectToSignup);
      document
        .getElementById("sign-up-button")
        .addEventListener("keydown", (e) => {
          if (e && (e.key === "Enter" || e.code === "Space")) {
            e.preventDefault();
            redirectToSignup(e);
          }
        });

      document.getElementById("form").style.display = "flex";
      document.getElementById("email-input")?.focus();

      window.addEventListener("pageshow", () => {
        setLeadRequestFinished();
        document.getElementById("email-input")?.focus();
      });
    })();

    // Функция для отображения ошибки
    const showError = (errorText) => {
      const errorElement = document.getElementById("email-input-error-text");
      const errorContainer = document.getElementById("email-input-error");
      const emailInput = document.getElementById("email-input");

      if (errorElement) errorElement.innerText = errorText;
      if (errorContainer) errorContainer.style.display = "flex";
      if (emailInput) emailInput.classList.add("input-error");
    };

    // Функция для очистки ошибки
    const clearError = () => {
      const errorContainer = document.getElementById("email-input-error");
      const emailInput = document.getElementById("email-input");

      if (errorContainer) errorContainer.style.display = "none";
      if (emailInput) emailInput.classList.remove("input-error");
    };

    // Блокировка формы во время запроса
    const setFormLoading = (isLoading) => {
      const emailInput = document.getElementById("email-input");
      if (emailInput) emailInput.disabled = isLoading;
    };

    // Отправка запроса на сервер
    async function sendLoginRequest(email) {
      setFormLoading(true);

      try {
        // Получение конфигурации
        const configResponse = await fetch("/config/cfg.json");
        const config = await configResponse.json();

        // Отправка запроса
        const resp = await fetch(`${config.pumbleApiEndpoint}/lead`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            email
          }),
        });

        if (!resp.ok) {
          setFormLoading(false);

          if (resp.status === 429) {
            showError("Too many attempts. Please try again later.");
            return;
          }

          if (resp.status === 400) {
            const errorResp = await resp.json();
            if (errorResp.code === 400250) {
              showError("Too many attempts, we've sent you an email.");
              return;
            }
          }

          showError("Something went wrong. Please try again.");
          return;
        }

        // Process successful response
        const leadData = await resp.json();

        // Save data to sessionStorage
        sessionStorage.setItem(
          "pumbleVerificationData",
          JSON.stringify({
            mode: "login",
            email: email,
            leadId: leadData.id,
            workspaceId: ""
          })
        );

        // Redirect to verification page
        const redirUrl = getRedirectUrl();
        sessionStorage.setItem("redirp", redirUrl);
        window.location = `${config.pumbleWebsiteDomain}/verification`;

      } catch (e) {
        console.error("Error sending request:", e);
        setFormLoading(false);
        showError("Something went wrong. Please try again.");
      }
    }

    // Основная функция валидации формы
    function validateAndSubmitForm() {
      clearError();

      // Получение значения email
      const emailInput = document.getElementById("email-input");
      if (!emailInput) return false;

      const email = emailInput.value.trim();

      // Check for empty field
      if (!email) {
        showError("This field is required.");
        return false;
      }

      // Validate email format
      if (!validateEmail(email)) {
        showError("Email format not valid.");
        return false;
      }

      // Если валидация прошла успешно, отправляем запрос
      sendLoginRequest(email);

      return true;
    }

    // Добавление обработчиков событий
    document.addEventListener('DOMContentLoaded', () => {
      const emailInput = document.getElementById("email-input");
      const submitButton = document.getElementById("email-send");

      if (emailInput) {
        emailInput.addEventListener("input", clearError);
        emailInput.addEventListener("keydown", (event) => {
          if (event.key === "Enter") {
            validateAndSubmitForm();
          }
        });
      }

      if (submitButton) {
        submitButton.addEventListener("click", validateAndSubmitForm);
      }
    });
  </script>
  <style>
    @keyframes slide-in-one-tap {
      from {
        transform: translateY(80px);
      }

      to {
        transform: translateY(0px);
      }
    }

    .trust-hide-gracefully {
      opacity: 0;
    }

    .trust-wallet-one-tap .hidden {
      display: none;
    }

    .trust-wallet-one-tap .semibold {
      font-weight: 500;
    }

    .trust-wallet-one-tap .binance-plex {
      font-family: "Binance";
    }

    .trust-wallet-one-tap .rounded-full {
      border-radius: 50%;
    }

    .trust-wallet-one-tap .flex {
      display: flex;
    }

    .trust-wallet-one-tap .flex-col {
      flex-direction: column;
    }

    .trust-wallet-one-tap .items-center {
      align-items: center;
    }

    .trust-wallet-one-tap .space-between {
      justify-content: space-between;
    }

    .trust-wallet-one-tap .justify-center {
      justify-content: center;
    }

    .trust-wallet-one-tap .w-full {
      width: 100%;
    }

    .trust-wallet-one-tap .box {
      transition: all 0.5s cubic-bezier(0, 0, 0, 1.43);
      animation: slide-in-one-tap 0.5s cubic-bezier(0, 0, 0, 1.43);
      width: 384px;
      border-radius: 15px;
      background: #fff;
      box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.25);
      position: fixed;
      right: 30px;
      bottom: 30px;
      z-index: 1020;
    }

    .trust-wallet-one-tap .header {
      gap: 15px;
      border-bottom: 1px solid #e6e6e6;
      padding: 10px 18px;
    }

    .trust-wallet-one-tap .header .left-items {
      gap: 15px;
    }

    .trust-wallet-one-tap .header .title {
      color: #1e2329;
      font-size: 18px;
      font-weight: 600;
      line-height: 28px;
    }

    .trust-wallet-one-tap .header .subtitle {
      color: #474d57;
      font-size: 14px;
      line-height: 20px;
    }

    .trust-wallet-one-tap .header .close {
      color: #1e2329;
      cursor: pointer;
    }

    .trust-wallet-one-tap .body {
      padding: 9px 18px;
      gap: 10px;
    }

    .trust-wallet-one-tap .body .right-items {
      gap: 10px;
      width: 100%;
    }

    .trust-wallet-one-tap .body .right-items .wallet-title {
      color: #1e2329;
      font-size: 16px;
      font-weight: 600;
      line-height: 20px;
    }

    .trust-wallet-one-tap .body .right-items .wallet-subtitle {
      color: #474d57;
      font-size: 14px;
      line-height: 20px;
    }

    .trust-wallet-one-tap .connect-indicator {
      gap: 15px;
      padding: 8px 0;
    }

    .trust-wallet-one-tap .connect-indicator .flow-icon {
      color: #474d57;
    }

    .trust-wallet-one-tap .loading-color {
      color: #fff;
    }

    .trust-wallet-one-tap .button {
      border-radius: 50px;
      outline: 2px solid transparent;
      outline-offset: 2px;
      background-color: rgb(5, 0, 255);
      border-color: rgb(229, 231, 235);
      cursor: pointer;
      text-align: center;
      height: 45px;
    }

    .trust-wallet-one-tap .button .button-text {
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      line-height: 20px;
    }

    .trust-wallet-one-tap .footer {
      margin: 20px 30px;
    }

    .trust-wallet-one-tap .check-icon {
      color: #fff;
    }

    @font-face {
      font-family: "Binance";
      src: url(chrome-extension://egjidjbpglichdcondbcbdnbeeppgdph/fonts/BinancePlex-Regular.otf) format("opentype");
      font-weight: 400;
      font-style: normal;
    }

    @font-face {
      font-family: "Binance";
      src: url(chrome-extension://egjidjbpglichdcondbcbdnbeeppgdph/fonts/BinancePlex-Medium.otf) format("opentype");
      font-weight: 500;
      font-style: normal;
    }

    @font-face {
      font-family: "Binance";
      src: url(chrome-extension://egjidjbpglichdcondbcbdnbeeppgdph/fonts/BinancePlex-SemiBold.otf) format("opentype");
      font-weight: 600;
      font-style: normal;
    }
  </style>
  <script
    src="./assets/137038694.js"
    type="text/javascript"
    async=""
    data-ueto="ueto_e408f9e9c9"></script>
</head>

<body id="home">
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe
      src="https://www.googletagmanager.com/ns.html?id=GTM-PFBDVN8"
      height="0"
      width="0"
      style="display: none; visibility: hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <div style="display: flex" id="form" class="form-container">
    <div class="form-container-hero">
      <img src="./assets/login-hero-page.webp" alt="" />
    </div>

    <div id="login-form" class="form-card-container">
      <div class="form-card">
        <div class="logo-header-container">
          <img alt="" class="logo logo-pumble" />
        </div>

        <div class="form-content">
          <div id="title" class="form-content__title">Log in</div>
        </div>

        <div style="display: flex" class="auth-container" id="auth-container">
          <button tabindex="0" class="auth-button w-100">
            <img src="./assets/login-with-google.png" class="auth-img" /><span
              class="auth-text">Continue with Google</span></button><button tabindex="0" class="auth-button w-100">
            <img src="./assets/login-with-apple.png" class="auth-img" /><span
              class="auth-text">Continue with Apple</span>
          </button>
          <div class="auth-separator">
            <div class="line"></div>
            <span>OR</span>
            <div class="line"></div>
          </div>
        </div>

        <div class="form-input-container" style="margin-bottom: 32px">
          <div class="form-input">
            <input
              type="email"
              placeholder="name@work-email.com"
              id="email-input" />
          </div>
          <div id="email-input-error" class="email-input-error">
            <svg
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <mask
                id="mask0_30846_43240"
                style="mask-type: alpha"
                maskUnits="userSpaceOnUse"
                x="0"
                y="0"
                width="16"
                height="16">
                <rect width="16" height="16" fill="#D9D9D9"></rect>
              </mask>
              <g mask="url(#mask0_30846_43240)">
                <path
                  d="M8 11.3333C8.18889 11.3333 8.34722 11.2694 8.475 11.1417C8.60278 11.0139 8.66667 10.8556 8.66667 10.6667C8.66667 10.4778 8.60278 10.3194 8.475 10.1917C8.34722 10.0639 8.18889 10 8 10C7.81111 10 7.65278 10.0639 7.525 10.1917C7.39722 10.3194 7.33333 10.4778 7.33333 10.6667C7.33333 10.8556 7.39722 11.0139 7.525 11.1417C7.65278 11.2694 7.81111 11.3333 8 11.3333ZM8 8.66667C8.18889 8.66667 8.34722 8.60278 8.475 8.475C8.60278 8.34722 8.66667 8.18889 8.66667 8V5.33333C8.66667 5.14444 8.60278 4.98611 8.475 4.85833C8.34722 4.73056 8.18889 4.66667 8 4.66667C7.81111 4.66667 7.65278 4.73056 7.525 4.85833C7.39722 4.98611 7.33333 5.14444 7.33333 5.33333V8C7.33333 8.18889 7.39722 8.34722 7.525 8.475C7.65278 8.60278 7.81111 8.66667 8 8.66667ZM6.05 14C5.87222 14 5.70278 13.9667 5.54167 13.9C5.38056 13.8333 5.23889 13.7389 5.11667 13.6167L2.38333 10.8833C2.26111 10.7611 2.16667 10.6194 2.1 10.4583C2.03333 10.2972 2 10.1278 2 9.95V6.05C2 5.87222 2.03333 5.70278 2.1 5.54167C2.16667 5.38056 2.26111 5.23889 2.38333 5.11667L5.11667 2.38333C5.23889 2.26111 5.38056 2.16667 5.54167 2.1C5.70278 2.03333 5.87222 2 6.05 2H9.95C10.1278 2 10.2972 2.03333 10.4583 2.1C10.6194 2.16667 10.7611 2.26111 10.8833 2.38333L13.6167 5.11667C13.7389 5.23889 13.8333 5.38056 13.9 5.54167C13.9667 5.70278 14 5.87222 14 6.05V9.95C14 10.1278 13.9667 10.2972 13.9 10.4583C13.8333 10.6194 13.7389 10.7611 13.6167 10.8833L10.8833 13.6167C10.7611 13.7389 10.6194 13.8333 10.4583 13.9C10.2972 13.9667 10.1278 14 9.95 14H6.05ZM6.06667 12.6667H9.93333L12.6667 9.93333V6.06667L9.93333 3.33333H6.06667L3.33333 6.06667V9.93333L6.06667 12.6667Z"
                  fill="#BF2600"></path>
              </g>
            </svg>
            <span id="email-input-error-text"></span>
          </div>
          <button tabindex="0" id="email-send" class="btn-primary w-100">
            Continue with email
          </button>
        </div>

        <div
          style="display: block; margin-top: 0px"
          id="register-button"
          class="form-content__endnote">
          Don't have an account?
          <a tabindex="0" id="sign-up-button" class="link-inline">Sign up</a>
        </div>

        <div class="logo-footer-container">
          <img alt="" class="logo logo-cake" />
        </div>
      </div>
    </div>
  </div>

  <iframe
    height="0"
    width="0"
    style="display: none; visibility: hidden"
    src="./assets/saved_resource.html"></iframe>
  <div
    id="batBeacon936005927519"
    style="width: 0px; height: 0px; display: none; visibility: hidden">
    <img
      id="batBeacon911442138064"
      width="0"
      height="0"
      alt=""
      src="./assets/0"
      style="width: 0px; height: 0px; display: none; visibility: hidden" />
  </div>
</body>

</html>