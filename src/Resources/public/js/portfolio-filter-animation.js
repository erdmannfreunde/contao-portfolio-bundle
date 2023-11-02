function animatePortfolioItems(filters, portfolioItems, duration) {
    let itemCategory;
    let animationDuration = duration || 500;
    let activeFilter;

    filters.forEach((filter) => {
        
        if(filter.classList.contains('active')) {
            activeFilter = filter;
        }

        filter.addEventListener('click', (e) => {
            e.preventDefault();
            
            if(activeFilter) {
                activeFilter.classList.remove('active');
            }

            e.target.classList.add('active');
            activeFilter = e.target;

            let filterCategory = activeFilter.dataset.category;

            if (filterCategory == 'all') {
                portfolioItems.forEach((item) => {
                    item.classList.remove('portfolio-visible');

                    fadeIn(item, animationDuration);
                });
            } else {
                for (const item of portfolioItems) {
                    itemCategory = item.dataset.category;

                    if (itemCategory.contains(filterCategory)) {
                        fadeOut(item, animationDuration).then(function() {
                            fadeIn(item, animationDuration);
                        });
                        
                    } else {
                        fadeOut(item, animationDuration);
                    }
                }
            }
        });
    });


    function defaultFadeConfig() {
    return {
        easing: 'linear', 
        iterations: 1, 
        direction: 'normal', 
        fill: 'forwards',
        delay: 0,
        endDelay: 0
        }
    }

    function fadeOut(el, animationDuration, config = defaultFadeConfig()) {

        return new Promise((resolve, reject) => {
            const animation = el.animate([
                { opacity: '1' },
                { opacity: '0', offset: 0.5 },
                { opacity: '0', offset: 1 }
            ], {duration: animationDuration, ...config});
            
            animation.onfinish = () => {
                el.style.display = 'none';
                el.classList.remove('portfolio-visible');
                el.classList.add('portfolio-hidden');
                resolve();
            }
        })
    }

    function fadeIn(el, animationDuration, config = defaultFadeConfig()) {

        return new Promise((resolve) => {
            el.style.display = 'block';

            const animation = el.animate([
                { opacity: '0' },
                { opacity: '0.5', offset: 0.5 },
                { opacity: '1', offset: 1 }
            ], {duration: animationDuration, ...config});
            

            animation.onfinish = () => {
                el.classList.add('portfolio-visible');
                el.classList.remove('portfolio-hidden');
                resolve();
            }
        });
    }
}
