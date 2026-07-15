(function () {
  document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.splide').forEach((element) => {
      const carouselId = element.getAttribute('id');
      const perPage = 3;

      const splide = new Splide('#' + carouselId, {
        type: 'slide',
        rewind: true,
        pagination: false,
        arrows: true,
        perPage: perPage,
        gap: '32px',
        speed: 800,
        focus: 'left',
        breakpoints: {
          821: {
            perPage: 1,
            gap: 0,
          },
        },
      }).mount();

      const slides = splide.Components.Elements.slides;
      const paginationContainer = element.querySelector('.custom-pagination');

      // Clear existing dots
      paginationContainer.innerHTML = '';

      // Create a dot per slide
      slides.forEach((slide, index) => {
        const dot = document.createElement('button');
        dot.classList.add('custom-dot');
        dot.dataset.index = index;

        dot.addEventListener('click', () => splide.go(index));
        paginationContainer.appendChild(dot);
      });

      const dots = paginationContainer.querySelectorAll('.custom-dot');

      // Update visible slides classes
      function updateVisibleSlides() {
        slides.forEach((slide, i) => {
          slide.classList.toggle('is-active', i >= splide.index && i < splide.index + perPage);
        });
      }

      // Update active dot classes, including last-three logic
      function updateActiveDots() {
        const totalSlides = slides.length;
        const start = splide.index;

        // SPLIDE BREAKPOINT CHECK
        const isMobile = window.matchMedia('(max-width: 821px)').matches;

        if (isMobile) {
          // MOBILE: Only highlight exact active dot
          dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === start);
          });
          return;
        }

        // DESKTOP: MULTIPLE ACTIVE DOTS (perPage=3)
        const currentPerPage = splide.options.perPage;
        let highlightStart = start;

        // If near end of slider, clamp start
        if (start >= totalSlides - (currentPerPage - 1)) {
          highlightStart = Math.max(totalSlides - currentPerPage, 0);
        }

        dots.forEach((dot, i) => {
          dot.classList.toggle('is-active', i >= highlightStart && i < highlightStart + currentPerPage);
        });
      }


      // Initial update
      updateVisibleSlides();
      updateActiveDots();

      // Update on slide move
      splide.on('move', () => {
        updateVisibleSlides();
        updateActiveDots();
      });
    });

  });
})();
