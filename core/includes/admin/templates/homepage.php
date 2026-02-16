<?php
$formId = 'homepage-selector-form';
$current_homepage = nqv::getConfig('homepage');
if(submitted($formId)) {
    $current_homepage[$_SESSION['CURRENT_LANGUAGE']]['pages_id'] = intval($_POST['pages_id']);
    $value = json_encode($current_homepage, JSON_UNESCAPED_UNICODE);
    nqv::setConfig('homepage', $value);
    header('location:');
    exit;
}
$pages = nqv::get('pages',['lang'=>$_SESSION['CURRENT_LANGUAGE']]);
$current_page_id = intval(@$current_homepage[$_SESSION['CURRENT_LANGUAGE']]['pages_id']);
$current_page = getPageById($current_page_id);
$src = !empty($current_page['slug']) ? '/' . $current_page['slug']:'/';

?>

<div class="my-4">
    <div class="form-container d-flex justify-content-center">
        <form id="<?php echo $formId?>" class="needs-validation" method="post" accept-charset="utf8" novalidate>
            <h2>Página de inicio</h2>
            <input type="hidden" name="form-token" value="<?php echo get_token($formId)?>" />
            <div class="" style="width:1400px">
                <select id="pages_id-input" name="pages_id" class="form-select" aria-label="Seleccionar hompage">
                    <option value="">Plantilla de inicio</option>
                    <?php foreach($pages as $page):?>
                        <?php $selected = $current_page_id === intval($page['id']) ? 'selected="selected"':'';?>
                        <option value="<?php echo $page['id']?>" data-slug="<?php echo $page['slug']?>" <?php echo $selected?>><?php echo $page['title']?></option>
                    <?php endforeach?>
                </select>
            </div>
            <div class="my-3">
                <button type="submit" class="btn btn-primary">Enviar</button>
            </div>

            <iframe id="page-preview" class="page-preview" src="<?= $src ?>"></iframe>
        </form>
    </div>
</div>

<script type="text/javascript">
    $('#pages_id-input').on({
        change: function(e){
            var slug = $(this).find('option:selected').data('slug');
            if(!slug || slug === 'undefined' || slug === undefined) $('#page-preview').attr('src','/');
            else $('#page-preview').attr('src','/' + slug);
        }
    })
</script>