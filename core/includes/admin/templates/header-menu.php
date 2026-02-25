<?php
if(!nqv::userCan(['crud','header-menu'])) {
    header('location:' . getAdminUrl());
    exit;
}

$menuArray = nqv::getConfig('header-menu');
$dbpages = nqv::get('pages',['lang'=>$_SESSION['CURRENT_LANGUAGE']]);

$lang = 'ES';
$selectedSlugs = $menuArray[$lang] ?? [];

$origin = [];
$destiny = [];

/* indexamos dbpages por slug para lookup rápido */
$indexed = [];
foreach($dbpages as $page) $indexed[$page['slug']] = $page;

/* destiny = lo que está en el menú (respetando orden) */
foreach($selectedSlugs as $slug) {
    if(isset($indexed[$slug])) $destiny[] = $indexed[$slug];
}

/* origin = lo que NO está seleccionado */
foreach($dbpages as $page){
    if(!in_array($page['slug'], $selectedSlugs)) $origin[] = $page;
}
?>
<div class="container">
    <h1 class="my-5">Menú del encabezado del front</h1>
    <div id="menu-manager" data-lang="<?php echo $lang ?>" class="d-flex gap-4">
        <!-- ORIGIN -->
        <div id="origin" class="border rounded p-3 w-100">
            <?php foreach($origin as $page): ?>
                <div class="item" data-value="<?php echo $page['slug'] ?>">
                    <div class="label"><?php echo ucfirst($page['title']) ?></div>
                    <div class="remove"><i class="bi bi-trash"></i></div>
                </div>
            <?php endforeach ?>
        </div>

        <!-- DESTINY -->
        <div id="destiny" class="border rounded p-3 w-100">
            <?php foreach($destiny as $page): ?>
                <div class="item" data-value="<?php echo $page['slug'] ?>">
                    <div class="label"><?php echo ucfirst($page['title']) ?></div>
                    <div class="remove"><i class="bi bi-trash"></i></div>
                </div>
            <?php endforeach ?>
        </div>

    </div>
</div>

<script type="text/javascript">
    const setMenuList = () => {
        const list = [];
        
        $('#destiny .item').each(function(){
            list.push($(this).data('value'));
        });

        const lang = $('#menu-manager').data('lang');

        console.log(lang,list)

        $.ajax({
            url: '/ajax/save_header_menu',
            type: 'post',
            data: {
                lang: lang,
                list: btoa(JSON.stringify(list))
            }
        });
    };

    $('#destiny').on('click', '.remove', function(){
        const item = $(this).closest('.item');
        $('#dest¡iny').remove(item);
        $('#origin').append(item);
        setMenuList();
    });

    $('#menu-manager .item').draggable({
        revert: "invalid",
        cursor: "move",
        zIndex: 100,
        connectToSortable: "#destiny"
    });

    $("#destiny").sortable({
        receive: setMenuList,
        update: setMenuList
    }).droppable({
        classes: {
            "ui-droppable-active": "ui-state-active",
            "ui-droppable-hover": "ui-state-hover"
        },
        drop: function( event, ui ) {
            $(ui.draggable).appendTo('#destiny').css({left:0,top:0}); //.draggable("option", "disabled", true);   
        }
    });
</script>