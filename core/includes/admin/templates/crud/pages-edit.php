<?php
$tablename = nqv::getVars(1);
$id = (int) nqv::getVars(3);
$table = new nqvDbTable($tablename);
$fields = $table->getTableFields();
$formId = 'create-' . $tablename;
$object = new nqvPages(['id'=>$id]);
$item = $object->getData();

if(submitted($formId)) {
    try {
        nqv::parseTags($tablename);

        // Suponiendo que recibís el JSON desde el input hidden
        $rawContent = $_POST['content'] ?? '{}';
        // 1. Convertir a objeto PHP
        $data = json_decode($rawContent, true);

        // 2. Recorrer los bloques y limpiar los textos
        if (!empty($data['blocks'])) {
            foreach ($data['blocks'] as &$block) {
                if (!empty($block['data']['text'])) {
                    // Reemplaza saltos de línea y tabs por espacios
                    $block['data']['text'] = str_replace(["\r", "\n", "\t"], ' ', $block['data']['text']);
                    // Opcional: quitar múltiples espacios consecutivos
                    $block['data']['text'] = preg_replace('/\s+/', ' ', $block['data']['text']);
                    // Trim
                    $block['data']['text'] = trim($block['data']['text']);
                }
            }
        }

        // 3. Volver a JSON limpio para guardar
        $cleanJson = json_encode($data, JSON_UNESCAPED_UNICODE);

        // Guardar $cleanJson en la DB
        $_POST['content'] = $cleanJson;

        if(nqvDB::save($tablename, $_POST)) nqvNotifications::add('El registro ha sido actualizado con éxito','success');
    } catch(Exception $e) {
        nqvNotifications::add('Hubo un error que detuvo el proceso: ' . $e->getMessage(),'error');
    }
    header('location:' . getAdminUrl() . $tablename);
    exit;
}
?>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest/dist/editorjs.umd.min.js"></script>
<!-- Herramientas (plugins) UMD -->
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest/dist/header.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/paragraph@latest/dist/paragraph.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest/dist/list.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/link@2.5.0"></script>

<div class="my-4">
    <?php if(nqv::userCan(['create',$tablename])):?>
        <?php $list = new nqvList($tablename)?>
        <?php echo $list->getHeader()?>
        <div class="form-container d-flex justify-content-center">
            <form id="<?php echo $formId?>" class="needs-validation" method="post" accept-charset="utf8" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="form-token" value="<?php echo get_token($formId)?>" />
                <div class="row my-lg-4">
                    <div class="form-group mb-3 mb-lg-0 col-lg">
                        <label style="width:200px;text-align:center">Imagen principal</label>
                        <?php echo get_main_image_input_pro($tablename, $id, null, null)?>
                    </div>
                </div>
                <div class="row" style="max-width:1400px">
                    <?php $f = new nqvDbField($fields['title'],$tablename);?>
                    <div class="col-12 pages-title-field col-lg-6 col-xl-6"><?php echo $f->setValue($item['title']);?></div>
                    <?php $f = new nqvDbField($fields['slug'],$tablename);?>
                    <div class="col-12 pages-slug-field col-lg-6 col-xl-6"><?php echo $f->setValue($item['slug']);?></div>
                    <?php $f = new nqvDbField($fields['description'],$tablename);?>
                    <div class="col-12 pages-description-field"><?php echo $f->setValue($item['description']);?></div>
                    <?php foreach($fields as $field):?>
                        <?php if($field['Field'] === 'title') continue?>
                        <?php if($field['Field'] === 'slug') continue?>
                        <?php if($field['Field'] === 'description') continue?>
                        <?php if($field['Field'] === 'created_at') continue?>
                        <?php if($field['Field'] === 'created_by') continue?>
                        <?php if($field['Field'] === 'modified_at') continue?>
                        <?php if($field['Field'] === 'content') continue?>
                        <?php $f = new nqvDbField($field,$tablename)?>
                        <?php $f->setValue($item[$field['Field']])?>
                        <?php if(!currentSessionTypeIs('root') && $field['Field'] === 'slug') $f->setHtmlInputType('hidden')?>
                        <?php if($f->isHidden()):  echo $f;?>
                        <?php else:?>
                            <div class="col-lg-6 col-xl-4"><?php echo $f?></div>
                        <?php endif?>
                    <?php endforeach?>
                    <div class="col-12 pages-content-field">
                        <div class="editor-wrapper">
                            <div id="editorjs-content"></div>
                        </div>
                        <input type="hidden" id="editorjs-input" name="content">
                    </div>
                </div>
                <div class="my-3">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    <?php else:?>
        <h4>No tenés permiso para acceder a esta sección</h4>
    <?php endif?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const titleInput = document.getElementById('title-input');
        const slugInput  = document.getElementById('slug-input');

        if (!titleInput || !slugInput) return;

        const removeAccents = (str = '') =>
            str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

        titleInput.addEventListener('input', () => {
            const slug = removeAccents(titleInput.value)
                .toLowerCase()
                .trim()
                .replace(/[\s\W-]+/g, '-')
                .replace(/^-+|-+$/g, '');

            slugInput.value = slug;
        });
    });

    window.editorJsData = <?php
        echo !empty($item['content'])
            ? $item['content']
            : 'null';
    ?>;

   document.addEventListener('DOMContentLoaded', function () {
    const editor = new EditorJS({
        holder: 'editorjs-content',
        data: window.editorJsData ?? undefined,
        i18n: {
            messages: {
                ui: {
                    "blockTunes": {
                        "toggler": {
                            "Click to tune": "Configurar bloque",
                            "or drag to move": "o arrastrar para mover"
                        }
                    },
                    "inlineToolbar": {
                        "converter": "Convertir"
                    },
                    "toolbar": {
                        "toolbox": {
                            "Add": "Agregar"
                        }
                    }
                },

                toolNames: {
                    "Text": "Texto",
                    "Heading": "Encabezado",
                    "List": "Lista",
                    "Quote": "Cita",
                    "Bold": "Negrita",
                    "Italic": "Cursiva",
                    "Link": "Enlace"
                },

                tools: {
                    "list": {
                        "Ordered": "Lista numerada",
                        "Unordered": "Lista con viñetas"
                    },
                    "header": {
                        "Heading 1": "Encabezado 1",
                        "Heading 2": "Encabezado 2",
                        "Heading 3": "Encabezado 3"
                    }
                },

                blockTunes: {
                    "delete": {
                        "Delete": "Eliminar"
                    },
                    "moveUp": {
                        "Move up": "Mover arriba"
                    },
                    "moveDown": {
                        "Move down": "Mover abajo"
                    },

                    "convertTo": {
                        "Convert to": "Convertir a"
                    }
                }
            }
        },

        tools: {
            header: Header,
            list: List,
            paragraph: {
                class: Paragraph,
                inlineToolbar: true
            }
        }
    });


    // Submit del formulario
    const form = document.getElementById('<?php echo $formId ?>');
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        editor.save().then((outputData) => {
            document.getElementById('editorjs-input').value =
                JSON.stringify(outputData);
            form.submit();
        });
    });

});


</script>
