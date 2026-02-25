class OvoEmbedGallery {
    static get toolbox() {
        return {
            title: 'Galería de Incrustaciones',
            icon: '<svg width="18" height="18" viewBox="0 0 24 24"><path d="M10 16l-4-4 4-4v8zm4-8l4 4-4 4V8z"/></svg>'
        };
    }

    static get isReadOnlySupported() {
        return true;
    }

    constructor({ data }) {
        this.data = data || {};
        if (!Array.isArray(this.data.embeds)) this.data.embeds = [];
        this.wrapper = undefined;
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.classList.add('ovo-embed-gallery');

        // Input + botón
        const inputWrapper = document.createElement('div');
        inputWrapper.classList.add('d-flex', 'gap-2', 'my-2');

        this.urlInput = document.createElement('input');
        this.urlInput.type = 'text';
        this.urlInput.placeholder = 'Pegá la URL (YouTube, Vimeo o Spotify)';
        this.urlInput.classList.add('form-control');

        const addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.textContent = '+';
        addBtn.classList.add('btn', 'btn-outline-primary');

        inputWrapper.appendChild(this.urlInput);
        inputWrapper.appendChild(addBtn);
        this.wrapper.appendChild(inputWrapper);

        // Contenedor de la galería
        this.galleryContainer = document.createElement('div');
        this.galleryContainer.classList.add('ovo-embed-gallery-container');
        this.wrapper.appendChild(this.galleryContainer);

        // Cargar embeds existentes
        if (this.data.embeds.length) {
            this.data.embeds.forEach(e => this.addEmbedItem(e.url, e.service));
        }

        // Botón +
        addBtn.addEventListener('click', () => {
            const url = this.urlInput.value.trim();
            if (!url) return;

            const service = this.detectService(url);
            if (!service) {
                alert('Servicio no soportado');
                return;
            }

            this.addEmbedItem(url, service);
            this.urlInput.value = '';
        });

        return this.wrapper;
    }

    // -----------------------------
    // Agregar embed al contenedor
    // -----------------------------
    addEmbedItem(url, service) {
        const item = document.createElement('div');
        item.classList.add('ovo-embed-gallery-item');

        const iframeWrapper = document.createElement('div');
        iframeWrapper.classList.add('ovo-embed-iframe-wrapper');

        const iframe = document.createElement('iframe');
        iframe.setAttribute('loading', 'lazy');
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('allowfullscreen', 'true');
        iframe.width = '100%';
        iframe.height = '200';

        // Determinar src según service
        if (service === 'youtube') {
            const videoId = this.extractYouTubeId(url);
            if (!videoId) return;
            iframe.src = `https://www.youtube.com/embed/${videoId}`;
        } else if (service === 'vimeo') {
            const videoId = this.extractVimeoId(url);
            if (!videoId) return;
            iframe.src = `https://player.vimeo.com/video/${videoId}`;
        } else if (service === 'spotify') {
            const { type, id, height } = this.extractSpotifyId(url);
            if (!id) return;
            iframe.height = height;
            iframe.src = `https://open.spotify.com/embed/${type}/${id}`;
        }

        iframeWrapper.appendChild(iframe);
        item.appendChild(iframeWrapper);

        // Botón eliminar
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = 'x';
        removeBtn.classList.add('btn', 'btn-sm', 'btn-outline-danger', 'ovo-embed-remove');
        removeBtn.addEventListener('click', () => item.remove());
        item.appendChild(removeBtn);

        // Guardar metadata en el item
        item.dataset.url = url;
        item.dataset.service = service;

        this.galleryContainer.appendChild(item);
    }

    // -----------------------------
    // Detectar servicio sin regex
    // -----------------------------
    detectService(url) {
        if (!url) return null;
        if (url.includes('youtube.com') || url.includes('youtu.be')) return 'youtube';
        if (url.includes('vimeo.com')) return 'vimeo';
        if (url.includes('spotify.com')) return 'spotify';
        return null;
    }

    // -----------------------------
    // Extraer IDs usando URL
    // -----------------------------
    extractYouTubeId(url) {
        try {
            const parsed = new URL(url);
            let videoId = null;

            if (parsed.hostname.includes('youtu.be')) {
                videoId = parsed.pathname.slice(1);
            } else if (parsed.hostname.includes('youtube.com')) {
                if (parsed.pathname === '/watch') videoId = parsed.searchParams.get('v');
                if (parsed.pathname.startsWith('/shorts/')) videoId = parsed.pathname.split('/')[2];
                if (parsed.pathname.startsWith('/embed/')) videoId = parsed.pathname.split('/')[2];
            }

            return videoId;
        } catch { return null; }
    }

    extractVimeoId(url) {
        try {
            const parsed = new URL(url);
            const parts = parsed.pathname.split('/').filter(Boolean);
            return parts.reverse().find(p => /^\d+$/.test(p));
        } catch { return null; }
    }

    extractSpotifyId(url) {
        try {
            const parsed = new URL(url);
            const parts = parsed.pathname.split('/').filter(Boolean);
            let type, id;
            if (parts[0].startsWith('intl-')) { type = parts[1]; id = parts[2]; }
            else { type = parts[0]; id = parts[1]; }
            const height = (type === 'track' || type === 'episode') ? 152 : 380;
            return { type, id, height };
        } catch { return { type: null, id: null, height: 0 }; }
    }

    // -----------------------------
    // Guardar
    // -----------------------------
    save() {
        const items = this.galleryContainer.querySelectorAll('.ovo-embed-gallery-item');
        return {
            embeds: Array.from(items).map(item => ({
                url: item.dataset.url,
                service: item.dataset.service
            }))
        };
    }

    validate(savedData) {
        return savedData.embeds && savedData.embeds.length > 0;
    }
}