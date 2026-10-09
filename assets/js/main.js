(function () {
  "use strict";

  var navbar = document.querySelector(".navbar-mi");
  if (navbar) {
    window.addEventListener("scroll", function () {
      navbar.classList.toggle("scrolled", window.scrollY > 12);
    }, { passive: true });
  }

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  var revealEls = document.querySelectorAll(".reveal");
  if (revealEls.length) {
    if (reduceMotion) {
      revealEls.forEach(function (el) { el.classList.add("in"); });
    } else {
      revealEls.forEach(function (el, i) {
        el.style.transitionDelay = Math.min(i % 3, 2) * 0.12 + "s";
      });
      var revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("in");
            revealObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.16, rootMargin: "0px 0px -8% 0px" });
      revealEls.forEach(function (el) { revealObserver.observe(el); });
    }
  }

  var counters = document.querySelectorAll("[data-count]");
  if (counters.length) {
    if (reduceMotion) {
      counters.forEach(function (el) { el.textContent = el.dataset.count; });
    } else {
      var countObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          countObserver.unobserve(entry.target);
          var start = performance.now();
          var duration = 900;
          (function tick(now) {
            var t = Math.min(1, (now - start) / duration);
            var eased = 1 - Math.pow(1 - t, 3);
            // dataset dibaca tiap frame agar nilai dari CMS yang datang belakangan ikut terpakai
            entry.target.textContent = Math.round(+entry.target.dataset.count * eased);
            if (t < 1) requestAnimationFrame(tick);
          })(start);
        });
      }, { threshold: 0.4 });
      counters.forEach(function (el) { countObserver.observe(el); });
    }
  }
})();
