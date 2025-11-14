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

const btn = document.getElementById("gototop");

if (btn) {
    window.onscroll = function() {
        if (document.body.scrollTop > 150 || document.documentElement.scrollTop > 150) {
            btn.style.display = "block";
        }else {
            btn.style.display = "none";
        }
    };

    btn.onclick = function () {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    };
}