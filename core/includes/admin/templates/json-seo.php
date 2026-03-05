<?php
$formId = 'json-seo-form';

$currentValue = nqv::getConfig('json-seo');
$type = $currentValue['@type'] ?? '';

$dafaults = array_merge([
    'name' => empty($currentValue['name']) ? APP_TITLE:$currentValue['name'],
    'description' => empty($currentValue['description']) ? APP_DESCRIPTION:$currentValue['description'],
    'url' => empty($currentValue['url']) ? URL:$currentValue['url'],
    'logo' => empty($currentValue['logo']) ? (!empty(getAsset('images/logo.jpg')) ? URL . getAsset('images/logo.jpg'):URL . getAsset('images/logo.png')):$currentValue['logo'],
    'image' => empty($currentValue['image']) ? URL . getAsset('images/og-image.jpg'):$currentValue['image']
],array_filter($currentValue));

$defaults_json = json_encode($dafaults, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

if(submitted($formId)) {
    if(empty($_POST['seo'])) throw new Exception('Los datos del formulario están mal construidos');
    try {
        nqv::setConfig('json-seo',json_encode($_POST['seo'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        nqvNotifications::add('la configuración se actualizó con éxito','success');
    } catch(Exception $e) {
        nqvNotifications::add($e->getMessage(),'error');
    }
    header('location:');
    exit;
}
nqvNotifications::flush();
?>

<div class="container">
    <h2 class="my-5 pb-5">Configuración JSON-LD para SEO</h2>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" class="container">
        <input type="hidden" name="form-token" value="<?= get_token($formId) ?>" />
        <input type="hidden" name="seo[@context]" value="https://schema.org" />

        <div class="col-md-6 col-lg-4 mb-3">
            <label class="form-label">@type</label>
            <select name="seo[@type]" class="form-select">
                <optgroup label="Generales">
                    <?php $types = ['Organization','Person','LocalBusiness'] ?>
                    <?php foreach($types as $v): ?>
                        <?php $selected = $type === $v ? 'selected="selected"' : '' ?>
                        <option <?= $selected ?> value="<?= $v ?>"><?= $v ?></option>
                    <?php endforeach ?>
                </optgroup>

                <optgroup label="Empresas / Negocios">
                    <?php $types = ['Corporation','NGO','ProfessionalService','Service','OnlineStore'] ?>
                    <?php foreach($types as $v): ?>
                        <?php $selected = $type === $v ? 'selected="selected"' : '' ?>
                        <option <?= $selected ?> value="<?= $v ?>"><?= $v ?></option>
                    <?php endforeach ?>
                </optgroup>

                <optgroup label="Comercios físicos">
                    <?php $types = ['Store','Restaurant','CafeOrCoffeeShop','BarOrPub','MedicalBusiness'] ?>
                    <?php foreach($types as $v): ?>
                        <?php $selected = $type === $v ? 'selected="selected"' : '' ?>
                        <option <?= $selected ?> value="<?= $v ?>"><?= $v ?></option>
                    <?php endforeach ?>
                </optgroup>

                <optgroup label="Educación">
                    <?php $types = ['EducationalOrganization','School','CollegeOrUniversity'] ?>
                    <?php foreach($types as $v): ?>
                        <?php $selected = $type === $v ? 'selected="selected"' : '' ?>
                        <option <?= $selected ?> value="<?= $v ?>"><?= $v ?></option>
                    <?php endforeach ?>
                </optgroup>

                <optgroup label="Tecnología">
                    <?php $types = ['SoftwareApplication','WebSite','WebPage'] ?>
                    <?php foreach($types as $v): ?>
                        <?php $selected = $type === $v ? 'selected="selected"' : '' ?>
                        <option <?= $selected ?> value="<?= $v ?>"><?= $v ?></option>
                    <?php endforeach ?>
                </optgroup>

            </select>
        </div>

        <div id="dynamic-fields" class="mt-4"></div>

        <div class="my-5">
            <button type="submit" class="btn btn-success">Guardar configuración</button>
        </div>

        </form>
</div>
<script>
    const schemaModel = {
        Thing: {
            fields: [
                { name: "name", label: "Nombre", type: "text" },
                { name: "description", label: "Descripción", type: "textarea" },
                { name: "url", label: "URL", type: "url" },
                { name: "image", label: "Imagen", type: "url" }
            ]
        },
        Organization: {
            extends: "Thing",
            fields: [
                { name: "logo", label: "Logo URL", type: "url" },
                { name: "legalName", label: "Razón social", type: "text" }
            ]
        },
        LocalBusiness: {
            extends: "Organization",
            fields: [
                { name: "telephone", label: "Teléfono", type: "text" },
                { name: "priceRange", label: "Rango de precios", type: "text" }
            ]
        },
        Restaurant: {
            extends: "LocalBusiness",
            fields: [
                { name: "servesCuisine", label: "Tipo de cocina", type: "text" },
                { name: "menu", label: "URL del menú", type: "url" }
            ]
        },
        Person: {
            extends: "Thing",
            fields: [
                { name: "jobTitle", label: "Profesión", type: "text" },
                { name: "birthDate", label: "Fecha de nacimiento", type: "date" }
            ]
        },
        WebSite: {
            extends: "Thing",
            fields: []
        },
        SoftwareApplication: {
            extends: "Thing",
            fields: [
                { name: "applicationCategory", label: "Categoría", type: "text" },
                { name: "operatingSystem", label: "Sistema Operativo", type: "text" }
            ]
        },

        // ── Tipos añadidos para consistencia con select ──
        Corporation: {
            extends: "Organization",
            fields: []
        },
        NGO: {
            extends: "Organization",
            fields: []
        },
        ProfessionalService: {
            extends: "Organization",
            fields: []
        },
        Service: {
            extends: "Organization",
            fields: []
        },
        OnlineStore: {
            extends: "LocalBusiness",
            fields: []
        },
        Store: {
            extends: "LocalBusiness",
            fields: []
        },
        CafeOrCoffeeShop: {
            extends: "LocalBusiness",
            fields: []
        },
        BarOrPub: {
            extends: "LocalBusiness",
            fields: []
        },
        MedicalBusiness: {
            extends: "LocalBusiness",
            fields: []
        },
        EducationalOrganization: {
            extends: "Organization",
            fields: []
        },
        School: {
            extends: "EducationalOrganization",
            fields: []
        },
        CollegeOrUniversity: {
            extends: "EducationalOrganization",
            fields: []
        },
        WebPage: {
            extends: "WebSite",
            fields: []
        }
    };

    function resolveFields(type) {
        let fields = [];
        while (type) {
            const model = schemaModel[type];
            if (!model) break;
            if (model.fields) fields = [...model.fields, ...fields];
            type = model.extends;
        }
        return fields;
    }
</script>
<script>
    const typeSelect = document.querySelector('select[name="seo[@type]"]');
    const container = document.getElementById('dynamic-fields');

    function renderFields(type) {

        container.innerHTML = "";
        defaults = JSON.parse(`<?= $defaults_json ?>`);

        console.log(defaults)

        const fields = resolveFields(type);

        fields.forEach(field => {
            const value = defaults[field.name] ?? '';
            const wrapper = document.createElement('div');
            wrapper.className = "my-3";

            let inputHTML = "";

            if (field.type === "textarea") {
                inputHTML = `
                    <textarea name="seo[${field.name}]"
                            class="form-control"
                            rows="3">${value}</textarea>
                `;
            } else {
                inputHTML = `
                    <input type="${field.type}"
                        name="seo[${field.name}]"
                        value="${value}"
                        class="form-control">
                `;
            }

            wrapper.innerHTML = `
                <label class="form-label">${field.label}</label>
                ${inputHTML}
            `;

            container.appendChild(wrapper);
        });
    }

    typeSelect.addEventListener('change', e => {
        renderFields(e.target.value);
    });

    document.addEventListener('DOMContentLoaded', () => {
        renderFields(typeSelect.value);
    });
</script>