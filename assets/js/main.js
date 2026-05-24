const Cookie = {
    set(name, value, days = 365) {
        const d = new Date();
        d.setTime(d.getTime() + days * 86400000);
        document.cookie = `${name}=${encodeURIComponent(value)};expires=${d.toUTCString()};path=/;SameSite=Lax`;
    },
    get(name) {
        const m = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return m ? decodeURIComponent(m[2]) : null;
    }
};




document.querySelector('.nav-toggle')?.addEventListener('click', (e) => {
    const links = document.getElementById('navLinks');
    const open = links.classList.toggle('open');
    e.currentTarget.setAttribute('aria-expanded', String(open));
});




const themeBtn = document.getElementById('themeToggle');
themeBtn?.addEventListener('click', () => {
    const cur = document.documentElement.getAttribute('data-theme') || 'light';
    const next = cur === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', next);
    Cookie.set('theme', next);
});




document.getElementById('acceptCookies')?.addEventListener('click', () => {
    Cookie.set('cookie_consent', '1');
    document.getElementById('cookieBanner')?.remove();
});




// todo: testa zxcvbn nån gång, blir snyggare
function scorePassword(pw) {
    let score = 0;
    if (pw.length >= 8) score++;
    if (/[A-ZÅÄÖ]/.test(pw)) score++;
    if (/[a-zåäö]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-zÅÄÖåäö0-9]/.test(pw))score++;
    if (pw.length >= 12) score++;
    return Math.min(score, 5);
}

const pwInput = document.getElementById('password');
const pwBar = document.querySelector('.pw-meter > span');
const pwHints = document.querySelector('.pw-hints');

if (pwInput && pwBar) {
    const labels = ['Mycket svagt', 'Svagt', 'OK', 'Bra', 'Starkt', 'Mycket starkt'];
    const colors = ['#dc2626', '#ef4444', '#f59e0b', '#84cc16', '#16a34a', '#15803d'];

    pwInput.addEventListener('input', () => {
        const pw = pwInput.value;
        const score = scorePassword(pw);
        const pct = pw.length ? ((score) / 5) * 100 : 0;
        pwBar.style.width = pct + '%';
        pwBar.style.background = colors[score];

        if (pwHints) {
            const need = [];
            if (pw.length < 8)            need.push('8 tecken');
            if (!/[A-ZÅÄÖ]/.test(pw))     need.push('versal');
            if (!/[a-zåäö]/.test(pw))     need.push('gemen');
            if (!/[0-9]/.test(pw))        need.push('siffra');
            if (!/[^A-Za-zÅÄÖåäö0-9]/.test(pw)) need.push('specialtecken');
            pwHints.textContent = pw.length === 0
                ? 'Minst 8 tecken, blanda versaler, gemener, siffror och specialtecken.'
                : (need.length
                    ? 'Behöver fortfarande: ' + need.join(', ')
                    : 'Bra lösenord (' + labels[score] + ')');
        }
    });
}







document.querySelectorAll('form[data-confirm]').forEach(f => {
    f.addEventListener('submit', (e) => {
        if (!confirm(f.dataset.confirm)) e.preventDefault();
    });
});
