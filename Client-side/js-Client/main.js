function showSignup() {
    document.getElementById('login-container').classList.add('hidden');
    document.getElementById('signup-container').classList.remove('hidden');
}

function showLogin() {
    document.getElementById('signup-container').classList.add('hidden');
    document.getElementById('login-container').classList.remove('hidden');
}

function toggleMenu() {
    const menu = document.getElementById('menuList');
    const isExpanded = menu.style.display === 'block';
    menu.style.display = isExpanded ? 'none' : 'block';
}
