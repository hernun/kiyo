document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById(window.ovoFormId);
    const hiddenInput = document.getElementById('editorjs-input');

    // ---------------------------
    // Inicializar Editor
    // ---------------------------
    const editor = new EditorJS({
        holder: 'editorjs-content',
        data: window.editorJsData || undefined,
        tools: {
            header: Header,
            list: List,
            paragraph: {
                class: Paragraph,
                inlineToolbar: true
            },
            embed: {
                class: OvoEmbed
            },
            shortcode: {
                class: OvoShortcode
            }
        }
    });

    // ---------------------------
    // Función centralizada de guardado
    // ---------------------------
    async function saveEditor() {
        const outputData = await editor.save();
        hiddenInput.value = JSON.stringify(outputData);
        form.submit();
    }

    // ---------------------------
    // Submit normal
    // ---------------------------
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        saveEditor();
    });

    // ---------------------------
    // Cmd + S / Ctrl + S
    // ---------------------------
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            saveEditor();
        }
    });

});
