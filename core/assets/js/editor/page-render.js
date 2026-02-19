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
                const match = url.match(/(youtu\.be\/|v=)([^&]+)/);
                const videoId = match ? match[2] : null;
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
            }

            if (service === 'vimeo') {
                const match = url.match(/vimeo\.com\/(\d+)/);
                const videoId = match ? match[1] : null;
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
            }

            if (service === 'spotify') {
                const match = url.match(/open\.spotify\.com\/(track|album|playlist|episode|show)\/([a-zA-Z0-9]+)/);
                if (!match) return '';

                const type = match[1];
                const id = match[2];
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
            }

            return '';
        },

        // ---------------------------
        // OVO SHORTCODE
        // ---------------------------

        shortcode: function (block) {
            const tag = block.data?.tag;
            if (!tag) return '';
            return block.data?.html;
        }

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
