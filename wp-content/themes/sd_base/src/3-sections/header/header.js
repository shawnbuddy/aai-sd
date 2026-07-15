window.addEventListener('DOMContentLoaded', () => {
  document.getElementById('js-toggle-menu').addEventListener('click', function () {
    this.classList.toggle('open');
    document.querySelector('.header').classList.toggle('is-open');
    document.body.classList.toggle('no-scroll');
  });

  // Manual active link handling for site drawer
  const siteDrawerLinks = document.querySelectorAll('.site-drawer a');
  const currentUrl = window.location.href;

  // get last segment of currentUrl to handle trailing slashes
  const url = new URL(currentUrl);
  let pathname = url.pathname;
  if (pathname.endsWith('/')) {
    pathname = pathname.slice(0, -1);
  }
  const last_segment =  pathname;

  siteDrawerLinks.forEach(link => {
    //get last segment of link href to handle trailing slashes
    let linkPath = link.getAttribute('href');
    if (linkPath.endsWith('/')) {
      linkPath = linkPath.slice(0, -1);
    }
    if (linkPath === last_segment) {
      link.classList.add('active');
    }
  });
});
