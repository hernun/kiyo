<?php
if(!nqv::userCan(['crud','config'])) {
    header('location:' . getAdminUrl());
    exit;
}

$homeWidgets = nqv::getConfig('home-widgets');

$widgets = array_filter(nqv::get('widgets'),function($w) use ($homeWidgets){
    return !in_array($w['id'],(array) $homeWidgets);
});

$indexes = [];
?>
<div class="container">
    <h1 class="my-5">Home Widgets</h1>
    <div id="menu-manager" class="d-flex flex-colun justify-content-between mb-5 gap-4">
        <div id="origin" class="border rounded p-3 w-100">
            <?php foreach($widgets as $k => $widget_array):?>
                <?php if(in_array($widget_array['id'],(array) $widgets)):?>
                    <?php $indexes[$widget_array['id']] =  $k?>
                    <?php continue?>
                <?php endif?>
                <?php $widget = new nqvWidgets($widget_array)?>
                <div class="item" data-value="<?php echo $widget->get('id')?>" data-index="<?php echo $k?>">
                    <div class="label"><?php echo ucfirst($widget->get_menu_label())?></div>
                    <div class="remove"><i class="bi bi-trash"></i></div>
                </div>
            <?php endforeach?>
        </div>
                
        <div id="destiny" class="border rounded p-3 w-100">
            <?php foreach($homeWidgets as $item_id):?>
                <?php $item = new nqvWidgets(['id' => $item_id])?> 
                <div class="item" data-value="<?php echo $item->get('id')?>" data-index="<?php echo @$indexes[$item_id]?>">
                    <div class="label"><?php echo ucfirst((string) $item->get_menu_label())?></div>
                    <div class="remove"><i class="bi bi-trash"></i></div>
                </div>
            <?php endforeach?>
        </div>
    </div>
</div>

<script type="text/javascript">
    function insertAtIndex(obj,i) {
        let ref;
        if(i === 0) {
            $("#origin").prepend(obj);        
            return;
        }
        if(!i) ref = $("#origin > div:last-child()");
        else ref = $("#origin > div:nth-child(" + (i) + ")");
        if(ref.length) ref.after(obj);
        else $("#origin").append(obj); 
    }

    $('#destiny').on({
        click:function(){
            const item = $(this).parents('.item');
            insertAtIndex(item,item.data('index'));
            setWigetsList();
        }
    },'.remove');

    const  setWigetsList = () => {
        const list = new Array();
        $('#destiny').find('.item').each(function(){
            list.push($(this).data('value'));
        });
        $.ajax({
            url: '/ajax/save_home_widgets_list',
            data: {list: btoa(JSON.stringify(list))},
            type: 'post'
        });
    }

    $('#menu-manager .item').draggable({
        revert: "invalid",
        cursor: "move",
        zIndex: 100,
        connectToSortable: "#destiny"
    });
    
    $( "#destiny" ).sortable({
        receive: function (event, ui) {      
             setWigetsList();
        }
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