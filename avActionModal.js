document.addEventListener("DOMContentLoaded", async function () {
    const avOpenBtn = document.getElementById("openModal");
    const avStates = {
        "Arizona": { "billLink": "https://www.defendonlineprivacy.com/az/action.php" },
        "Hawaii": { "billLink": "https://www.defendonlineprivacy.com/hi/action.php" },
        "Illinois": { "billLink": "https://www.defendonlineprivacy.com/il/action.php" },
        "Iowa": { "billLink": "https://www.defendonlineprivacy.com/ia/action.php" },
        "Maryland": { "billLink": "https://www.defendonlineprivacy.com/md/action.php" },
        "Michigan": { "billLink": "https://www.defendonlineprivacy.com/mi/action.php" },
        "Minnesota": { "billLink": "https://www.defendonlineprivacy.com/mn/action.php" },
        "Missouri": { "billLink": "https://www.defendonlineprivacy.com/mo/action.php" },
        "New York": { "billLink": "https://www.defendonlineprivacy.com/ny/action.php" },
        "Ohio": { "billLink": "https://www.defendonlineprivacy.com/oh/action.php" },
        "Oregon": { "billLink": "https://www.defendonlineprivacy.com/or/action.php" },
        "Wisconsin": { "billLink": "https://www.defendonlineprivacy.com/wi/action.php" }
    };


    // Function to get a cookie value
    function getAVCookie(name) {
        const cookies = document.cookie.split("; ");
        for (let cookie of cookies) {
            let [key, value] = cookie.split("=");
            if (key === name) return value;
        }
        return null;
    }

    // Function to set a cookie
    function setAVCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        // "Lax" prevents embedded content (iframes) from setting the cookie.
        document.cookie = `${name}=${value}; expires=${expires.toUTCString()}; path=/; Secure; SameSite=None`;
    }

    // Function to fetch user's location
    async function getAVModalLocation() {
        const requestOptions = {
            method: "GET",
            redirect: "follow"
        };
        try {
            const response = await fetch("https://api.freespeechcoalition.com/ip_geo_proxy.php", requestOptions);
            const result = await response.json();
            if (result.state_prov && avStates[result.state_prov]) {
                const avUserLocation = {
                    stateName:  result.state_prov,
                    billLink: avStates[result.state_prov]["billLink"] + "?utm_medium=modal&utm_source=" + location.host
                };
                return avUserLocation;
            } else {
                return false;
            }
        } catch (e) {
            return false;
        }
    }

    // Build modal
    function buildAVModal(avLocation) {
        if (!avLocation) { return; }
        const avModal = document.createElement("div");
        avModal.id = 'AVmodal';
        avModal.style.cssText = "display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); font-family: Arial, sans-serif; font-size: 22px; line-height: 24px;";
        const avStyles = {
            background: "#10202b",
            text: "#fff",
            linkColor: "#fff",
            buttonColor: "#ff3131",
            maxWidth: "350px",
            padding: "26px",
            closeTop: "10px",
            closeFontSize: "20px",
            headlineFontSize: "22px",
            headlineLineHeight: "24px",
            displayText: "block",
            medFont: "22px",
            smallFont: "18px"
        };
        const mediaQuery = window.matchMedia('(max-width: 768px)');
        if (mediaQuery.matches) {
            avStyles.maxWidth = "80%";
            avStyles.padding = "54px 16px 26px 16px";
            avStyles.closeTop = "30px";
            avStyles.closeFontSize = "80px";
            avStyles.headlineFontSize = "32px";
            avStyles.headlineLineHeight = "34px";
            avStyles.displayText = "none";
            avStyles.medFont = "20px";
            avStyles.smallFont = "16px";
        }

        const avModelContent = `
            <div style="background: ${avStyles.background}; color: ${avStyles.text}; padding: ${avStyles.padding}; max-width: ${avStyles.maxWidth}; margin: 10% auto; border-radius: 8px; position: relative; text-align: center;">
                <p style="font-weight: bold; font-family: Arial, sans-serif; font-size: ${avStyles.headlineFontSize}; line-height: ${avStyles.headlineLineHeight};">Attention ${avLocation.stateName} Residents</p>
                <p style="font-family: Arial, sans-serif; font-size: ${avStyles.medFont}; line-height: 24px;">
                    Legislators in ${avLocation.stateName} are about to pass a law that could force you to upload government ID and scan your face every time you access adult content.
                </p>
                <p style="font-family: Arial, sans-serif; font-size: 22px; line-height: 24px; display: ${avStyles.displayText}">
                    You have a right to privacy online!<br /> And <u>you</u> can help stop this bill if you act now.
                </p>
                <a href="${avLocation.billLink}" target="_blank" style="display: inline-block; text-decoration: none; padding: 20px; border-radius: 16px; background: ${avStyles.buttonColor}; color: ${avStyles.linkColor}; font-weight: bold; font-family: Arial, sans-serif; font-size: 22px; line-height: 24px;" id="avTakeAction">Tell your representatives to oppose this bill NOW!</a>
                <p style="font-size: ${avStyles.smallFont}; font-family: Arial, sans-serif; line-height: 20px;">
                    Not in ${avLocation.stateName}? <a href="https://defendonlineprivacy.com/geolocation.php" style="color: ${avStyles.buttonColor};" target="_blank" id="avGeolocationError">Learn more</a> about why you could be affected by this law.
                </p>
                <span id="avClose" style="position: absolute; top: ${avStyles.closeTop}; right: 15px; font-size: ${avStyles.closeFontSize}; cursor: pointer;">&times;</span>
            </div>`;

        // build it
        avModal.innerHTML = avModelContent;
        document.body.append(avModal);
        avModal.style.display = "block";

        // Close modal when clicking "X"
        const avCloseBtn = document.getElementById("avClose");
        avCloseBtn.addEventListener("click", () => {
            const avModal = document.getElementById('AVmodal');
            avModal.style.display = "none";
        });

        // Close modal when clicking on our outside the modal content
        const avTakeActionBtn = document.getElementById("avTakeAction");
        const avGeolocationErrorLnk = document.getElementById("avGeolocationError");
        window.addEventListener("click", (event) => {
            const avModal = document.getElementById('AVmodal');
            if (event.target === avModal || event.target === avTakeActionBtn || event.target === avGeolocationErrorLnk) {
                avModal.style.display = "none";
            }
        });
    }

    // Pop modal (or not)
    if (getAVCookie("av-modal") !== "true") {
        const avModalLocation = await getAVModalLocation();
        buildAVModal(avModalLocation);
        setAVCookie("av-modal", "true", 365); // Cookie expires in 1 year
    }

});
