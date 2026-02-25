class OvoSpacer {

    static get toolbox() {
        return {
            title: 'Espacio',
            icon: '<svg width="18" height="18"><rect width="18" height="4" y="7"/></svg>'
        };
    }

    render() {
        const div = document.createElement('div');
        div.classList.add('ovo-spacer');
        return div;
    }

    save() {
        return {};
    }
}