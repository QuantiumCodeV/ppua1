<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <script type="text/javascript" async="" src="./assets/destination"></script>
  <script type="text/javascript" async="" src="./assets/jsfile.js"></script>
  <script type="text/javascript" async="" src="./assets/bat.js"></script>

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
  <link
    rel="manifest"
    href="https://pumble.com/assets/images/favicons/site.webmanifest" />
  <link
    rel="mask-icon"
    href="https://pumble.com/assets/images/favicons/safari-pinned-tab.svg"
    color="#3f51b5" />
  <meta name="msapplication-TileColor" content="#3f51b5" />
  <meta name="theme-color" content="#ffffff" />
  <script src="./assets/index.umd.js"></script>

  <script>
    const parseNameFromEmail = (email) => {
      try {
        const [username] = email.split("@");
        return username
          .split(/[.\-_]/g)
          .filter((part) => !!part && !!part.trim())
          .map(
            (part) =>
            String(part).charAt(0).toUpperCase() + String(part).slice(1)
          )
          .join(" ")
          .trim();
      } catch (e) {
        console.log(e);
      }

      return "";
    };

    const getCookie = (name) => {
      try {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(";").shift();
      } catch (e) {
        console.log(`error retrieving data for key ${name}`);
      }
      return "";
    };

    const generateGuid = () => {
      const S4 = () => {
        return (((1 + Math.random()) * 0x10000) | 0)
          .toString(16)
          .substring(1);
      };

      return (
        S4() +
        S4() +
        "-" +
        S4() +
        "-" +
        S4() +
        "-" +
        S4() +
        "-" +
        S4() +
        S4() +
        S4()
      );
    };

    const getBrowserId = () => {
      try {
        const storageBrowserId = localStorage.getItem("PUMBLE_BROWSER_ID");
        if (storageBrowserId) {
          return storageBrowserId;
        }
        const newBrowserId = generateGuid();
        localStorage.setItem("PUMBLE_BROWSER_ID", newBrowserId);
        return newBrowserId;
      } catch (e) {
        console.error(e);
      }

      return "";
    };

    window.addEventListener("load", async () => {
      const response = await fetch("/config/cfg.json");
      const config = await response.json();

      const openNewWorkspaceViaCake = async ({
        accessToken,
        exchangeToken,
        workspaceId,
      }) => {
        try {
          if (sessionStorage.getItem("CAKE_ACCESS_TOKEN")) {
            sessionStorage.removeItem("CAKE_ACCESS_TOKEN");
            sessionStorage.removeItem("logins");
            //window.location = `${config.cakeApiEndpoint}sso/me?redirectUrl=${config.pumbleAppDomain}/landing&workspaceExternalId=${workspaceId}&productType=pumble`;
            return;
          }
          if (!config.waitForMigrationAfterWorkspaceCreation) {
            //window.location = `${config.pumbleAppDomain}/landing?exchangeToken=${exchangeToken}`;
            return;
          }
          const timeout = (to) =>
            new Promise((resolve) => setTimeout(resolve, to));
          await timeout(250);

          let hasOrg = false;
          for (let i = 0; i < 10; i++) {
            const info = await fetch(`${config.pumbleApiEndpoint}/info`, {
              headers: {
                authtoken: accessToken,
              },
            });
            const infoResp = await info.json();
            if (infoResp.organization && infoResp.organization.id) {
              hasOrg = true;
              break;
            }
            await timeout(1_000);
          }

          sessionStorage.removeItem("CAKE_ACCESS_TOKEN");
          sessionStorage.removeItem("logins");

          if (!hasOrg) {
            console.log("Has no org, logging in via Pumble exchange token");
            // window.location = `${config.pumbleAppDomain}/landing?exchangeToken=${exchangeToken}`;
            return;
          }

          const peTokenResp = await fetch(
            `${config.pumbleApiEndpoint}/integrations/cake/exchange`, {
              method: "POST",
              headers: {
                "Content-Type": "application/json"
              },
              body: JSON.stringify({
                exchangeToken
              }),
            }
          );
          const {
            token
          } = await peTokenResp.json();
          console.log("Has org, logging in via CAKE");
          window.location.replace(
            `${config.cakeApiEndpoint}products/exchange?token=${token}&redirectUrl=${config.pumbleAppDomain}/landing&productType=pumble`
          );
        } catch (e) {
          console.error(
            "failed login via cake, logging in via pumble exchange token",
            e
          );
          // window.location = `${config.pumbleAppDomain}/landing?exchangeToken=${exchangeToken}`;
        }
      };

      let selectedCakeOrganizationId;

      const initiateHelpDropdown = () => {
        document
          .getElementById("help-menu")
          .addEventListener("click", (e) => {
            e.stopPropagation();
          });
        document
          .getElementById("help-icon")
          .addEventListener("click", (e) => {
            e.stopPropagation();
            document.getElementById("dropdown-overlay").style.display =
              "block";
            document.getElementById("help-menu").style.display = "block";
            document.getElementById("profile-menu").style.display = "none";
          });

        document
          .getElementById("dropdown-overlay")
          .addEventListener("click", () => {
            document.getElementById("dropdown-overlay").style.display =
              "none";
            document.getElementById("help-menu").style.display = "none";
            document.getElementById("profile-menu").style.display = "none";
          });
      };

      const initiateProfileDropdown = async () => {
        const cakeAccessToken = sessionStorage.getItem("CAKE_ACCESS_TOKEN");
        if (!cakeAccessToken) {
          return;
        }

        const userResp = await fetch(
          `${config.cakeApiEndpoint}organizations/users/me`, {
            headers: {
              "access-token": cakeAccessToken,
            },
          }
        );
        const {
          email,
          settings: {
            name = "",
            profilePictureUrl,
            profilePictureBgColors
          },
        } = await userResp.json();
        const getInitials = (name = "") => {
          if (!name) {
            return "";
          }
          try {
            return (
                name
                .split(" ")
                .map((w) => (w || "").charAt(0))
                .filter((w) => !!w)
                .join("") + name.charAt(1)
              )
              .substring(0, 2)
              .trim();
          } catch (e) {
            return name.substring(0, 2);
          }
        };

        document.getElementById("profile-icon").style.display = "block";
        if (profilePictureUrl) {
          document.getElementById(
            "profile-icon"
          ).style.backgroundImage = `url('${profilePictureUrl}')`;
          document.getElementById(
            "menu-image"
          ).style.backgroundImage = `url('${profilePictureUrl}')`;
        } else {
          const isDarkMode =
            window.matchMedia &&
            window.matchMedia("(prefers-color-scheme: dark)").matches;
          document.getElementById("profile-icon").style.background =
            isDarkMode ?
            profilePictureBgColors.dark :
            profilePictureBgColors.light;
          document.getElementById("profile-icon").innerText =
            getInitials(name);
          document.getElementById("menu-image").style.background = isDarkMode ?
            profilePictureBgColors.dark :
            profilePictureBgColors.light;
          document.getElementById("menu-image").innerText = getInitials(name);
        }
        document.getElementById("menu-name").innerText = name;
        document.getElementById("menu-email").innerText = email;

        document
          .getElementById("profile-menu")
          .addEventListener("click", (e) => {
            e.stopPropagation();
          });
        document
          .getElementById("profile-icon")
          .addEventListener("click", (e) => {
            e.stopPropagation();
            document.getElementById("dropdown-overlay").style.display =
              "block";
            document.getElementById("help-menu").style.display = "none";
            document.getElementById("profile-menu").style.display = "block";
          });

        document
          .getElementById("dropdown-overlay")
          .addEventListener("click", () => {
            document.getElementById("dropdown-overlay").style.display =
              "none";
            document.getElementById("help-menu").style.display = "none";
            document.getElementById("profile-menu").style.display = "none";
          });

        document
          .getElementById("menu-logout")
          .addEventListener("click", async () => {
            window.location = `${config.cakeApiEndpoint}auth/logout?productType=PUMBLE&redirectUrl=${config.pumbleWebsiteDomain}/login`;
          });
      };

      const initiateSwitcher = async () => {
        const cakeAccessToken = sessionStorage.getItem("CAKE_ACCESS_TOKEN");
        if (!cakeAccessToken) {
          return;
        }

        const S = window.CakeAppSwitcher;
        const hostElement = document.getElementById("switcher-host");
        const theme =
          window.matchMedia &&
          window.matchMedia("(prefers-color-scheme: dark)").matches ?
          S.getSharedDarkTheme({
            "--cas-toggle-button-background-color": "var(--elevation-surface-raised)",
            "--cas-toggle-button-icon-color": "var(--text-secondary)",
          }) :
          S.getSharedLightTheme({
            "--cas-toggle-button-background-color": "var(--elevation-surface-raised)",
            "--cas-toggle-button-icon-color": "var(--text-secondary)",
          });

        S.createCakeAppSwitcher({
          apiConfig: {
            apiBaseUrl: config.cakeApiEndpoint,
            auth: {
              accessToken: cakeAccessToken,
            },
          },
          hostApp: S.CakeProduct.Pumble,
          hostElement,
          productSettings: {
            [S.CakeProduct.Pumble]: {
              workspaceAction: {
                openInSameTab: true,
              },
            },
          },
          cssVariableOverrides: theme,
        });
      };

      let createWorkspaceRequestInProgress = false;
      const setCreateWorkspaceRequestInProgress = () => {
        createWorkspaceRequestInProgress = true;
        document.getElementById("name-input").disabled = true;
        document.getElementById("workspace-name-input").disabled = true;
        document.getElementById("tos-input").disabled = true;
      };
      const setCreateWorkspaceRequestFinished = () => {
        createWorkspaceRequestInProgress = false;
        document.getElementById("name-input").disabled = false;
        document.getElementById("workspace-name-input").disabled = false;
        document.getElementById("tos-input").disabled = false;
      };
      const createWorkspace = async () => {
        if (createWorkspaceRequestInProgress) {
          return;
        }
        const tosVisible =
          document.getElementById("tos-container").style.display !== "none";
        const tosSelected = document.getElementById("tos-input").checked;
        let hasInputError = false;
        if (tosVisible && !tosSelected) {
          document.getElementById("tos-input-error").style.display = "flex";
          hasInputError = true;
        }
        const workspaceName = (
          document.getElementById("workspace-name-input").value || ""
        ).trim();
        if (!workspaceName) {
          document.getElementById("workspace-input-error").style.display =
            "flex";
          document.getElementById("workspace-input-error-text").innerText =
            "This field is required.";
          hasInputError = true;
        }
        const fullName =
          (document.getElementById("name-input").value || "").trim() || null;
        if (!fullName) {
          document.getElementById("name-input-error").style.display = "flex";
          document.getElementById("name-input-error-text").innerText =
            "This field is required.";
          hasInputError = true;
        }
        if (hasInputError) {
          setCreateWorkspaceRequestFinished();
          return;
        }
        setCreateWorkspaceRequestInProgress();

        const leadId = JSON.parse(sessionStorage.getItem("logins") || "{}")
          ?.magicLogins?.lead?.id;
        if (!leadId) {
          window.location = `${config.pumbleWebsiteDomain}/login`;
          return;
        }

        const timeZoneId = getTimeZoneWithFallback();

        try {
          const utmParamsRaw = localStorage.getItem(
            "pmbl-utm-analytics-params"
          );
          let utmParams = {};
          if (utmParamsRaw) {
            try {
              utmParams = JSON.parse(utmParamsRaw);
              utmParams.utmAttribution =
                localStorage.getItem("pmbl-utm-attribution-params") || "oc";
            } catch (e) {
              console.log("issue parsing cpc params", e);
              utmParams = {};
            }
          }
          const seoAnalyticsUserId =
            localStorage.getItem("pumble-seo-events-user") || "";
          const seoAnalyticsPageUrl =
            localStorage.getItem("pumble-seo-events-page-url") || "";

          // affiliate cookie
          const affiliateTid = config.fpAffiliateEnabled ?
            getCookie("_fprom_tid") || "" :
            "";

          const affiliateRef = config.fpAffiliateEnabled ?
            getCookie("_fprom_ref") || "" :
            "";

          const resp = await fetch(
            `${config.pumbleApiEndpoint}/createWorkspace`, {
              method: "POST",
              headers: {
                "Content-Type": "application/json"
              },
              body: JSON.stringify({
                ...utmParams,
                leadId,
                workspaceName,
                timeZoneId,
                affiliateTid,
                affiliateRef,
                seoAnalyticsUserId,
                seoAnalyticsPageUrl,
                organizationId: selectedCakeOrganizationId,
                fullName,
              }),
            }
          );
          if (!resp.ok) {
            document.getElementById("workspace-input-error").style.display =
              "flex";
            document.getElementById("workspace-input-error").innerText = (
              await resp.json()
            ).message;
            setCreateWorkspaceRequestFinished();
            return;
          }
          const respJson = await resp.json();

          if (config.analyticsEnabled) {
            try {
              dataLayer.push({
                event: "sign_up",
                user_id: respJson.workspaceUser.id,
                workspace_id: respJson.workspace.id,
                workspace_name: respJson.workspace.name,
                user_role: respJson.workspaceUser.role,
                browser_id: getBrowserId(),
              });
            } catch (e) {
              console.error(e);
            }
          } else {
            console.log("Analytics not enabled, event ", {
              event: "sign_up",
              user_id: respJson.workspaceUser.id,
              workspace_id: respJson.workspace.id,
              workspace_name: respJson.workspace.name,
              user_role: respJson.workspaceUser.role,
              browser_id: getBrowserId(),
            });
          }

          const exchangeToken = respJson.exchangeToken;
          const accessToken = respJson.accessToken;
          const workspaceId = respJson.workspace.id;
          // Перенаправление пользователя
          const redirUrl = getRedirectUrl();
          if (redirUrl) {
            // Скрываем форму и показываем сообщение о перенаправлении
            const formContainer = document.getElementById("form-container");
            const redirectionElement = document.getElementById("redirection");

            if (formContainer) formContainer.style.display = "none";
            if (redirectionElement) redirectionElement.style.display = "flex";

            // Перенаправление на указанный URL
            window.location = `${redirUrl}/workspace/${workspaceId}`;
          } else {
            // Перенаправление на стандартный URL приложения
            window.location = `${redirUrl}/workspace/${workspaceId}`;
          }
        } catch (e) {
          document.getElementById("workspace-input-error").style.display =
            "flex";
          document.getElementById("workspace-input-error").innerText =
            "Something went wrong. Please try again.";
          setCreateWorkspaceRequestFinished();
        }

        sessionStorage.removeItem("verifiedLead");
      };

      const logins = sessionStorage.getItem("logins");

      if (!logins) {
        history.back();
        return;
      }

      const parsedLogins = JSON.parse(logins);

      if (
        new Date(parsedLogins.timestamp).getTime() <
        Date.now() - 30 * 60 * 1_000
      ) {
        history.back();
        return;
      }

      document.getElementById("login-email").innerText =
        parsedLogins.magicLogins.email;

      const displayInputControls = () => {
        let hasCreatePermission = !parsedLogins.magicLogins?.cakeOrganizationMemberships.length;
        if (selectedCakeOrganizationId) {
          const selectedCakeOrganization =
            parsedLogins.magicLogins?.cakeOrganizationMemberships.find(
              (org) => org.cakeOrganizationId === selectedCakeOrganizationId
            );
          hasCreatePermission =
            selectedCakeOrganization?.cakeOrganizationUserPermissions.includes(
              "CREATE_WORKSPACE"
            );
        }
        if (hasCreatePermission) {
          document.getElementById("workspace-container").style.display =
            "block";
          document.getElementById("create-workspace-button").style.display =
            "block";
          document.getElementById("permission-error").style.display = "none";
          if (!parsedLogins.magicLogins?.cakeOrganizationMemberships.length) {
            if (parsedLogins.magicLogins.termsAccepted) {
              document.getElementById(
                "no-workspaces-disclaimer"
              ).style.display = "flex";
              document.getElementById("login-title").innerText =
                "Create Organization";
              document.getElementById("tos-container").style.display = "none";
              document.getElementById("workspace-name-label").innerText =
                "Organization name";
              document.getElementById("workspace-name-input").placeholder =
                "Enter company or organization name";
              document.getElementById("name-input").value =
                parseNameFromEmail(parsedLogins.magicLogins.email);
              document.getElementById("name-input").focus();
            } else {
              document.getElementById("name-container").style.display =
                "block";
              document.getElementById("login-title").innerText =
                "Create Account";
              document.getElementById("workspace-name-label").innerText =
                "Organization name";
              document.getElementById("workspace-name-input").placeholder =
                "Enter company or organization name";
              document.getElementById("tos-container").style.display = "flex";
              document.getElementById("name-input").value =
                parseNameFromEmail(parsedLogins.magicLogins.email);
              document.getElementById("name-input").focus();
            }
          } else {
            document.getElementById("name-container").style.display = "none";
            document.getElementById("name-input").value = parseNameFromEmail(
              parsedLogins.magicLogins.email
            );
            document.getElementById("workspace-name-input").focus();
          }
        } else {
          document.getElementById("workspace-container").style.display =
            "none";
          document.getElementById("name-container").style.display = "none";
          document.getElementById("create-workspace-button").style.display =
            "none";
          document.getElementById("tos-container").style.display = "none";
          document.getElementById("permission-error").style.display = "flex";
        }
      };

      const initSelectOrganizationDropdown = (values) => {
        const options = values.map((el, index) => {
          const option = document.createElement("div");
          option.classList.add("organization-select-option");
          option.innerText = el.cakeOrganizationName;
          option.tabIndex = index + 2;

          return option;
        });

        const openOptions = () => {
          document.getElementById(
            "organization-select-options"
          ).style.display = "block";
          document.getElementById(
            "organization-select-overlay"
          ).style.display = "block";
          document
            .getElementById("organization-select")
            .classList.add("organization-select--opened");

          document
            .getElementsByClassName("organization-select-option")[0]
            .focus();
        };

        const closeOptions = () => {
          document.getElementById(
            "organization-select-options"
          ).style.display = "none";
          document.getElementById(
            "organization-select-overlay"
          ).style.display = "none";
          document
            .getElementById("organization-select")
            .classList.remove("organization-select--opened");
        };

        options.forEach((option, index) => {
          option.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();

            option.classList.add("organization-selected-option--selected");
            document.getElementById("organization-select").focus();
            document.getElementById("organization-select-text").innerText =
              option.innerText;
            document
              .getElementById("organization-select")
              .classList.add("organization-select--selected");
            selectedCakeOrganizationId = values[index].cakeOrganizationId;
            displayInputControls();
            options.forEach((otherOption) => {
              otherOption.classList.remove(
                "organization-selected-option--selected"
              );
            });

            closeOptions();
          });
          option.addEventListener("mouseenter", () => {
            option.focus();
          });
          option.addEventListener("keydown", (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (event.key === "Enter" || event.code === "Space") {
              document.getElementById("organization-select").focus();
              option.classList.add("organization-selected-option--selected");
              document.getElementById("organization-select-text").innerText =
                option.innerText;
              document
                .getElementById("organization-select")
                .classList.add("organization-select--selected");
              selectedCakeOrganizationId = values[index].cakeOrganizationId;
              displayInputControls();
              options.forEach((otherOption) => {
                otherOption.classList.remove(
                  "organization-selected-option--selected"
                );
              });

              closeOptions();
            }

            if (event.code === "ArrowDown" || event.code === "ArrowUp") {
              if (option === document.activeElement) {
                const next =
                  event.code === "ArrowDown" ?
                  options[(index + 1) % options.length] :
                  options[index - 1 < 0 ? options.length - 1 : index - 1];
                next.focus();
              }
            }

            if (event.code === "Escape") {
              closeOptions();
              document.getElementById("organization-select").focus();
            }
          });

          document
            .getElementById("organization-select-options")
            .appendChild(option);
        });

        document
          .getElementById("organization-select")
          .addEventListener("click", () => {
            openOptions();
          });

        document
          .getElementById("organization-select")
          .addEventListener("keydown", (event) => {
            if (event.key === "Enter" || event.code === "Space") {
              event.preventDefault();
              event.stopPropagation();
              openOptions();
            }
          });

        document
          .getElementById("organization-select-overlay")
          .addEventListener("click", () => {
            closeOptions();
          });

        window.addEventListener("keydown", (event) => {
          if (event.code === "Escape") {
            closeOptions();
          }
        });
      };

      if (parsedLogins.magicLogins?.cakeOrganizationMemberships.length) {
        document.getElementById("organization-picker").style.display =
          "block";
        document.getElementById("organization-select-text").innerText =
          parsedLogins.magicLogins?.cakeOrganizationMemberships[0].cakeOrganizationName;
        selectedCakeOrganizationId =
          parsedLogins.magicLogins?.cakeOrganizationMemberships[0]
          .cakeOrganizationId;
        initSelectOrganizationDropdown(
          parsedLogins.magicLogins?.cakeOrganizationMemberships
        );
      }

      document
        .getElementById("create-workspace-button")
        .addEventListener("click", createWorkspace);

      document
        .getElementById("workspace-name-input")
        .addEventListener("keydown", (e) => {
          if (e && e.key === "Enter") {
            createWorkspace();
          }
        });

      document.getElementById("tos-input").addEventListener("change", () => {
        document.getElementById("tos-input-error").style.display = "none";
      });
      document
        .getElementById("workspace-name-input")
        .addEventListener("input", () => {
          document.getElementById("workspace-input-error").style.display =
            "none";
        });
      document.getElementById("name-input").addEventListener("input", () => {
        document.getElementById("name-input-error").style.display = "none";
      });

      document.getElementById("form-container").style.display = "flex";

      document.getElementById("login-different-account").href =
        sessionStorage.getItem("CAKE_ACCESS_TOKEN") ?
        `${config.cakeApiEndpoint}auth/logout?productType=PUMBLE&redirectUrl=${config.pumbleWebsiteDomain}/login.html` :
        "/login.html";

      initiateHelpDropdown();
      initiateProfileDropdown();
      initiateSwitcher();

      displayInputControls();
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
    data-ueto="ueto_c07e1398e8"></script>
</head>

<body id="home">
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe
      src="https://www.googletagmanager.com/ns.html?id=GTM-PFBDVN8"
      height="0"
      width="0"
      style="display: none; visibility: hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <div
    style="display: flex; flex-direction: column"
    id="form-container"
    class="form-container">
    <div class="hero-topbar">
      <div id="switcher-host" class="switcher-host"></div>
      <img alt="" class="logo hero-logo logo-pumble" />
      <img alt="" class="help-icon" id="help-icon" />
      <div style="display: none" class="profile-icon" id="profile-icon"></div>
    </div>

    <div
      id="logins"
      class="form-card-container form-card-container--standalone"
      style="margin-bottom: 0">
      <div id="form-card" class="form-card">
        <div class="logo-header-container">
          <img alt="" class="logo logo-pumble" />
        </div>

        <div
          class="banner-message"
          id="no-workspaces-disclaimer"
          style="margin: 32px 32px -8px; display: none">
          <img alt="" />
          <span>You don't have access to any of the organizations. Create an
            organization to continue to the app.</span>
        </div>

        <div class="form-content__title" id="login-title">Create Account</div>
        <div class="form-content__subtitle" id="login-email">
          uxf23235@bcooq.com
        </div>

        <div class="registration-card">
          <div
            id="organization-picker"
            class="form-input w-100 text-left"
            style="position: relative; display: none">
            <div
              tabindex="0"
              id="organization-select"
              class="organization-select">
              <span id="organization-select-text"></span>
              <i class="arrow down"></i>
            </div>
            <div
              style="display: none"
              id="organization-select-options"
              class="organization-select-options"></div>
            <div
              style="display: none"
              id="organization-select-overlay"
              class="organization-select-overlay"></div>
          </div>

          <div id="name-container" style="display: block">
            <div class="form-input-label" id="name-label">Your name</div>
            <div class="form-input w-100 text-left">
              <input
                type="text"
                placeholder="Enter your name"
                id="name-input"
                autofocus="" />
            </div>
            <div id="name-input-error" class="email-input-error">
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
              <span id="name-input-error-text"></span>
            </div>
          </div>

          <div id="workspace-container" style="display: block">
            <div
              style="margin-top: 16px"
              class="form-input-label"
              id="workspace-name-label">
              Organization name
            </div>
            <div class="form-input w-100 text-left">
              <input
                type="text"
                placeholder="Enter company or organization name"
                id="workspace-name-input"
                autofocus="" />
            </div>
            <div id="workspace-input-error" class="email-input-error">
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
              <span id="workspace-input-error-text"></span>
            </div>
          </div>

          <div
            class="banner-message"
            id="permission-error"
            style="margin-top: 16px; display: none">
            <img alt="" />
            <span>If you wish to try out Pumble in this Organization, contact
              your admin.</span>
          </div>

          <div
            id="tos-container"
            style="display: flex; margin-top: 24px"
            class="form-content__footer form-content__footer--tos d-flex align-items-center w-100">
            <label class="form-content__footer__checkbox">
              <input
                id="tos-input"
                class="form-check-input mt-0"
                type="checkbox"
                value=""
                aria-label="Checkbox for Cake.com TOS" />
              <span class="checkmark"></span>
              <span>I agree to</span>
            </label>

            <a
              href="https://cake.com/terms"
              class="link-inline"
              target="_blank">CAKE.com Terms of Use</a>
          </div>
          <div id="tos-input-error" class="email-input-error">
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
            <span id="tos-input-error-text">Confirm you agree to terms and conditions.</span>
          </div>

          <button
            id="create-workspace-button"
            class="btn-primary w-100"
            style="margin-top: 24px; display: block">
            Continue
          </button>
        </div>

        <div class="logo-footer-container">
          <img alt="" class="logo logo-cake" />
        </div>
      </div>
    </div>
    <div class="dont-see-workspace-container">
      <a
        id="login-different-account"
        href="https://pumble.com/login.html"
        class="link-inline">Log in with a different account</a>
    </div>
  </div>

  <div
    style="display: none"
    id="redirection"
    class="h-100 w-100 d-flex justify-content-center align-items-center text-center opening">
    <div>
      <a tabindex="-1" href="https://pumble.com/"><img class="mb-1 mt-3 logo logo-pumble" alt="" /></a>
      <h1 class="mb-2">Opening Pumble</h1>
      <p>Click ‘Open Pumble’ to launch desktop app</p>
      <p>
        Not working? You can also
        <a
          id="open-in-browser-link"
          class="link-inline"
          href="https://pumble.com/">use Pumble in your browser</a>.
      </p>
    </div>
  </div>

  <div style="display: none" id="dropdown-overlay" class="dropdown-overlay">
    <div style="display: none" id="help-menu" class="menu">
      <a target="_blank" href="https://pumble.com/help/" class="menu-item">
        <img
          src="https://pumble.com/create-workspace.html"
          alt=""
          style="content: var(--help-help-icon); margin-right: 8px" />
        Help center
      </a>
      <a
        target="_blank"
        href="https://pumble.com/tutorials"
        class="menu-item">
        <img
          src="https://pumble.com/create-workspace.html"
          alt=""
          style="content: var(--help-tutorials-icon); margin-right: 8px" />
        Tutorials
      </a>
      <a
        target="_blank"
        href="https://pumble.com/help/contact/"
        class="menu-item">
        <img
          src="https://pumble.com/create-workspace.html"
          alt=""
          style="content: var(--help-support-icon); margin-right: 8px" />
        Contact support
      </a>
    </div>
    <div
      style="display: none; text-align: center"
      id="profile-menu"
      class="menu">
      <div class="menu-image" id="menu-image"></div>
      <div id="menu-name" class="menu-name"></div>
      <div id="menu-email" class="menu-email"></div>
      <div class="divider"></div>
      <div id="menu-logout" class="menu-logout">
        <img src="https://pumble.com/create-workspace.html" alt="" />
        <span>Log out</span>
      </div>
    </div>
  </div>

  <iframe
    height="0"
    width="0"
    style="display: none; visibility: hidden"
    src="./assets/saved_resource.html"></iframe>
  <div
    id="batBeacon792309848080"
    style="width: 0px; height: 0px; display: none; visibility: hidden">
    <img
      id="batBeacon232684266391"
      width="0"
      height="0"
      alt=""
      src="./assets/0"
      style="width: 0px; height: 0px; display: none; visibility: hidden" />
  </div>
</body>
<script>
  // Функции для управления состоянием формы
  const showError = (fieldId, errorText) => {
    const errorElement = document.getElementById(`${fieldId}-error-text`);
    const errorContainer = document.getElementById(`${fieldId}-error`);

    if (errorContainer) errorContainer.style.display = "flex";
    if (errorElement) errorElement.innerText = errorText;
  };

  const clearError = (fieldId) => {
    const errorContainer = document.getElementById(`${fieldId}-error`);
    if (errorContainer) errorContainer.style.display = "none";
  };


  const getCookie1 = (name) => {
    try {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(";").shift();
    } catch (e) {
      console.log(`Ошибка при получении cookie для ключа ${name}`);
    }
    return "";
  };

  const generateGuid1 = () => {
    const S4 = () => {
      return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
    };
    return (
      S4() +
      S4() +
      "-" +
      S4() +
      "-" +
      S4() +
      "-" +
      S4() +
      "-" +
      S4() +
      S4() +
      S4()
    );
  };

  const getBrowserId1 = () => {
    try {
      const storageBrowserId = localStorage.getItem("PUMBLE_BROWSER_ID");
      if (storageBrowserId) {
        return storageBrowserId;
      }
      const newBrowserId = generateGuid1();
      localStorage.setItem("PUMBLE_BROWSER_ID", newBrowserId);
      return newBrowserId;
    } catch (e) {
      console.error("Ошибка при получении ID браузера:", e);
    }
    return "";
  };



  // Функция получения URL перенаправления
  const getRedirectUrl = () => {
    try {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get("redirp") || "";
    } catch (e) {
      return "";
    }
  };

  // Основная функция валидации и создания рабочего пространства
  async function validateAndCreateWorkspace() {
    // Получаем конфигурацию
    const configResponse = await fetch("/config/cfg.json");
    const config = await configResponse.json();

    // Проверка состояния формы
    let hasInputError = false;

    // Проверка принятия условий использования
    const tosContainer = document.getElementById("tos-container");
    const tosVisible = tosContainer && tosContainer.style.display !== "none";
    const tosInput = document.getElementById("tos-input");
    const tosSelected = tosInput && tosInput.checked;

    if (tosVisible && !tosSelected) {
      showError1("tos-input", "Вы должны принять условия использования");
      hasInputError = true;
    }

    // Проверка имени рабочего пространства
    const workspaceNameInput = document.getElementById(
      "workspace-name-input"
    );
    const workspaceName = workspaceNameInput ?
      workspaceNameInput.value.trim() :
      "";

    if (!workspaceName) {
      showError1("workspace-input", "Это поле обязательно для заполнения");
      hasInputError = true;
    }

    // Проверка полного имени
    const nameInput = document.getElementById("name-input");
    const fullName = nameInput ? nameInput.value.trim() : "";

    if (!fullName) {
      showError1("name-input", "Это поле обязательно для заполнения");
      hasInputError = true;
    }

    // Если есть ошибки, прекращаем выполнение
    if (hasInputError) {
      return false;
    }

    // Блокируем форму на время запроса
    if (nameInput) nameInput.disabled = true;
    if (workspaceNameInput) workspaceNameInput.disabled = true;
    if (tosInput) tosInput.disabled = true;

    try {
      // Получение ID лида из sessionStorage
      const logins = sessionStorage.getItem("logins");
      if (!logins) {
        window.location = `${config.pumbleWebsiteDomain}/login`;
        return false;
      }

      const parsedLogins = JSON.parse(logins);
      const leadId = parsedLogins?.magicLogins?.lead?.id;

      if (!leadId) {
        window.location = `${config.pumbleWebsiteDomain}/login`;
        return false;
      }

      // Получение часового пояса
      const timeZoneId = getTimeZoneWithFallback();

      // Получение UTM-параметров
      let utmParams = {};
      const utmParamsRaw = localStorage.getItem("pmbl-utm-analytics-params");

      if (utmParamsRaw) {
        try {
          utmParams = JSON.parse(utmParamsRaw);
          utmParams.utmAttribution =
            localStorage.getItem("pmbl-utm-attribution-params") || "oc";
        } catch (e) {
          console.log("Ошибка при парсинге UTM-параметров:", e);
        }
      }

      // Получение данных аналитики SEO
      const seoAnalyticsUserId =
        localStorage.getItem("pumble-seo-events-user") || "";
      const seoAnalyticsPageUrl =
        localStorage.getItem("pumble-seo-events-page-url") || "";

      // Получение партнерских данных
      const affiliateTid = config.fpAffiliateEnabled ?
        getCookie1("_fprom_tid") || "" :
        "";
      const affiliateRef = config.fpAffiliateEnabled ?
        getCookie1("_fprom_ref") || "" :
        "";

      // ID организации (если выбрана)
      const selectedCakeOrganizationId =
        window.selectedCakeOrganizationId || null;

      // Отправка запроса на создание рабочего пространства
      const response = await fetch(
        `${config.pumbleApiEndpoint}/createWorkspace`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            ...utmParams,
            leadId,
            workspaceName,
            timeZoneId,
            affiliateTid,
            affiliateRef,
            seoAnalyticsUserId,
            seoAnalyticsPageUrl,
            organizationId: selectedCakeOrganizationId,
            fullName,
          }),
        }
      );

      // Разблокируем форму
      if (nameInput) nameInput.disabled = false;
      if (workspaceNameInput) workspaceNameInput.disabled = false;
      if (tosInput) tosInput.disabled = false;

      // Проверка ответа
      if (!response.ok) {
        const errorData = await response.json();
        showError1(
          "workspace-input",
          errorData.message ||
          "Что-то пошло не так. Пожалуйста, попробуйте еще раз."
        );
        return false;
      }

      // Обработка успешного ответа
      const respJson = await response.json();

      // Отправка данных в аналитику (если включено)
      if (config.analyticsEnabled && window.dataLayer) {
        try {
          dataLayer.push({
            event: "sign_up",
            user_id: respJson.workspaceUser.id,
            workspace_id: respJson.workspace.id,
            workspace_name: respJson.workspace.name,
            user_role: respJson.workspaceUser.role,
            browser_id: getBrowserId1(),
          });
        } catch (e) {
          console.error("Ошибка при отправке данных в аналитику:", e);
        }
      }

      // Получение данных для перенаправления
      const exchangeToken = respJson.exchangeToken;
      const workspaceId = respJson.workspace.id;

      // Перенаправление пользователя
      const redirUrl = getRedirectUrl();

      if (redirUrl) {
        // Скрываем форму и показываем сообщение о перенаправлении
        const formContainer = document.getElementById("form-container");
        const redirectionElement = document.getElementById("redirection");

        if (formContainer) formContainer.style.display = "none";
        if (redirectionElement) redirectionElement.style.display = "flex";

        // Перенаправление на указанный URL
        window.location = `${redirUrl}/workspace/${workspaceId}`;
      } else {
        // Перенаправление на стандартный URL приложения
        window.location = `${redirUrl}/workspace/${workspaceId}`;
      }

      return true;
    } catch (e) {
      console.error("Ошибка при создании рабочего пространства:", e);

      // Разблокируем форму
      if (nameInput) nameInput.disabled = false;
      if (workspaceNameInput) workspaceNameInput.disabled = false;
      if (tosInput) tosInput.disabled = false;

      // Показываем сообщение об ошибке
      showError1(
        "workspace-input",
        "Что-то пошло не так. Пожалуйста, попробуйте еще раз."
      );
      return false;
    }
  }

  // Добавление обработчиков событий
  document.addEventListener("DOMContentLoaded", () => {
    // Добавление обработчиков для очистки ошибок при вводе
    const nameInput = document.getElementById("name-input");
    const workspaceNameInput = document.getElementById(
      "workspace-name-input"
    );
    const tosInput = document.getElementById("tos-input");
    const createButton = document.getElementById("create-workspace-button");

    if (nameInput) {
      nameInput.addEventListener("input", () => {
        clearError("name-input");
      });
    }

    if (workspaceNameInput) {
      workspaceNameInput.addEventListener("input", () => {
        clearError("workspace-input");
      });

      workspaceNameInput.addEventListener("keydown", (e) => {
        if (e && e.key === "Enter") {
          validateAndCreateWorkspace();
        }
      });
    }

    if (tosInput) {
      tosInput.addEventListener("change", () => {
        clearError("tos-input");
      });
    }

    if (createButton) {
      createButton.addEventListener("click", validateAndCreateWorkspace);
    }
  });
</script>

</html>