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
            header: {
                class: Header,
                inlineToolbar: true,
                config: {
                    levels: [2,3,4,5,6],
                    defaultLevel: 5
                }
            },
            list: List,
            paragraph: {
                class: Paragraph,
                inlineToolbar: true
            },
            embed: {
                class: OvoEmbed
            },
            embedgallery: {
                class: OvoEmbedGallery
            },
            shortcode: {
                class: OvoShortcode
            },
            spacer: {
                class: OvoSpacer
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
