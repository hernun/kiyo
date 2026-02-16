<?php
$fields = $this->getDbTable()->getTableFields();
unset($fields['id']);
$mainFieldLabel = $this->getDbTable()->getMainFieldName();

if(is_array($mainFieldLabel)) {
    if(in_array('name',$mainFieldLabel) && in_array('lastname',$mainFieldLabel)) $mainFieldLabel = 'Nombre y apellido';
    else $mainFieldLabel = implode(' + ',array_map(function($value){
        return nqv::translate($value,'ES','label');
    },$mainFieldLabel));
}
$fiealdNames = [];
$exclude = [];
if(!empty($options)) {
    $include = (array) $options['include'];
    $exclude = (array) @$options['exclude'];
} else {
    $include = ['mainfield'];
}

usort($fields, function ($a, $b) use ($include) {
    return array_search($a['Field'], $include) <=> array_search($b['Field'], $include);
});
?>
<thead>
    <tr>
        <th>Id</th>
        <?php if(!in_array('mainfield',$exclude)):?>
            <th><?php echo ucwords((string) nqv::translate((string) $mainFieldLabel,'ES','label'))?></th>
        <?php endif?>
        <?php foreach($fields as $field): ?>
            <?php if(in_array($field['Field'],$exclude)) continue; ?>
            <?php if(!empty($include)):?>
                <?php if(!in_array($field['Field'],$include)) continue;?>
            <?php endif?>
            <?php if($field['Field'] === 'password') continue?>
            <th><?php echo ucfirst(nqv::translate($field['Field'],'ES','label'))?></th>
        <?php endforeach?>
        <th data-dt-order="disable">Acciones</th>
        <th data-dt-order="disable"></th>
        <th data-dt-order="disable"></th>
    </tr>
</thead>