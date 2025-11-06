const eye = document.getElementById('icon-eye');
if (eye) {
    const pswd = document.getElementById('password');

    eye.addEventListener('click', function () {
        pswd.type = pswd.type === 'password' ? 'text' : 'password';
        // eye.className = 'fa-solid fa-cache';
        eye.classList.toggle('fa-eye');
        eye.classList.toggle('fa-eye-slash');
    });
}