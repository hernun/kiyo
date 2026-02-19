class OvoEmbed {

    static get toolbox() {
        return {
            title: 'Incrustación',
            icon: '<svg width="18" height="18" viewBox="0 0 24 24"><path d="M10 16l-4-4 4-4v8zm4-8l4 4-4 4V8z"/></svg>'
        };
    }

    static get isReadOnlySupported() {
        return true;
    }

    static get enableLineBreaks() {
        return false;
    }

    constructor({ data }) {
        this.data = data || {};
        this.wrapper = undefined;
    }

    render() {

        this.wrapper = document.createElement('div');
        this.wrapper.classList.add('ovo-embed-block');

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Pegá la URL (YouTube, Vimeo o Spotify)';
        input.value = this.data.url || '';
        input.classList.add('form-control','my-3');

        this.preview = document.createElement('div');
        this.preview.classList.add('mt-2', 'text-muted', 'small');
        this.preview.textContent = `[[${this.data.service}]]`;

        input.addEventListener('input', (e) => {
            this.data.url = e.target.value;
            this.data.service = this.detectService(this.data.url);
            this.preview.textContent = `[[${this.data.service}]]`;
        });
        

        // 👇 MUY IMPORTANTE
        input.addEventListener('keydown', e => e.stopPropagation());
        input.addEventListener('click', e => e.stopPropagation());

        this.wrapper.appendChild(input);
        this.wrapper.appendChild(this.preview);

        return this.wrapper;
    }

    detectService(url) {
        if (!url) return null;

        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            return 'youtube';
        }

        if (url.includes('vimeo.com')) {
            return 'vimeo';
        }

        if (url.includes('spotify.com')) {
            return 'spotify';
        }

        return null;
    }

    save() {
        return {
            service: this.data.service || null,
            url: this.data.url || ''
        };
    }

    validate(savedData) {
        return !!savedData.url;
    }
}
