<?php
if(!nqv::userCan(['crud','header-menu'])) {
    header('location:' . getAdminUrl());
    exit;
}

$menuArray = nqv::getConfig('header-menu');

$languages = getEnabledLangs(); // ['ES','EN',...]
$lang = $_SESSION['CURRENT_LANGUAGE']; // idioma activo por defecto

$allPages = [];
foreach($languages as $lng) $allPages[$lng] = nqv::get('pages',['lang'=>$lng]);

$selectedSlugs = $menuArray[$lang] ?? [];
?>
<div class="container">
    <h1 class="my-5">Menú del encabezado del front</h1>
    <ul class="nav nav-tabs mb-4" id="menuLangTabs">
        <?php foreach($languages as $i => $lng): ?>
            <li class="nav-item">
                <button 
                    class="nav-link <?php echo $lng === $_SESSION['CURRENT_LANGUAGE'] ? 'active' : '' ?>" 
                    data-lang="<?php echo $lng ?>"
                    type="button">
                    <?php echo $lng ?>
                </button>
            </li>
        <?php endforeach ?>
    </ul>
    <div id="menu-manager" data-lang="<?php echo $lang ?>" class="d-flex gap-4">
        <!-- ORIGIN -->
        <div id="origin" class="border rounded p-3 w-100"></div>

        <!-- DESTINY -->
        <div id="destiny" class="border rounded p-3 w-100"></div>
    </div>
</div>
<script>
    const MENU_DATA = <?php echo json_encode($menuArray) ?>;
    const DB_PAGES = <?php echo json_encode($allPages) ?>;
    
    const createItem = (page) => {
        return `
            <div class="item" data-value="${page.slug}">
                <div class="label">${page.title}</div>
                <div class="remove"><i class="bi bi-trash"></i></div>
            </div>
        `;
    };

    $('#menuLangTabs').on('click', '.nav-link', function(){
        $('#menuLangTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        const lang = $(this).data('lang');
        renderMenu(lang);
    });
    
    const activateDragAndDrop = () => {
        $('.item').draggable({
            revert: "invalid",
            cursor: "move",
            zIndex: 100,
            connectToSortable: "#destiny"
        });
    };

    const renderMenu = (lang) => {
        const selected = MENU_DATA[lang] || [];
        const pages = DB_PAGES[lang] || [];
        const indexed = {};
        pages.forEach(p => indexed[p.slug] = p);

        const destiny = [];
        const origin = [];

        selected.forEach(slug => {
            if(indexed[slug]) destiny.push(indexed[slug]);
        });

        pages.forEach(page => {
            if(!selected.includes(page.slug)) origin.push(page);
        });

        $('#origin').empty();
        $('#destiny').empty();

        origin.forEach(page => {
            $('#origin').append(createItem(page));
        });

        destiny.forEach(page => {
            $('#destiny').append(createItem(page));
        });

        $('#menu-manager').data('lang', lang);
        activateDragAndDrop();
    };

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
        
        MENU_DATA[lang] = list;
    };

    $('#destiny').on('click', '.remove', function(){
        const item = $(this).closest('.item');
        $('#destiny').remove(item);
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

    $(function(){
        renderMenu($('#menu-manager').data('lang'));
    });
</script>