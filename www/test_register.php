<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Test inscripiton AJAX</title>
<script>
async function registerUser() {
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    const response = await fetch('ajax_register.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({username, email, password})
    });

    const data = await response.json();
    const messageElem = document.getElementById('message');
    if (data.success) {
        messageElem.style.color = 'green';
        messageElem.textContent = data.message;
    } else {
        messageElem.style.color = 'red';
        messageElem.textContent = data.error || 'Erreur inconnue';
    }
}
</script>
</head>
<body>
<h1>Test inscription utilisateur</h1>
<div>
    <label>Nom d'utilisateur: <input type="text" id="username"></label><br><br>
    <label>Email: <input type="email" id="email"></label><br><br>
    <label>Mot de passe: <input type="password" id="password"></label><br><br>
    <button onclick="registerUser()">S'inscrire</button>
</div>
<p id="message"></p>
</body>
</html>
