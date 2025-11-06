function getCookie(name) {
    const cookies = document.cookie.split(';');
    for (let c of cookies) {
        const [key, value] = c.trim().split('=');
        if (key === name) return value;
    }
    return null;
}

function acceptCookies() {
    document.cookie = "cookieConsent=accepted; path=/; max-age=" + (86400);
    document.getElementById("cookie-modal").style.display = "none";
}

function refuseCookies() {
    document.cookie = "cookieConsent=refused; path=/; max-age=" + (3600);
    document.getElementById("cookie-modal").style.display = "none";
}

window.addEventListener("load", function () {
    const consent = getCookie("cookieConsent");
    if (!consent) {
        document.getElementById("cookie-modal").style.display = "flex";
    }
});