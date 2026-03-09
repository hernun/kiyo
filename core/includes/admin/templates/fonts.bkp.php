<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<title>Selector Google Fonts</title>
</head>
<body class="p-4">

<div class="container">
<h3 class="mb-4">Selector de Google Fonts</h3>

<form method="post" action="/procesar-font.php" id="fontForm">

<div class="row g-3">

<div class="col-md-4">
<label class="form-label">Buscar fuente</label>
<input type="text" class="form-control" id="searchFont" placeholder="Roboto, Lato...">
</div>

<div class="col-md-3">
<label class="form-label">Estilo</label>
<select class="form-select" id="category">
<option value="">Todos</option>
<option value="serif">Serif</option>
<option value="sans-serif">Sans Serif</option>
<option value="monospace">Monospace</option>
<option value="display">Display</option>
<option value="handwriting">Handwriting</option>
</select>
</div>

<div class="col-md-3">
<label class="form-label">Idioma / Subset</label>
<select class="form-select" id="subset">
<option value="">Latin</option>
<option value="latin-ext">Latin Extended</option>
<option value="cyrillic">Cyrillic</option>
<option value="greek">Greek</option>
<option value="vietnamese">Vietnamese</option>
</select>
</div>

<div class="col-md-2">
<label class="form-label">Peso</label>
<select class="form-select" id="weight" multiple>
<option value="100">100</option>
<option value="200">200</option>
<option value="300">300</option>
<option value="400" selected>400</option>
<option value="500">500</option>
<option value="600">600</option>
<option value="700">700</option>
<option value="800">800</option>
<option value="900">900</option>
</select>
</div>

</div>

<hr>

<div class="mb-3">
<label class="form-label">Fuente</label>
<select class="form-select" id="fontList"></select>
</div>

<div class="mb-3">
<label class="form-label">URL generada</label>
<input class="form-control" id="fontUrl" name="font_url" readonly>
</div>

<button class="btn btn-primary">
Usar esta fuente
</button>

</form>
</div>

<script>

const API_KEY = "TU_API_KEY";
let fonts = [];

async function loadFonts(){

const res = await fetch(
`https://www.googleapis.com/webfonts/v1/webfonts?key=${API_KEY}`
);

const data = await res.json();
fonts = data.items;

renderFonts(fonts);

}

function renderFonts(list){

const select = document.getElementById('fontList');
select.innerHTML = '';

list.forEach(font=>{
const opt = document.createElement('option');
opt.value = font.family;
opt.textContent = font.family;
select.appendChild(opt);
});

updateURL();

}

function filterFonts(){

const search = document.getElementById('searchFont').value.toLowerCase();
const category = document.getElementById('category').value;
const subset = document.getElementById('subset').value;

const filtered = fonts.filter(font=>{

if(search && !font.family.toLowerCase().includes(search)) return false;

if(category && font.category !== category) return false;

if(subset && !font.subsets.includes(subset)) return false;

return true;

});

renderFonts(filtered);

}

function updateURL(){

const family = document.getElementById('fontList').value;

const weights = [...document.getElementById('weight').selectedOptions]
.map(o=>o.value)
.join(';');

const subset = document.getElementById('subset').value;

let url =
`https://fonts.googleapis.com/css2?family=${family.replace(/ /g,'+')}:wght@${weights}&display=swap`;

if(subset)
url += `&subset=${subset}`;

document.getElementById('fontUrl').value = url;

}

document.getElementById('searchFont').addEventListener('input',filterFonts);
document.getElementById('category').addEventListener('change',filterFonts);
document.getElementById('subset').addEventListener('change',filterFonts);

document.getElementById('fontList').addEventListener('change',updateURL);
document.getElementById('weight').addEventListener('change',updateURL);

loadFonts();

</script>

</body>
</html>