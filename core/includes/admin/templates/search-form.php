<form id="nqv-search-form" class="search-form d-inline-block " action="/search" method="get">
    <div class="input-group input-group-sm mb-0">
        <input type="text" id="nqv-search-input" name="q" class="form-control" value="" aria-label="Campo de búsqueda" autocomplete="off" placeholder="<?php echo $this->getTablename()?>" />
        <span class="input-group-text">buscar</span>
	    <input type="hidden" name="search-type" class="input-search-type" value="<?php echo $this->getTablename()?>" />
    </div>
</form>
<?php $data = $this->getData()?>
<?php if(isValidJson($data)):?>
    <script type="text/javascript">
        $(function(){
            const searchFormTypeOptions = $('ul.search-options').find('li');
            var <?php echo $this->getSrcVarName()?> = JSON.parse('<?php echo $data?>');
            var nqvSerarchInput = document.getElementById('nqv-search-input');

            if(nqvSerarchInput) {
                new nqvAutocomplete(nqvSerarchInput, {
                    data: <?php echo $this->getSrcVarName()?>,
                    maximumItems: 500,
                    threshold: 1,
                    showValue: false,
                    onSelectItem: ({
                        label,
                        value,
                        type
                    }) => {
                        window.location = '?filter=' + btoa(JSON.stringify({id:value}))
                    }
                });
            }

            function validateNqvSearchForm(form) {
                const value = form.find('input[name=q]').val();
                return value != '';
            }
        })
    </script>
<?php endif?>