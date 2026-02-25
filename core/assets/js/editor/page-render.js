(function () {

    if (!window.OVO_PAGE_CONTENT) return;

    // ---------------------------
    // PARSER
    // ---------------------------

    const edjsParser = edjsHTML({
        // ---------------------------
        // OVO EMBED
        // ---------------------------
        embed: function (block) {

            const { service, url } = block.data || {};
            if (!service || !url) return '';

            if (service === 'youtube') {

                try {
                    const parsed = new URL(url);
                    let videoId = null;

                    if (parsed.hostname.includes('youtu.be')) {
                        videoId = parsed.pathname.slice(1);
                    }

                    if (parsed.hostname.includes('youtube.com')) {

                        if (parsed.pathname === '/watch') {
                            videoId = parsed.searchParams.get('v');
                        }

                        if (parsed.pathname.startsWith('/shorts/')) {
                            videoId = parsed.pathname.split('/')[2];
                        }

                        if (parsed.pathname.startsWith('/embed/')) {
                            videoId = parsed.pathname.split('/')[2];
                        }
                    }

                    if (!videoId) return '';

                    return `
                        <div class="ovo-embed-responsive">
                            <iframe 
                                src="https://www.youtube.com/embed/${videoId}" 
                                frameborder="0" 
                                allowfullscreen 
                                loading="lazy">
                            </iframe>
                        </div>
                    `;

                } catch {
                    return '';
                }
            }

            if (service === 'vimeo') {
                try {
                    const parsed = new URL(url);
                    const parts = parsed.pathname.split('/').filter(Boolean);

                    // último segmento numérico
                    const videoId = parts.reverse().find(p => /^\d+$/.test(p));

                    if (!videoId) return '';

                    return `
                        <div class="ovo-embed-responsive">
                            <iframe 
                                src="https://player.vimeo.com/video/${videoId}" 
                                frameborder="0" 
                                allowfullscreen 
                                loading="lazy">
                            </iframe>
                        </div>
                    `;

                } catch {
                    return '';
                }
            }

            if (service === 'spotify') {
                try {
                    const parsed = new URL(url);
                    const parts = parsed.pathname.split('/').filter(Boolean);

                    // puede ser: ["playlist", "..."]
                    // o: ["intl-es", "track", "..."]

                    let type, id;

                    if (parts[0].startsWith('intl-')) {
                        type = parts[1];
                        id = parts[2];
                    } else {
                        type = parts[0];
                        id = parts[1];
                    }

                    if (!type || !id) return '';

                    const height = (type === 'track' || type === 'episode') ? 152 : 380;

                    return `
                        <div class="ovo-spotify-embed">
                            <iframe 
                                src="https://open.spotify.com/embed/${type}/${id}"
                                width="100%" 
                                height="${height}"
                                frameborder="0"
                                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                                loading="lazy">
                            </iframe>
                        </div>
                    `;

                } catch (e) {
                    return '';
                }
            }
        },

        // ---------------------------
        // OVO EMBED GALLERY
        // ---------------------------

        embedgallery: (block) => {
            if (!block.data.embeds) return '';

            // Contenedor principal de la galería
            const wrapperStart = `<div class="ovo-embed-gallery-wrapper">`;
            const wrapperEnd = `</div>`;

            const innerHtml = block.data.embeds.map(e => {
                let iframeHtml = '';

                if (e.service === 'youtube') {
                    const videoId = new URL(e.url).searchParams.get('v') || e.url.split('/').pop();
                    iframeHtml = `<iframe src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen loading="lazy"></iframe>`;
                } else if (e.service === 'vimeo') {
                    const id = new URL(e.url).pathname.split('/').filter(Boolean).pop();
                    iframeHtml = `<iframe src="https://player.vimeo.com/video/${id}" frameborder="0" allowfullscreen loading="lazy"></iframe>`;
                } else if (e.service === 'spotify') {
                    const parts = new URL(e.url).pathname.split('/').filter(Boolean);
                    const type = parts[0].startsWith('intl-') ? parts[1] : parts[0];
                    const id = parts[0].startsWith('intl-') ? parts[2] : parts[1];
                    const height = (type === 'track' || type === 'episode') ? 152 : 380;
                    iframeHtml = `<iframe src="https://open.spotify.com/embed/${type}/${id}" width="100%" height="${height}" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>`;
                }

                // Cada embed dentro de un div.inner
                return `<div class="ovo-embed-gallery-item-inner">${iframeHtml}</div>`;
            }).join('');

            return wrapperStart + innerHtml + wrapperEnd;
        },

        // ---------------------------
        // OVO SHORTCODE
        // ---------------------------

        shortcode: function (block) {
            const tag = block.data?.tag;
            if (!tag) return '';
            return block.data?.html;
        },

        // ---------------------------
        // OVO SPACER
        // ---------------------------
        
        spacer: () => '<div class="ovo-spacer"></div>'

    });

    // ---------------------------
    // RENDER
    // ---------------------------

    try {
        const savedData = JSON.parse(window.OVO_PAGE_CONTENT);
        const html = edjsParser.parse(savedData);
        const container = document.getElementById('page-content');

        if (container) {
            container.innerHTML = html.join('');
        }
    } catch (e) {
        console.error('OVO render error:', e);
    }

})();
