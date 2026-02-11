
<a id="tulete"></a>
<div class="movieaspredictive-main-container">
    <?php
    $this->addAttribute(['onaddition'=>['funcname'=>$this->getName() . 'InputAddPoster','args'=>['containerId'=>'movies-poster-container']]]);
    $ws = nqvS::get('movies');
    $values = nqvDbObject::parseCrossPredictiveValues($ws, 'movies');
    $this->setOptions($values);
    $this->addCssClasses('predictive-input');
    include(ROOT_PATH . 'templates/forms/components/input-predictive.php');
    $ids = array_unique(array_filter((array) explode(',',(string) $this->getValue())));
    ?>
    <div id="movies-poster-container" class="movies-gallery movies-predictive-input">
        <?php foreach($ids as $movie_id):?>
            <?php $movie = new nqvMovies(['id' => $movie_id])?>
            <?php if(!$movie->exists()) continue?>
            <div class="movies-info-poster-wrap">
                <div class="movies-info-poster aspect poster">
                    <a class="over info ajax" href="/movies/info/<?php echo $movie->get('id')?>">
                        <i class="icon-info"></i>
                    </a>
                    <a class="over trash" href="#<?php echo $movie->get('id')?>">
                        <i class="icon-trash" data-element-id="<?php echo $movie->get('id')?>"></i>
                    </a>
                    <a class="over info ajax" href="/movies/info/<?php echo $movie->get('id')?>">
                        <?php echo $movie->getPosterTag('small') ?>
                    </a>
                </div>
            </div>
        <?php endforeach?>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        window["<?php echo $this->getName()?>InputAddPoster"] = function(movieData) {
            const atts = JSON.parse(window['<?php echo $this->getName()?>InputOnAdditionAtts']);
            const wrapper = $('<div class="movies-info-poster-wrap wait" />');
            const infoPoster = $('<div class="movies-info-poster aspect poster" />');
            const posterContainer = $('<div class="poster-container" />');
            const im = $('<img />');
            const anchor = $('<a href="/movies/edit/'+movieData['value']+'" />');

            const spinner = $('<div class="spinner" />');
            spinner.append('<div class="spinner-border" />');
            spinner.append('<div class="poster-mockup" />');
            infoPoster.append(spinner);

            const a = $('<a class="over info" />').html('<i class="icon-info"></i>');
            const b = $('<a class="over trash" />').html('<i class="icon-trash" data-element-id="'+movieData['value']+'"></i>');
            infoPoster.append(a);
            infoPoster.append(b);
            infoPoster.append(posterContainer);
            anchor.append(im);
            posterContainer.append(anchor);
            wrapper.append(infoPoster);

            if(!$('#' + atts['containerId']).length)  {
                const container = $('<div id="' + atts['containerId'] + '" class="movies-gallery" />');
                container.append(wrapper);
                const parent = $('#movies-input').parents('.input-container').last();
                parent.after(container);
            } else {
                $('#' + atts['containerId']).append(wrapper);
            }

            $(window).scrollTop($('#tulete').offset().top - 70);

            $.get('/ajax/get_movie_api_description/id/'+movieData['value']).done(function(response){
                const description = JSON.parse(response);
                wrapper.find('a').attr('href','/movies/info/' + description['id']).addClass('ajax');
                const posterUrl = description['poster'];
                $.get(posterUrl).done(function(){
                    wrapper.removeClass('wait');
                    wrapper.find('img').attr('id','poster-' + description['id']);
                    wrapper.find('img').attr('src',posterUrl);
                    spinner.fadeOut(function(){$(this).remove()})
                })
            });
        }
        
        $('#movies-poster-container').on({
            click: function(e) {
                e.preventDefault();
                <?php if ($this->getCalledClass() === 'nqvCaccounts'):?>
                    let txt = 'En vez de remover una película de la cuenta es preferible asignar esa película a otra cuenta, para no dejar películas huérfanas.';
                    txt += '\n\n¿Querés removerla de todas formas?';
                    if(!confirm(txt)) return false;
                <?php endif?>
                const id = $(this).attr('href').substr(1);
                const input = document.getElementById('<?php echo $this->getName()?>-input');
                input.suggestion = input.nextElementSibling;
                input.inform = input.suggestion.nextElementSibling;
                input.ids = input.inform.nextElementSibling;
                predictiveRemoveValue(e,input);
                $(this).parents('.movies-info-poster-wrap').fadeOut(function(){$(this).remove()});
                return false;
            }
        },'a.over.trash');
    })


</script>