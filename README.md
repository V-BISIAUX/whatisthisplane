# Whatisthisplane

## Noms et contacts des auteurs
- Mathéo COSTA - matheo.costa4@etu.u-cergy.fr
- Valentin BISIAUX - valentin.bisiaux@etu.cyu.fr
- Thierno Abasse DIALLO - thierno-abasse.diallo1@etu.cyu.fr

## Liens
- [Cliquez sur ce lien pour visiter la plateforme web](https://whatisthisplane.alwaysdata.net/)
- [Visitez notre site vitrine wordpress](https://t-a-diallo.alwaysdata.net/wordpress/)
- [Trello](https://trello.com/b/GrUOHvN6)
- [Figma](https://www.figma.com/design/NGUg34QHkHr8I2hKMcmCgU/Untitled?node-id=11-463&p=f&t=YfKJ7Rrl1PwnkMcV-0)
- [Dépot GitHub](https://github.com/V-BISIAUX/whatisthisplane/)

## Description de la plateforme
**WhatIsThisPlane** est une application web pour trouver des informations sur les avions.
Elle permet aussi de voir les avions les plus recherchés sur la plateforme, elle offre la possibilité aux usagers de faire des recherches par nom d'avions, de code d'aéroport, de compagnie et meme par code ICAO (Le code ICAO est l'identifiant unique attribué à chaque aéroport ou compagnie par l'Organisation de l'aviation civile internationale).
Le site permet aussi aux utilisateurs connectés de gérer leur profil et de conserver une liste d'avions favoris.

### Objectifs du projet
- Mettre en pratique le développement web dynamique (HTML, CSS, JS et PHP).
- Apprendre à gérer une base de données SQL et à faire le lien avec le site.
- Utiliser **AJAX** pour récupérer et afficher les données sans recharger la page.
- Créer un système d'authentification sécurisé (inscription, connexion, sessions).

## Fonctionnalités
### Voir l'avion au-dessus de vous
Utilisation de la géolocalisation pour identifier les avions à proximité.
### Rechercher un vol
Recherche par nom, compagnie ou code ICAO
### Fiche détaillée des avions
Affichage des informations techniques de l'avion.
### Carte du trafic aérien
Visualisation des positions des avions sur une carte interactive.
### Espace compte
Inscription, connexion et gestion du profil.
### Donnée historique
Consultation des avions les plus recherchés.
### Envoi de mail
Notifications par email (ex: mail d'activation du compte lors de l'inscription).
### APIs
Ce projet interagit avec plusieurs API publiques pour récupérer des informations sur le trafic aérien et la géolocalisation.
- **Format JSON & XML** : Les données sont récupérées depuis plusieurs APIs qui renvoient des données en format JSON ou XML.
- [OpenSky Network](https://opensky-network.org/api/states/all) : Fournit des données en temps réel sur les avions, telles que la position, la vitesse et l’altitude.
- [ADSBdb API](https://api.adsbdb.com/v0/) : Permet d’obtenir des informations détaillées sur un avion à partir de son identifiant ICAO (code hexadécimal).
- [IP-API](http://ip-api.com/json/) : Fournit la géolocalisation approximative d’un utilisateur à partir de son adresse IP.

## Technologies
Ce projet repose sur un ensemble cohérent de technologies web modernes, couvrant à la fois le front-end, le back-end, la base de données, et la visualisation des données aéronautiques.
- **Front end** : HTML5, CSS3, JavaScript
  - Gère l’affichage dynamique des données reçues des APIs (vols, positions, statistiques).
  - Interface utilisateur responsive, conçue pour être légère et rapide.
  - Utilisation d’AJAX pour charger les données sans recharger la page.

- **Back end** : PHP8, AJAX
  - Sert d’intermédiaire entre le front-end et les APIs externes.
  - Effectue les requêtes vers OpenSky Network, ADSBdb et IP-API.
  - Gère la logique métier (traitement des données, filtrage, agrégation).
  - Interagit avec la base de données MySQL pour stocker, récupérer les informations historiques ainsi qu'enregistrer les utilisateurs.
- **PHP Mailer** : 
  - Permet l’envoi d’e-mails sécurisés depuis le serveur.
  - Gère l’authentification SMTP, les pièces jointes et le format HTML des messages.
- **APIs utilisées** : Open Sky Network, ADSBdb, IP-API - voir la section [API](#apis).
- **Base de donnée** : MySQL
- **Visualisation des statistiques** : **Chart JS**
- **Hébergement** : Alwaysdata.