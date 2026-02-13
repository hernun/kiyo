$(function(){
    $('.navbar-nav').on({
        click: function(){
            if($('.notifications-container').filter(':visible').length) $('.notifications-container').fadeOut();
        }
    })

    $('img.nqv-modal-trigger').on({
        click:function(){
            openModal(this);
        }
    })
});

function getBgImageByElelemntId(elementId) {
    var img = document.getElementById(elementId),
    style = img.currentStyle || window.getComputedStyle(img, false);
    return style.backgroundImage.slice(4, -1).replace(/"/g, "");
}

function smoothScrollTo(targetY, duration = 1000) {
    const startY = window.scrollY;
    const distance = targetY - startY;
    const startTime = performance.now();

    function step(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const ease = easeInOutQuad(progress); // efecto de aceleración suave

        window.scrollTo(0, startY + distance * ease);

        if (progress < 1) {
            requestAnimationFrame(step);
        }
    }

    requestAnimationFrame(step);
}

// Easing suave
function easeInOutQuad(t) {
    return t < 0.5
        ? 2 * t * t
        : -1 + (4 - 2 * t) * t;
}

function rot13(str) {
    return str.replace(/[a-zA-Z]/g, function(c) {
        const start = c <= "Z" ? 65 : 97;
        return String.fromCharCode((c.charCodeAt(0) - start + 13) % 26 + start);
    });
}

var waitin = function() {
    let clickCallback = null; // ← guardamos la función pasada

    return {
        show: function(onClick) {
            clickCallback = onClick || null;

            const body = document.getElementsByTagName('body')[0];
            const w = document.createElement('div');
            w.id = 'waitin';
            w.classList.add('full');
            body.appendChild(w);

            // Listener para clic
            w.addEventListener('click', function(e) {
                if (typeof clickCallback === 'function') {
                    clickCallback(e);
                }
            });

            createWaveSpinner(w, {
                size: 320,
                rings: 3,
                duration: 2.4,
                color: "#E5CFBB",
                border: 2,
                center: true
            });
        },

        hide: function() {
            const w = document.getElementById('waitin');
            if (w) w.remove();
            clickCallback = null; // limpiamos
        }
    }
}();



/**
 * Crea un spinner de ondas concéntricas dentro de un contenedor
 * @param {HTMLElement} target - Elemento donde se insertará el spinner
 * @param {Object} opts - Opciones de configuración
 */
function createWaveSpinner(target, opts = {}) {
    const config = {
        size: opts.size || 120,         // px ancho/alto del área
        rings: opts.rings || 4,         // cantidad de anillos
        duration: opts.duration || 2.4, // seg por animación
        color: opts.color || "#2b9cff", // color de las ondas
        border: opts.border || 1,       // grosor del anillo
        center: opts.center !== false,  // mostrar punto central
    };

    // --- crear e inyectar estilos si no existen ---
    if (!document.getElementById("wave-spinner-style")) {
        const style = document.createElement("style");
        style.id = "wave-spinner-style";
        style.textContent = `
        .wave-spinner{
            position:relative;
            display:inline-grid;
            place-items:center;
            border-radius:50%;
        }
        .wave-spinner .center{
            border-radius:50%;
            background:currentColor;
            box-shadow:0 0 18px rgba(0,0,0,0.3);
            z-index:10;
        }
        .wave-spinner .ring{
            position:absolute;
            left:50%;
            top:50%;
            width:6px;
            height:6px;
            border-radius:50%;
            transform:translate(-50%,-50%) scale(0);
            border: solid currentColor;
            box-sizing:border-box;
            opacity:0.9;
            pointer-events:none;
            animation:ripple var(--duration) ease-out infinite;
            will-change:transform,opacity;
        }
        @keyframes ripple{
            0%{
            transform:translate(-50%,-50%) scale(0);
            opacity:0.95;
            }
            60%{ opacity:0.45; }
            100%{
            transform:translate(-50%,-50%) scale(12);
            opacity:0;
            border-width:0.8px;
            }
        }
        `;
        document.head.appendChild(style);
    }

    // --- crear contenedor principal ---
    const wrap = document.createElement("div");
    wrap.className = "wave-spinner";
    wrap.style.setProperty("width", config.size + "px");
    wrap.style.setProperty("height", config.size + "px");
    wrap.style.setProperty("color", config.color);
    wrap.style.setProperty("--duration", config.duration + "s");

    // --- añadir anillos dinámicamente ---
    for (let i = 0; i < config.rings; i++) {
        const ring = document.createElement("span");
        ring.className = "ring";
        ring.style.borderWidth = config.border + "px";
        ring.style.width = config.size * 0.05 + "px";
        ring.style.height = config.size * 0.05 + "px";
        ring.style.animationDelay = (i * (config.duration / config.rings)) + "s";
        wrap.appendChild(ring);
    }

    // --- añadir punto central ---
    if (config.center) {
        const dot = document.createElement("div");
        dot.className = "center";
        dot.style.width = config.size * 0.05 + "px";
        dot.style.height = config.size * 0.05 + "px";
        wrap.appendChild(dot);
    }

    target.innerHTML = ""; // limpiar target
    target.appendChild(wrap);
    return wrap;
}

function createSlug(str = '') {
    return removeAccents(str)
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function parseSlugOnForm(exclude) {
    document.addEventListener('DOMContentLoaded', () => {
        const titleInput = document.getElementById('title-input');
        const slugInput  = document.getElementById('slug-input');

        if (!titleInput || !slugInput) return;

        const removeAccents = (str = '') =>
            str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

        let timeout;

        titleInput.addEventListener('input', () => {

            const baseSlug = removeAccents(titleInput.value)
                .toLowerCase()
                .trim()
                .replace(/[\s\W-]+/g, '-')
                .replace(/^-+|-+$/g, '');

            clearTimeout(timeout);

            timeout = setTimeout(() => {

                fetch('/admin/check-slug', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        slug: baseSlug,
                        table: 'pages',
                        exclude: exclude,
                    })
                })
                .then(res => res.json())
                .then(data => {
                    slugInput.value = data.slug;
                });

            }, 300); // debounce
        });
    });
}