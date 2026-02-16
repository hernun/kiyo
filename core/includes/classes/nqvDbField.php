<?php

class nqvDbField {
    protected $isnull = false;
    protected $canBeNull = false;
    protected $name = '';
    protected $value = null;
    protected $default = null;
    protected $disabled = '';
    protected $type = '';
    protected $itype = '';
    protected $htmlInputType = '';
    protected $fks = [];
    protected $label = '';
    protected $cssClasses = [];
    protected $typeLetter = 's';
    protected $autocomplete = 'off';
    protected $multiple = '';
    protected $small = '';
    protected $htmlData = [];
    protected $props = [];
    protected $scripts = [];
    protected $options = [];
    protected $isHtmlTypeForced = false;
    protected $hidelabel = false;
    protected $attributes = [];
    protected $placeholder = false;
    protected $crosser;
    protected $selectOptions;
    protected $newPassType = 'auto';
    protected string $tablename = '';
    protected $tableobject;

    public function __construct(array $input,  $tablename) {
        $this->name = $input['Field'];
        $this->default = $input['Default'];
        $this->label = $this->name;
        $this->itype = $input['Type'];
        $this->parseNull($input['Null']);
        $this->parseType();
        $this->parseHtmlInputType();
        $this->parseValue(@$input['Value']);
        $this->getTableObject($tablename);
    }

    protected function parseValue($value = null): void {
        if (empty($value)) $this->setValue($this->getDefault());
        elseif($this->isDateStyleInputType()) $this->setValue($this->parseDate($value));
        else $this->setValue($value);
    }

    protected function parseNull(string $canBeNull): void {
        $this->canBeNull = ($this->name === 'id' || $canBeNull === 'NO') ? false : true;
    }

    protected function parseType(): void {
        if (strpos($this->itype, 'int') !== false) {
            $this->typeLetter = 'i';
            $this->type = 'integer';
        } elseif (strpos($this->itype, 'float') !== false) {
            $this->typeLetter = 'd';
            $this->type = 'float';
        } else {
            $this->typeLetter = 's';
            $this->type = 'string';
        }
    }

    public function getTypeLetter(): string {
        return $this->typeLetter;
    }

    public function isNumeric(): bool {
        return $this->getTypeLetter() === 'i' || $this->getTypeLetter() === 'd';
    }

    protected function parseHtmlInputType(): void {
        if ($this->is('enum')) $this->setHtmlInputType('select');
        elseif ($this->name === 'id') $this->setHtmlInputType('hidden');
        elseif ($this->name === 'email') $this->setHtmlInputType('email');
        elseif ($this->name === 'birthdate' || $this->name === 'date' || $this->itype === 'datetime' || $this->itype === 'date') $this->setHtmlInputType('date');
        elseif ($this->type === 'integer') $this->setHtmlInputType('number');
        elseif ($this->type === 'string' || $this->type === 'json') {
            if (strpos($this->itype, 'varchar') === 0) {
                if (getWithinParenthesis($this->itype) > 255) $this->setHtmlInputType('textarea');
                else $this->setHtmlInputType('text');
            } elseif (strpos($this->itype, 'mediumtext') === 0) {
                $this->setHtmlInputType('textarea');
            } elseif (strtoupper($this->itype) === 'TEXT') {
                $this->setHtmlInputType('textarea');
            } else {
                $this->setHtmlInputType('text');
            }
        } else my_print([$this->name, $this->type]);
        if ($this->getHtmlInputType() === 'select multiple') $this->setMultiple(true);
    }

    public function is(string $itype): bool {
        return strpos($this->getItype(), $itype) === 0;
    }

    public function getItype() {
        return $this->itype;
    }

    public function getHtmlInputType(): string {
        return $this->htmlInputType;
    }

    public function setHtmlInputType(string $htmlInputType, $forced=false): nqvDbField {
        $this->isHtmlTypeForced = $forced;
        $this->htmlInputType = $htmlInputType;
        return $this;
    }

    public function getCrosser() {
        return $this->crosser ? $this->crosser:$this->getHtmlInputName();
    }

    public function setCrosser($crosser): nqvDbField {
        $this->crosser = $crosser;
        return $this;
    }

    protected function isTextStyleInputType() {
        $ts = ['hidden', 'text', 'email'];
        return in_array($this->getHtmlInputType(), $ts);
    }

    public function setAutocompleteList(string $json): void{
        $this->setProp('data-json',$json);
    }

    protected function isSelectStyleInputType() {
        if(!$this->isHtmlTypeForced && strrpos($this->name,'_id')) return true;
        $ts = ['select', 'select multiple'];
        return in_array($this->getHtmlInputType(), $ts);
    }

    protected function isNumericStyleInputType() {
        $ts = ['number'];
        return in_array($this->getHtmlInputType(), $ts);
    }

    public function isDateStyleInputType() {
        $ts = ['date','datetime','timestamp'];
        return in_array(strtolower($this->getHtmlInputType()), $ts);
    }

    protected function isTelStyleInputType() {
        $ts = ['tel', 'phone', 'cellphone'];
        return in_array($this->getName(), $ts);
    }

    protected function isStandardInputHtmlType() {
        return $this->isNumericStyleInputType() ||
            $this->isTextStyleInputType();
    }

    public function setProp(string $name, $value) {
        $this->props[$name] = $value;
    }

    public function getProp(string $name) {
        return @$this->props[$name];
    }

    public function getProps(?string $name = null) {
        if(!empty($name)) return $this->getProp($name);
        return $this->props;
    }

    public function showLabel() {
        $this->hidelabel = false;
        return $this;
    }

    public function hideLabel() {
        $this->hidelabel = true;
        return $this;
    }

    public function labelMustBeShowed() {
        return !$this->hidelabel;
    }

    public function readonly($val=true) {
        if($val) $this->setProp('readonly', 'readonly');
        else $this->setProp('readonly', false);
        return $this;
    }

    public function setNewPassType($type) {
        $this->newPassType = $type;
        return $this;
    }

    public function getNewPassType() {
        return $this->newPassType;
    }

    public function getComponent($name) {
        ob_start();
        include(FORMS_PATH . 'components/' . $name . '.php');
        return ob_get_clean();
    }

    public function getHtmlInput(): string {
        try {
            $o = '';
            if ($this->isSelectStyleInputType()) {
                $this->addCssClasses('form-select');
                $this->parseHtmlOptions();
                $o .= $this->getComponent('input-select');
            }elseif ($this->getItype() === 'tinyint(1)') {
                $this->hideLabel();
                $o .= $this->getComponent('input-boolean');
            }elseif ($this->isTelStyleInputType()) {
                $this->setHtmlInputType('tel');
                $this->setProp('pattern', '[0-9]{8,}');
                $o .= $this->getComponent('input-standard');
            } elseif ($this->getHtmlInputType() === 'tags') {
                $o .= $this->getComponent('input-tags');
            } elseif ($this->getHtmlInputType() === 'creatorsTags') {
                $o .= $this->getComponent('input-creatorsTags');
            } elseif ($this->getHtmlInputType() === 'actorsTags') {
                $o .= $this->getComponent('input-actorsTags');
            } elseif ($this->getHtmlInputType() === 'textarea' || $this->getName() === 'observations') {
                $o .= $this->getComponent('input-textarea');
            } elseif ($this->getHtmlInputType() === 'file' || $this->getHtmlInputType() === 'multiple-file') {
                $o .= $this->getComponent('input-file');
            } elseif ($this->getHtmlInputType() === 'duration') {
                $o .= $this->getComponent('input-duration');
            } elseif ($this->getName() === 'password') {
                $o .= $this->getComponent('input-password');
            } elseif ($this->isStandardInputHtmlType()) {
                $o .= $this->getComponent('input-standard');
            } elseif ($this->isDateStyleInputType()) {
                $this->addCssClasses('datepicker');
                $value = $this->getValue();
                if(empty($value)) $value = null;
                elseif(is_numeric($value)) $value = date('d/m/Y', $value);
                $this->value = $value;
                if ($this->getName() === 'birthdate') {
                    $this->setHtmlInputType('text');
                    $this->setCssClasses(['cleave']);
                    $this->setProp('data-maxdate', '-8y');
                    $this->setProp('data-defaultdate', '-35y');
                    $this->setProp('placeholder','Ingresá la fecha: DD/MM/YYYY');
                    $o .= $this->getComponent('input-standard');
                } else {
                    $o .= $this->getComponent('input-date');
                }
            } elseif ($this->getHtmlInputType() === 'none') {
                $o .= $this->getComponent('input-none');
            } elseif ($this->getHtmlInputType() === 'predictive') {
                $this->addCssClasses('predictive-input');
                $o .= $this->getComponent('input-predictive');
            } else {
                $type = $this->getHtmlInputType();
                $component = 'input-' . $type . '.php';
                $this->addCssClasses($type . '-input');
                ob_start();
                include(FORMS_PATH . 'components/' . $component);
                $o .= ob_get_clean();
            }
            if($this->labelMustBeShowed()) {
                $o = $this->getHtmlLabel() . $o;
            }
            return $o;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function parseDate($value, $toLatin = false){
        if(!$value && !$this->getDefault()) return null;
        try {
            // 2024-09-13 03:59:01
            if(!is_numeric($value)) $value = strtotime($value);
            $value = date('Y-m-d h:i:s',$value);
            $date = new DateTimeImmutable($value);
        } catch (Exception $e) {
            throw new Exception($e->getFile() . ' ' . $e->getLine() . ': ' . $e->getMessage());
        }
        if($toLatin) $value = $date->format('d/m/Y');
        else $value = $date->format('Y-m-d');
        return $value;
    }

    public function setHtmlData(string $k, string $value): nqvDbField {
        $this->htmlData[$k] = $value;
        return $this;
    }

    public function getHtmlData(?string $k = null) {
        if ($k) $o = $this->htmlData[$k];
        else $o = $this->htmlData;
        return $o;
    }

    public function disable(): nqvDbField {
        $this->disabled = 'disabled';
        return $this;
    }

    public function enable(): nqvDbField {
        $this->disabled = '';
        return $this;
    }

    public function getDisabled() {
        return $this->disabled;
    }

    public function getRequired($canBeNull = false) {
        if($this->canBeNull() || $canBeNull) {
            return '';
        } else {
            $this->addCssClasses('required');
            return 'required="required" ';
        }
    }

    public function setAutoComplete(string $value) {
        $this->autocomplete = $value;
        return $this;
    }

    public function getAutoComplete() {
        return $this->autocomplete;
    }

    public function setMultiple(bool $m): void {
        $this->multiple = $m ? 'multiple' : '';
    }

    public function getMultiple(): string {
        return $this->multiple;
    }

    public function isMultiple(): bool {
        return $this->multiple === 'multiple';
    }

    public function getHtmlName(?string $name = '') {
        if (empty($name)) $name = $this->getName();
        if ($this->isMultiple()) {
            $name .= '[]';
        }
        return $name;
    }

    public function addSmall(string $text): nqvDbField {
        $this->small = $text;
        return $this;
    }

    public function getSmall(): string {
        return $this->small;
    }

    public function getHtmlInputName($force=[]){
        return empty($force['name']) ? $this->getName() : $force['name'];
    }

    public function getHtmlInputId($force=[]){
        return $this->getHtmlInputName($force) . '-input';
    }

    protected function showProps(?array $force = []) {
        $name = empty($force['name']) ? $this->getName() : $force['name'];
        $autocomplete = empty($force['autocomplete']) ? $this->getAutoComplete() : $force['autocomplete'];
        $req = $this->getRequired(@$force['canBeNull']) . ' ';
        $o = 'id="' . $this->getHtmlInputId($force) . '" ';
        $o .= 'name="' . $this->getHtmlName($name) . '" ';
        $this->setCssClasses(['form-control']);
        $o .= 'class="' . $this->getCssClassesString() . '" ';
        $o .= 'aria-describedby="' . $name . '-help" ';
        $o .= 'autocomplete="' . $autocomplete . '" ';
        $o .= 'aria-label="' . $name . '" ';
        foreach ($this->getProps() as $k => $v) {
            $o .= $k . '="' . $v . '" ';
        }
        foreach ($this->getHtmlData() as $k => $v) {
            $o .= 'data-' . $k . '="' . $v . '" ';
        }
        $o .= $req;
        $o .= $this->getDisabled();
        return $o;
    }

    protected function showStandardInputProps(?array $force = []) {
        $value = empty($force['value']) ? $this->getValue() : $force['value'];
        $type = empty($force['type']) ? $this->getHtmlInputType() : $force['type'];
        $o = $this->showProps($force) . ' ';
        $o .= 'type="' . $type . '" ';
        $o .= 'value="' . htmlspecialchars((string) $value) . '" ';
        $o .= 'data-label="' . $this->getLabel() . '" ';
        $o .= $this->getMultiple();
        return $o;
    }

    public function getHtmlLabel(): string {
        if ($this->isHidden()) return '';
        $asterix = $this->canBeNull() ? '' : '<span class="required-mark">*</span>';
        $label = '<label for="' . $this->getName() . '-input">';
        $label .= nqv::translate($this->getLabel(), 'ES', 'label') . $asterix;
        $label .= '</label>';
        return $label;
    }

    public function setOptions(?array $options = []): nqvDbField {
        $this->options = $options;
        return $this;
    }

    public function getOptions() {
        return $this->options;
    }

    public function setPlaceholder($text=null) {
        if($text) $this->placeholder = $text;
        else $this->placeholder = 'Selecciona una opción';
    }

    public function getPlaceholder() {
        return $this->placeholder;
    }

    public function hasPlaceholder() {
        return !empty($this->placeholder);
    }

    protected function parseHtmlOptions(): void {
        try {
            $ops = '';
            if(!empty($this->options)) {
                $values = $this->options;
            } elseif (!empty($classValue)) {
                $values = $classValue;
            } elseif ($this->is('enum')) {
                preg_match('#\((.*?)\)#', $this->getItype(), $match);
                $values = explode(',', removeQutes($match[1]));
                $values = array_combine($values, $values);
            } else {
                $values = $this->getFks($this->getName());
            }
            if($this->canBeNull()) $ops .= '<option value=""></option>';
            foreach ($values as $v => $k) {
                if(is_array($k) && isset($k['label'])) {
                    $v = $k['value'];
                    $k = $k['label'];
                }
                $value = $this->getValue() ? $this->getValue():$this->getDefault();
                $currentVal = array_unique(array_filter(explode(',', (string) $value)));
                $condition = in_array($v, $currentVal);
                $s = $condition ? 'selected="selected" checked' : '';
                $ops .= '<option value="' . $v . '" ' . $s . '>' . $k . '</option>';
            }
            $this->selectOptions = ['count' => count($values), 'string' => $ops];
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function setScript(string $script) {
        $this->scripts[] = $script;
    }

    public function getScripts() {
        return $this->scripts;
    }

    public function getFks(string $k = ''): ?array {
        $values = [];
        $parts = array_values(array_filter(explode('_',$k)));
        $sufix = array_pop($parts);
        $tablename = implode('_',$parts);
        if($sufix === 'id' && nqvDB::isTable($tablename)) {
            $sql = 'SELECT id as value, name as label FROM ' . $tablename . ' ORDER by name ASC, id ASC';
            $stmt = nqvDB::prepare($sql);
            $values = nqvDB::parseSelect($stmt);
        }
        return $values;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getDefault() {
        if($this->default === 'CURRENT_TIMESTAMP') $this->default = date('Y-m-d h:i:s',time());
        return $this->default;
    }

    public function setDefault($value): nqvDbField {
        $v = $this->getValue();
        if (empty($v)) $this->setValue($value);
        $this->default = $value;
        return $this;
    }

    public function getValue($default=null) {
        if($this->isDateStyleInputType()) {
            if($default) $default = $this->parseDate($default);
            $value = $this->value ? $this->value:$default;
            $this->value = $this->parseDate($value, true);
        }
        return $this->value ? $this->value:$default;
    }

    public function canBeNull(): bool {
        return $this->canBeNull;
    }

    public function setCanBeNull(bool $value): void {
        $this->canBeNull = $value;
    }

    public function getName() {
        return strtolower($this->name);
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function setLabel(string $label): nqvDbField {
        $this->label = $label;
        return $this;
    }

    public function setValue($value): nqvDbField {
        if ($this->getHtmlInputType() === 'json') $this->value = json_encode($value);
        elseif (is_null($value) && $this->canBeNull()) $this->value = null;
        else {
            if (is_array($value)) $value = implode(',', $value);
            settype($value, $this->getType());
            $this->value = $value;
        }
        return $this;
    }

    public function checkValue(): bool {
        $value = $this->getValue();
        $iType = gettype($value);
        if (is_null($value) && $this->canBeNull()) return true;
        elseif (empty($value) && !$this->canBeNull()) {
            nqvNotifications::add('El campo ' . $this->getName() . ' no puede estar vacío', 'error');
            return false;
        } elseif ($iType !== $this->getType()) {
            nqvNotifications::add('El campo ' . $this->getName() . ' es de tipo ' . $this->getType() . ' pero recibió un valor de tipo ' . $iType, 'error');
            return false;
        }
        return true;
    }

    public function isHidden() {
        return $this->getType() === 'hidden' || $this->getHtmlInputType() === 'hidden';
    }

    protected function getTableObject() {
        if(!nqvDB::isTable($this->tablename)) return null;
        $classname = 'nqv' . ucfirst($this->tablename);
        if(!class_exists($classname)) return null;
        $this->tableobject = new ReflectionClass($classname);
        return $this->tableobject;
    }

    public function getHumanValue() {
        $parentMethod = 'getHuman' . ucfirst($this->name);
        $value = $this->getValue();
         if($this->tableobject && $this->tableobject->hasMethod($parentMethod)) {
            $method = $this->tableobject->getMethod($parentMethod);
            return $method->invoke($value);
        }
        $fks = $this->getFks($this->name);
        if ($fks) {
            if($this->isSelectStyleInputType()) {
                $fk = array_filter($fks,function($a) use ($value) {
                    return intval($a['value']) === intval($value);
                });
                return nqv::translate(array_values($fk)[0]['label'],'ES');
            } else return nqv::translate($fks[$this->getValue()]);
        } else return empty($value) ? $value:nqv::translate($value, 'ES');
    }

    public function setCssClasses($classes): nqvDbField {
        $this->cssClasses = array_merge($this->getCssClasses(), (array)$classes);
        return $this;
    }

    public function addCssClasses($classes): nqvDbField {
        if(is_array($classes)) $classes = implode(' ', $classes);
        $this->cssClasses[] = $classes;
        return $this;
    }

    public function getCssClasses() {
        return array_filter(array_unique($this->cssClasses));
    }

    public function getCssClassesString() {
        return implode(' ', $this->getCssClasses());
    }

    public function __toString() {
        return $this->getHtmlInput();
    }

    public function addAttribute($atts){
        $this->attributes = array_merge($this->attributes, $atts);
    }
}
