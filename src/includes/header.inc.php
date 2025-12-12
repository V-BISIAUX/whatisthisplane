<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="keywords" content="WhatIsThisPlane,What Is This Plane,rechercher les avions au-dessus de nous,Quel avion au-dessus de moi,trouver avion au-dessus de moi"/>
    <meta name="location" content="Université CY / projet UE en Développement Web, France"/>
    <meta name="description" content="<?=$description?>"/>
    <meta name="application-name" content="Whatisthisplane"/>
    <meta name="apple-mobile-web-app-title" content="Whatisthisplane"/>
    <meta name="og:site_name" content="Whatisthisplane"/>
    <meta name="robots" content="index, follow"/>
    <meta name="author" content="Thierno Abasse DIALLO,Costa Mathéo,Bisiaux Valentin"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="styles/style.css"/>
    <?php
        date_default_timezone_set('Europe/Paris');

        require_once __DIR__ . "/../config/config.php";
        $youtube = "images/youtube_sombre.png";
        $instagram = "images/instagram_sombre.png";
        $twitter = "images/twitter_sombre.png";
        $snapchat = "images/logo-snapchat-noir.png";
        $style = "styles/style.css";
        $url = "?theme=noir";
        $img = "images/sombre.png";
        $logo = "images/logo.png";
        if (isset($_GET['theme']) && isset($_COOKIE["cookieConsent"])) {
            if ($_GET['theme'] == "noir") {
                $url = "?theme=clair";
                $style = "styles/style.sombre.css";
                $img = "images/claire.png";
                setcookie("theme", "sombre", time() + 86400, "/");
            }else if ($_GET['theme'] == "claire") {
                setcookie("theme", "claire", time() + 86400, "/");
            }else {
                setcookie("theme", "", time() - 86400, "/");
            }
        } else if (isset($_COOKIE["theme"]) && $_COOKIE["theme"] == "sombre") {
            $style = "styles/style.sombre.css";
            $url = "?theme=clair";
            $img = "images/claire.png";
        }

        if (!isset($_COOKIE["visite"])) {
            $msg = "Bienvenue pour la premiere fois sur le site!";
            $date = date("d/m/Y H:i:s");
            setcookie("visite", $date, time() + 365*86400, "/");
        } else {
            $date = $_COOKIE["visite"];
            $msg = "Content de vous revoir, votre derniere visite sur le site est le : " . $date;
            $new_date = date("d/m/Y H:i:s");
            setcookie("visite", $new_date, time() + 365*86400, "/");
        }
    ?>
    <title><?=$title?></title>
    <link rel="stylesheet" href="<?= $style; ?>" />
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg" />
    <link rel="icon" type="image/png" href="images/favicon-96x96.png" sizes="96x96" />
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png" />
    <link rel="manifest" href="images/site.webmanifest" />
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
</head>
<body>
    <header class="navbar custom-header navbar-expand-lg">
        <figure class="mb-0 ml-2 d-flex align-items-center">
            <a class="navbar-brand" href="index.php">
                <img src="<?= $logo; ?>" alt="Logo du site"/>
                <span class="ms-2"><?=SITE_NAME?></span>
            </a>
        </figure>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="nav-mobile collapse navbar-collapse justify-content-between align-items-center" id="navbarResponsive">
            <nav class="my-3 my-lg-0">
                <ul class="navbar-nav flex-row flex-lg-row gap-3">
                    <li>
                        <a class="nav-link" href="search.php">Trouver</a>
                    </li>
                    <li>
                        <a class="nav-link" href="carte.php">Maps</a>
                    </li>
                    <li>
                        <a class="nav-link" href="stats.php">Statistiques</a>
                    </li>
                    <li>
                        <a class="nav-link" href="about.php">A propos</a>
                    </li>
                </ul>
            </nav>

            <div class="right my-3 my-lg-0">
                <?php if (isset($_SESSION['login'])): ?>
                    <ul class="navbar-nav flex-row flex-lg-row gap-3">
                        <li><a class="nav-link" href="favorites.php">Mes favoris</a></li>
                        <li><a class="nav-link" href="mon-profile.php">Mon Profil</a></li>
                    </ul>
                <?php endif; ?>
                <a href="cnx.php" class="btn1">Se connecter</a>
                <a href="<?= $url ?>">
                    <img src="<?= $img; ?>" alt="icone pour le changement de theme" />
                </a>
            </div>
        </div>
        <?php if (isset($_SESSION['login'])): ?>
            <script>
                const btn1 = document.querySelector('.btn1');
                if (btn1) {
                    btn1.innerText = "Déconnexion";
                    btn1.setAttribute("href", "logout.php");
                    btn1.addEventListener("click", function (e) {
                        if (!confirm("Voulez-vous vous déconnecter ?")) {
                            e.preventDefault();
                        }
                    });
                }
            </script>
        <?php endif; ?>
    </header>
    <div id="cookie-modal" class="cookie-modal">
        <div class="cookie-content">
            <p>Ce site utilise des cookies pour améliorer votre expérience. Acceptez-vous ?</p>
            <div class="cookie-buttons">
                <button onclick="acceptCookies()">Accepter</button>
                <button onclick="refuseCookies()">Refuser</button>
            </div>
        </div>
    </div>