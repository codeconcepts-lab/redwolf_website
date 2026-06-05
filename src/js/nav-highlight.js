document.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname;

    // Clear any existing active state
    document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('nav-active'));

    if (path === '/' || path.endsWith('/index.html')) {
        document.getElementById('nav-home')?.classList.add('nav-active');
    } else if (path.includes('/about')) {
        document.getElementById('nav-about')?.classList.add('nav-active');
    } else if (path.includes('/services') || path.includes('/service-')) {
        document.getElementById('nav-services')?.classList.add('nav-active');
    } else if (path.includes('/contact')) {
        document.getElementById('nav-contact')?.classList.add('nav-active');
    }
});
