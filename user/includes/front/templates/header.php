<header class="front">
    <div class="header-container">
        <div id="branding">
            <div class="logo"><a href="/"><img src="<?php echo getAsset('images/logo.png')?>" /></a></div>
        </div>
        <nav class="desktop">
            <?php include_template_part('menu-items')?>
        </nav>
        <nav class="tablet mobile">
            <div class="hamburger menu-trigger">
                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" focusable="false" aria-hidden="true" ><path d="M20 5H4a1 1 0 000 2h16a1 1 0 100-2Zm0 6H4a1 1 0 000 2h16a1 1 0 000-2Zm0 6H4a1 1 0 000 2h16a1 1 0 000-2Z"></path></svg>
            </div>
            <div class="d-flex justify-content-center align-items-center"><a class="lang-button" href="">ES</a></div>
            <div class="mobile-menu">
                <?php include_template_part('menu-items')?>
            </div>
        </nav>
    </div>
</header>

<script>
    const mobile = document.querySelector('.mobile-menu');
    const trigger = document.querySelector('.menu-trigger');
    const closeBtn = mobile.querySelector('.close-button');

    trigger.addEventListener('click', () => {
        mobile.classList.add('open');
    });

    if(closeBtn) {
        closeBtn.addEventListener('click', () => {
            mobile.classList.remove('open');
        });
    }

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