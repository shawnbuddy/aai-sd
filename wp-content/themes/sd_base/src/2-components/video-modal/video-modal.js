window.addEventListener('DOMContentLoaded', () => {
  const videoMaxWidth = 700;
  const videoModalTriggers = document.querySelectorAll('.remote-video-button');

  const normalizeVimeoUrl = (url) => {
    try {
      const u = new URL(url);

      // Match /video/{id}/{hash}
      const match = u.pathname.match(/^\/video\/(\d+)\/([a-zA-Z0-9]+)$/);
      if (!match) return url; // Not a special Vimeo format—return as-is

      const videoId = match[1];
      const hash = match[2];

      // Build clean Vimeo embed URL
      return `https://player.vimeo.com/video/${videoId}?h=${hash}`;
    } catch (e) {
      return url; // Fallback safety
    }
  };

  const sizeVideoFrame = (iframe, height, width) => {
    const w = Math.min(videoMaxWidth, window.innerWidth);
    let h = height * (w / width);
    const winHeight = window.innerHeight;

    if (h > (winHeight - 64)) {
      h = winHeight - 64;
      w = width * (h / height);
    }
  };

  const focusTrap = (modal) => {
    const focusableItems = modal.querySelectorAll('button, input, select, textarea, a[href]');
    const firstFocusable = focusableItems[0];
    const lastFocusable = focusableItems[focusableItems.length - 1];
    firstFocusable.focus();

    lastFocusable.addEventListener('focus', (event) => {
      event.preventDefault();
      firstFocusable.focus();
    });

    modal.addEventListener('keydown', (e) => {
      if (e.key === 'Tab') {
        if (e.shiftKey) {
          if (document.activeElement === firstFocusable) {
            e.preventDefault();
            lastFocusable.focus();
          }
        } else if (document.activeElement === lastFocusable) {
          e.preventDefault();
          firstFocusable.focus();
        }
      }
    });
  };

  const buttonLoadVideo = (btn) => {
    let src = btn.getAttribute('data-src');
    src = normalizeVimeoUrl(src); // ← Add this
    const title = btn.getAttribute('data-title');
    const width = 640;
    const height = 360;
    const toOverlay = btn.getAttribute('data-video-overlay') === 'true';

    const iframe = document.createElement('iframe');
    iframe.setAttribute('allowfullscreen', '');
    iframe.setAttribute('height', height);
    iframe.setAttribute('width', width);
    iframe.setAttribute('allow', 'autoplay ; fullscreen;');
    const symbol = src.includes('?') ? '&' : '?';
    iframe.setAttribute('src', `${src}${symbol}autoplay=1`);
    iframe.setAttribute('title', title);

    if (toOverlay) {
      const overlay = document.createElement('div');
      overlay.classList.add('video-player-overlay');

      const videoDismiss = document.createElement('div');
      videoDismiss.classList.add('video-dismiss');

      const videoWrap = document.createElement('div');
      videoWrap.classList.add('video-wrap');
      videoWrap.id = 'video-modal';

      const closeVideo = document.createElement('button');
      closeVideo.classList.add('close-video');
      closeVideo.innerHTML = '<span class="sr-only">Close</span><i class="fa-solid fa-xmark"></i>';

      const focusEnd = document.createElement('a');
      focusEnd.href = '#';

      videoDismiss.addEventListener('click', () => {
        overlay.remove();
        document.getElementsByTagName('body')[0].classList.remove('no-scroll');
      });

      closeVideo.addEventListener('click', () => {
        overlay.remove();
        document.getElementsByTagName('body')[0].classList.remove('no-scroll');
      });

      videoWrap.appendChild(closeVideo);
      videoWrap.appendChild(iframe);
      videoWrap.appendChild(focusEnd);
      overlay.appendChild(videoDismiss);
      overlay.appendChild(videoWrap);

      if (window.innerWidth < videoMaxWidth) {
        videoWrap.classList.add('full-width');
      }

      document.getElementsByTagName('body')[0].appendChild(overlay);
      document.getElementsByTagName('body')[0].classList.add('no-scroll');
      sizeVideoFrame(iframe, height, width);
      focusTrap(videoWrap);
    } else {
      btn.parentNode.replaceChild(iframe, btn);
    }
  };

  videoModalTriggers.forEach((trigger) => {
    if (!trigger.dataset.openModal) {
      trigger.dataset.openModal = true;
      trigger.addEventListener('click', () => {
        buttonLoadVideo(trigger);
      });
    }
  });
});