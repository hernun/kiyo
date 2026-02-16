<header class="front">
    <div class="header-container">
        <div id="branding">
            <div class="logo"><img src="<?php echo getAsset('images/logo-white.png')?>" /></div>
        </div>
        <nav class="desktop">
            <ul>
                <li><a href="/trabajos-realizado">Trabajos realizados</a></li>
                <li><a href="">Estudio velador</a></li>
                <li><a href="">Formación</a></li>
                <li><a href="">Servicios</a></li>
                <li><a href="">Kiyo música</a></li>
                <li><a href="">Bio</a></li>
                <li><a href="">Contacto</a></li>
                <li><?php echo getLaguageSelector()?></li>
            </ul>
        </nav>
        <nav class="tablet mobile">
            <div class="hamburger menu-trigger">
                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" focusable="false" aria-hidden="true" ><path d="M20 5H4a1 1 0 000 2h16a1 1 0 100-2Zm0 6H4a1 1 0 000 2h16a1 1 0 000-2Zm0 6H4a1 1 0 000 2h16a1 1 0 000-2Z"></path></svg>
            </div>
            <div class="d-flex justify-content-center align-items-center"><a class="lang-button" href="">ES</a></div>
            <div class="mobile-menu">
                <ul>
                    <li class="close"><div class="close-button">X</div></li>
                    <li><a href="/trabajos-realizado">Trabajos realizados</a></li>
                    <li><a href="">Estudio velador</a></li>
                    <li><a href="">Formación</a></li>
                    <li><a href="">Servicios</a></li>
                    <li><a href="">Kiyo música</a></li>
                    <li><a href="">Bio</a></li>
                    <li><a href="">Contacto</a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>
<script>
    const mobile = document.querySelector('.mobile-menu');
    const trigger = document.querySelector('.menu-trigger');
    const closeBtn = document.querySelector('.close-button');

    trigger.addEventListener('click', () => {
        mobile.classList.add('open');
    });

    closeBtn.addEventListener('click', () => {
        mobile.classList.remove('open');
    });

    mobile.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            mobile.classList.remove('open');
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            mobile.classList.remove('open');
        }
    });
</script>