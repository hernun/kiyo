<?php
$formId = 'googlefonts-selector';
if(submitted($formId)) {
    $fonts = !empty(nqv::getConfig('fonts-list')) ? nqv::getConfig('fonts-list'):[];
    $fonts[] = ['family'=>$_POST['font_family'],'url'=>$_POST['font_url']];
    nqv::setConfig('fonts-list',json_encode($fonts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('location:');
    exit;
}
nqvNotifications::flush();
?>
    <div class="container">
        <h3 class="mb-4">Selector de Google Fonts</h3>
        <form method="post" id="fontForm">
            <input type="hidden" name="form-token" value="<?php echo get_token($formId)?>" />
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input class="form-control" id="search">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Categoría</label>
                    <select class="form-select" id="category">
                        <option value="">Todas</option>
                        <option value="serif">Serif</option>
                        <option value="sans-serif">Sans Serif</option>
                        <option value="monospace">Monospace</option>
                        <option value="display">Display</option>
                        <option value="handwriting">Handwriting</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Subset / Idioma</label>
                    <select class="form-select" id="subset">
                        <option value="">latin</option>
                        <option value="latin-ext">latin-ext</option>
                        <option value="cyrillic">cyrillic</option>
                        <option value="greek">greek</option>
                        <option value="vietnamese">vietnamese</option>
                    </select>
                </div>
            </div>

            <div class="my-3">
                <label class="form-label">Fuente</label>
                <select class="form-select" id="fontList"></select>
            </div>

            <div class="my-3">
                <label class="form-label">Pesos</label>
                <select class="form-select" id="weights" multiple></select>
            </div>

            <div class="preview" id="googlefont-preview">The quick brown fox jumps over the lazy dog</div>

            <div class="my-3">
                <label class="form-label">URL</label>
                <input class="form-control" name="font_url" id="fontUrl">
            </div>

            <div class="my-3">
                <label class="form-label">CSS font-family</label>
                <input class="form-control" name="font_family" id="fontFamily">
            </div>

            <button class="btn btn-primary">Agregar fuente</button>

        </form>

    </div>

    <script>
        const CACHE_KEY = "ovo_google_fonts_v1";

        let fonts = [];
        let currentFonts = [];

        async function loadFonts(){
            //localStorage.removeItem(CACHE_KEY);
            if(localStorage[CACHE_KEY]){
                fonts = JSON.parse(localStorage[CACHE_KEY]);
            }else{
                const res = await fetch('https://ovo.nqv.ar/api/fonts');
                fonts = await res.json();
                localStorage[CACHE_KEY] = JSON.stringify(fonts);
            }

            currentFonts = fonts.items;
            renderFonts();

        }

        function renderFonts(){
            const select = document.getElementById("fontList");
            select.innerHTML = "";

            currentFonts.forEach(font=>{
                const o = document.createElement("option");
                o.value = font.family;
                o.textContent = font.family;
                select.appendChild(o);
            });

            updateWeights();
            updateURL();

        }

        function filterFonts(){

            const search = document.getElementById("search").value.toLowerCase();
            const category = document.getElementById("category").value;
            const subset = document.getElementById("subset").value;

            currentFonts = fonts.items.filter(f=>{
                if(search && !f.family.toLowerCase().includes(search)) return false;
                if(category && f.category !== category) return false;
                if(subset && !f.subsets.includes(subset)) return false;
                return true;
            });

            renderFonts();
        }

        function updateWeights(){
            const family = document.getElementById("fontList").value;
            const font = fonts.items.find(f=>f.family===family);

            if(!font) return;

            const select = document.getElementById("weights");
            select.innerHTML = "";
            font.variants.forEach(v=>{
                if(v === "italic") return;
                const weight = v.replace("regular","400");
                const o = document.createElement("option");
                o.value = weight;
                o.textContent = weight;
                if(weight === "400") o.selected = true;
                select.appendChild(o);
            });
        }

        function updateURL(){
            const family = document.getElementById("fontList").value;
            const weights = [...document.getElementById("weights").selectedOptions]
                .map(o=>o.value)
                .sort()
                .join(";");

            let url = `https://fonts.googleapis.com/css2?family=${family.replace(/ /g,"+")}`;
            if(weights) url += `:wght@${weights}`;
            url += "&display=swap";
            
            document.getElementById("fontUrl").value = url;
            document.getElementById("fontFamily").value = `'${family}', sans-serif;`;

            loadPreview(family);
        }

        function loadPreview(font){
            const id = "previewFont";
            let link = document.getElementById(id);

            if(!link){
                link = document.createElement("link");
                link.rel = "stylesheet";
                link.id = id;
                document.head.appendChild(link);
            }

            const preview = document.getElementById("googlefont-preview");
            if(font) {
                preview.classList.remove('unknown');
                link.href = document.getElementById("fontUrl").value;
                preview.style.fontFamily = `'${font}', sans-serif`;
            } else {
                preview.style.fontFamily ='system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
                preview.classList.add('unknown');
            }
        }

        document.getElementById("search").addEventListener("input",filterFonts);
        document.getElementById("category").addEventListener("change",filterFonts);
        document.getElementById("subset").addEventListener("change",filterFonts);

        document.getElementById("fontList").addEventListener("change",()=>{
            updateWeights();
            updateURL();
        });

        document.getElementById("weights").addEventListener("change",updateURL);

        loadFonts();
    </script>