/* ==========================================================================
   GHOSTTRACK APP.JS (Dynamic 3D Parallax, Motion & Auth Engine)
   ========================================================================== */

// 1. LENIS SMOOTH SCROLL ENGINE
const lenis = new Lenis({
    duration: 1.4,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    direction: 'vertical',
    smooth: true,
    mouseMultiplier: 1.15,
    smoothTouch: false,
    lerp: 0.1
});

function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
}
requestAnimationFrame(raf);

// 2. CANVAS 3D PARTICLES & DYNAMIC PERSPECTIVE GRID
const canvas = document.getElementById('heroCanvas');
const ctx = canvas.getContext('2d');
let width = 0, height = 0;
let particles = [];

function resizeCanvas() {
    width = canvas.width = window.innerWidth;
    height = canvas.height = window.innerHeight;
    initParticles();
}

function initParticles() {
    particles = [];
    const count = Math.floor((width * height) / 9000);
    for (let i = 0; i < count; i++) {
        particles.push({
            x: Math.random() * width,
            y: Math.random() * height,
            z: Math.random() * 2.5 + 0.5,
            size: Math.random() * 2.0 + 0.4,
            alpha: Math.random() * 0.7 + 0.2,
            speedY: (Math.random() * 0.4 + 0.15) * -1,
            speedX: (Math.random() - 0.5) * 0.35,
            pulse: Math.random() * Math.PI
        });
    }
}

let scrollProgress = 0;

function drawCanvas() {
    ctx.clearRect(0, 0, width, height);

    ctx.save();
    ctx.strokeStyle = 'rgba(56, 189, 248, 0.09)';
    ctx.lineWidth = 1;

    const gridSpacing = 60;
    const horizon = height * 0.5;

    for (let x = -width; x < width * 2; x += gridSpacing * 2) {
        ctx.beginPath();
        ctx.moveTo(x, height);
        ctx.lineTo(width / 2 + (x - width / 2) * 0.1, horizon);
        ctx.stroke();
    }

    for (let y = horizon; y < height; y += gridSpacing * (1 + (y - horizon) / 200)) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(width, y);
        ctx.stroke();
    }
    ctx.restore();

    for (let p of particles) {
        p.y += p.speedY;
        p.x += p.speedX;
        p.pulse += 0.03;

        if (p.y < 0) p.y = height;
        if (p.x < 0) p.x = width;
        if (p.x > width) p.x = 0;

        const drawY = p.y - (scrollProgress * 250 * p.z);
        const modY = ((drawY % height) + height) % height;
        const currentAlpha = p.alpha + Math.sin(p.pulse) * 0.15;

        ctx.fillStyle = `rgba(56, 189, 248, ${Math.max(0.1, currentAlpha)})`;
        ctx.beginPath();
        ctx.arc(p.x, modY, p.size * p.z, 0, Math.PI * 2);
        ctx.fill();
    }

    requestAnimationFrame(drawCanvas);
}

window.addEventListener('resize', resizeCanvas);
resizeCanvas();
drawCanvas();

// 3. DYNAMIC MOUSE INTERACTIVE 3D TILT ENGINE (Hero Bg & Login Card)
const heroBgWrapper = document.getElementById('heroBgWrapper');
const loginCardWrapper = document.querySelector('.login-card-wrapper');
let mouseX = 0, mouseY = 0;
let targetX = 0, targetY = 0;

window.addEventListener('mousemove', (e) => {
    mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
    mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
});

function animateMouseTilt() {
    targetX += (mouseX - targetX) * 0.06;
    targetY += (mouseY - targetY) * 0.06;

    // Hero Section Background 3D Parallax Tilt
    if (heroBgWrapper) {
        const rotateX = -targetY * 12;
        const rotateY = targetX * 12;
        const scale = 1.02 + scrollProgress * 0.35;
        const translateY = scrollProgress * 200;

        heroBgWrapper.style.transform = `scale(${scale}) translateY(${translateY}px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    }

    // Dynamic Login Card Subtle 3D Mouse Tilt
    if (loginCardWrapper) {
        const cardRotateX = -targetY * 5;
        const cardRotateY = targetX * 5;
        loginCardWrapper.style.transform = `perspective(1000px) rotateX(${cardRotateX}deg) rotateY(${cardRotateY}deg)`;
    }

    requestAnimationFrame(animateMouseTilt);
}
animateMouseTilt();

// 4. PARALLAX SCROLL & TELEMETRY
const heroBg = document.getElementById('heroBg');
const heroVeil = document.getElementById('heroVeil');
const heroContent = document.getElementById('heroContent');
const clockDisplay = document.getElementById('clockDisplay');

lenis.on('scroll', (e) => {
    const scrollY = e.scroll;
    const stageHeight = window.innerHeight * 2.5;
    scrollProgress = Math.min(1, Math.max(0, scrollY / stageHeight));

    if (scrollY <= stageHeight) {
        if (heroBg) {
            heroBg.style.filter = `brightness(${0.75 - scrollProgress * 0.3}) contrast(${1.2 + scrollProgress * 0.2})`;
        }
        if (heroVeil) {
            heroVeil.style.opacity = 1 - scrollProgress * 0.8;
        }
        if (heroContent) {
            const contentTranslateY = scrollY * 0.55;
            const opacity = 1 - scrollProgress * 1.8;
            const blur = scrollProgress * 12;
            heroContent.style.transform = `translate(-50%, calc(-50% - ${contentTranslateY}px)) scale(${1 - scrollProgress * 0.1})`;
            heroContent.style.opacity = Math.max(0, opacity);
            heroContent.style.filter = blur > 0.1 ? `blur(${blur}px)` : 'none';
        }
    }

    if (clockDisplay) {
        const totalSecs = Math.floor(scrollY * 0.35);
        const hrs = String(Math.floor(totalSecs / 3600)).padStart(2, '0');
        const mins = String(Math.floor((totalSecs % 3600) / 60)).padStart(2, '0');
        const secs = String(totalSecs % 60).padStart(2, '0');
        clockDisplay.textContent = `T+ ${hrs}:${mins}:${secs}`;
    }
});

// 5. ROLE SELECTION & AUTHENTICATION LOGIC
let currentRole = 'officer';

function setRole(role) {
    currentRole = role;
    const tabOfficer = document.getElementById('tabOfficer');
    const tabContractor = document.getElementById('tabContractor');
    const officerSubTier = document.getElementById('officerSubTier');
    const userIdLabel = document.getElementById('userIdLabel');
    const userIdInput = document.getElementById('userId');

    if (role === 'officer') {
        tabOfficer.classList.add('active');
        tabContractor.classList.remove('active');
        if (officerSubTier) officerSubTier.style.display = 'block';
        if (userIdLabel) userIdLabel.innerText = 'Official Government Email / Employee ID';
        if (userIdInput) userIdInput.placeholder = 'e.g. name@imed.gov.bd';
    } else {
        tabContractor.classList.add('active');
        tabOfficer.classList.remove('active');
        if (officerSubTier) officerSubTier.style.display = 'none';
        if (userIdLabel) userIdLabel.innerText = 'Registered Trade License / Vendor ID';
        if (userIdInput) userIdInput.placeholder = 'e.g. TR-BD-2026-9942';
    }
}

function togglePasswordVisibility() {
    const passInput = document.getElementById('userPass');
    if (passInput) {
        passInput.type = passInput.type === 'password' ? 'text' : 'password';
    }
}

function handleAuth(event) {
    event.preventDefault();
    let authenticatedRole = currentRole;

    if (currentRole === 'officer') {
        const officerSelect = document.getElementById('officerTypeSelect');
        if (officerSelect) {
            authenticatedRole = officerSelect.value;
        }
    }

    const userId = document.getElementById('userId').value.trim();
    if (userId === "") {
        alert("SECURITY ERROR: Please enter valid credentials.");
        return;
    }

    localStorage.setItem('ghosttrack_user_role', authenticatedRole);
    localStorage.setItem('ghosttrack_user_id', userId);
    window.location.href = 'dashboard.html';
}