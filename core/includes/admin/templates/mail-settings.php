<?php
$mailConfig = new nqvMailConfig();
$formId = 'mail-settings-form';

if (submitted($formId)) {

    $active = $_POST['active_driver'] ?? '';

    $mailConfig->setActive($active);

    $mailConfig->updateDriver($active, [
        'host'          => $_POST['host'] ?? '',
        'port'          => $_POST['port'] ?? 0,
        'smtp_auth'     => isset($_POST['smtp_auth']),
        'smtp_secure'   => $_POST['smtp_secure'] ?? '',
        'smtp_auto_tls' => isset($_POST['smtp_auto_tls']),
        'region'        => $_POST['region'] ?? '',
        'endpoint'      => $_POST['endpoint'] ?? '',
    ]);

    $mailConfig->updateNested($active, 'from', [
        'address' => $_POST['from_address'] ?? '',
        'name'    => $_POST['from_name'] ?? '',
    ]);

    $mailConfig->save();

    header('location:');
    exit;
}

$activeDriver = $mailConfig->getActive();
$drivers = $mailConfig->getDrivers();
$current = $mailConfig->getDriver($activeDriver);
?>

<div class="container">
    <h2 class="my-5 pb-5">Configuración de correo electrónico</h2>
    <form id="<?php echo $formId?>" class="needs-validation" method="post" accept-charset="utf8" novalidate>
        <input type="hidden" name="form-token" value="<?php echo get_token($formId) ?>" />

        <div class="row my-5" style="max-width:1400px">
            <div class="col-2">
            <label for="active_driver-input" >Driver activo</label>
            </div>
            <div class="col-9 col-lg-6 col-xl-4">
                <select name="active_driver" id="active_driver-input" class="form-select">
                <?php foreach($drivers as $name => $driver): ?>
                    <option value="<?= $name ?>" <?= $activeDriver === $name ? 'selected' : '' ?>>
                        <?= strtoupper($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </div>
        </div>

        <hr>
        <div class="row my-5" style="max-width:1400px">
            <div id="driver-fields" class="my-4"></div>
        </div>

        <div class="my-3" style="max-width:1400px">
            <button type="submit" name="save_mail_settings" class="btn btn-success">Guardar configuración</button>
        </div>
    </form>
    <form method="post" action="/admin/test-mail-reset">
            <div class="row my-3" style="max-width:1400px">
                <input type="hidden" name="form-token" value="<?php echo get_token('test-mail-form',false)?>" />
                <div class="col-9 col-lg-6 col-xl-4 my-3">
                    <input type="email" name="email" class="form-control" placeholder="Email destino" />
                </div>
                <div class="col-9 col-lg-6 col-xl-4 my-3">
                    <button type="submit" class="btn btn-primary" name="test-mail-form">Enviar mail de prueba</button>
                </div>
            </div>
        </form>
</div>
<script>

const mailConfig = <?= json_encode($drivers, JSON_PRETTY_PRINT) ?>;
const activeDriver = "<?= $activeDriver ?>";

</script>
<script>

const driverSelect = document.getElementById('active_driver-input');
const fieldsContainer = document.getElementById('driver-fields');

function createFormRow() {
    const row = document.createElement('div');
    row.className = 'row mb-3 align-items-center';
    return row;
}

function createLabel(text) {
    const col = document.createElement('div');
    col.className = 'col-2';

    const label = document.createElement('label');
    label.className = 'col-form-label';
    label.innerText = text;

    col.appendChild(label);
    return col;
}

function createInputCol() {
    const col = document.createElement('div');
    col.className = 'col-9 col-lg-6 col-xl-4';
    return col;
}

function createInput(labelText, name, value, type = 'text') {

    const row = createFormRow();
    const labelCol = createLabel(labelText);
    const inputCol = createInputCol();

    let input;

    // CHECKBOX
    if(type === 'checkbox') {

        const divCheck = document.createElement('div');
        divCheck.className = 'form-check';

        input = document.createElement('input');
        input.className = 'form-check-input';
        input.type = 'checkbox';
        input.name = name;
        input.checked = !!value;

        divCheck.appendChild(input);
        inputCol.appendChild(divCheck);
    }

    // SELECT SECURE
    else if(type === 'select_secure') {

        input = document.createElement('select');
        input.className = 'form-select';
        input.name = name;

        const options = [
            {value: '', label: 'None'},
            {value: 'tls', label: 'TLS'},
            {value: 'ssl', label: 'SSL'}
        ];

        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.innerText = opt.label;
            if(opt.value === value) option.selected = true;
            input.appendChild(option);
        });

        inputCol.appendChild(input);
    }

    // NORMAL INPUT
    else {

        input = document.createElement('input');
        input.className = 'form-control';
        input.type = type;
        input.name = name;
        input.value = value ?? '';

        inputCol.appendChild(input);
    }

    row.appendChild(labelCol);
    row.appendChild(inputCol);

    return row;
}

function renderDriverFields(driverName) {

    fieldsContainer.innerHTML = '';

    const config = mailConfig[driverName];
    if(!config) return;

    // SMTP-like drivers
    if(config.host !== undefined) {

        fieldsContainer.appendChild(createInput('Host', 'host', config.host));
        fieldsContainer.appendChild(createInput('Port', 'port', config.port, 'number'));
        fieldsContainer.appendChild(createInput('SMTP Auth', 'smtp_auth', config.smtp_auth, 'checkbox'));
        fieldsContainer.appendChild(createInput('Secure', 'smtp_secure', config.smtp_secure, 'select_secure'));
        fieldsContainer.appendChild(createInput('Auto TLS', 'smtp_auto_tls', config.smtp_auto_tls, 'checkbox'));
    }

    // SES
    if(config.region !== undefined) {

        fieldsContainer.appendChild(createInput('Region', 'region', config.region));
        fieldsContainer.appendChild(createInput('Endpoint', 'endpoint', config.endpoint));
    }

    // FROM block (común)
    if(config.from) {

        fieldsContainer.appendChild(createInput('From Address', 'from_address', config.from.address, 'email'));
        fieldsContainer.appendChild(createInput('From Name', 'from_name', config.from.name));
    }
}

driverSelect.addEventListener('change', function() {
    renderDriverFields(this.value);
});

// Render inicial
renderDriverFields(activeDriver);

</script>
