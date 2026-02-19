class OvoShortcode {

    static get toolbox() {
        return {
            title: 'Shortcode',
            icon: '<svg width="18" height="18" viewBox="0 0 24 24"><path d="M10 16l-4-4 4-4v8zm4-8l4 4-4 4V8z"/></svg>'
        };
    }

    static get isReadOnlySupported() {
        return true;
    }

    constructor({ data, readOnly }) {
        this.readOnly = readOnly;

        /* Cuando quieras agregar más shortcodes, solo sumás:
        // 
        this.shortcodes = [
            { value: 'email-form', label: 'Formulario de contacto' },
            { value: 'gallery', label: 'Galería' },
            { value: 'map', label: 'Mapa' }
        ];
        */
        this.shortcodes = [
            { value: 'email-form', label: 'Formulario de contacto' }
        ];

        this.data = {
            tag: data?.tag ?? 'email-form'
        };
    }

    validate(savedData) {
        return typeof savedData.tag === 'string' && savedData.tag.length > 0;
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.classList.add('ovo-shortcode-block');

        if (this.readOnly) {
            this.wrapper.textContent = `[[${this.data.tag}]]`;
            return this.wrapper;
        }

        this.select = document.createElement('select');
        this.select.classList.add('form-select', 'form-select-sm','w-auto');

        this.shortcodes.forEach(sc => {
            const option = document.createElement('option');
            option.value = sc.value;
            option.textContent = sc.label;

            if (sc.value === this.data.tag) {
                option.selected = true;
            }

            this.select.appendChild(option);
        });

        this.preview = document.createElement('div');
        this.preview.classList.add('mt-2', 'text-muted', 'small');
        this.preview.textContent = `[[${this.data.tag}]]`;

        this.select.addEventListener('change', () => {
            this.data.tag = this.select.value;
            this.preview.textContent = `[[${this.data.tag}]]`;
        });

        this.wrapper.appendChild(this.select);
        this.wrapper.appendChild(this.preview);

        return this.wrapper;
    }

    save() {
        return {
            tag: this.data.tag
        };
    }
}
