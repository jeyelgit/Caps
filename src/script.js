document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu functionality
  const header = document.querySelector("header");
  const hamburgerBtn = document.querySelector("#hamburger-btn");
  const closeMenuBtn = document.querySelector("#close-menu-btn");

  hamburgerBtn.addEventListener("click", () => {
      header.classList.toggle("show-mobile-menu");
  });

  closeMenuBtn.addEventListener("click", () => {
      header.classList.remove("show-mobile-menu");
  });

  // Lightbox functionality
  const lightBox = document.querySelector(".products-preview");
  const images = document.querySelectorAll(".product .image-card");
  const closeBtn = lightBox?.querySelector(".fa-times");
  const showImg = lightBox?.querySelector(".preview img");

  if (lightBox) {
      images.forEach(image => {
          image.addEventListener("click", (e) => {
              e.stopPropagation(); // Prevent triggering parent handlers
              const preview = e.currentTarget.closest('.product');
              const previewContent = preview.querySelector('.image-card');
              
              // Update lightbox content
              showImg.src = previewContent.src || previewContent.querySelector('img').src;
              lightBox.style.display = "block";
              document.body.style.overflow = "hidden";
          });
      });

      // Close lightbox
      closeBtn?.addEventListener('click', () => {
          lightBox.style.display = "none";
          document.body.style.overflow = "visible";
      });

      // Close on outside click
      lightBox.addEventListener('click', (e) => {
          if (e.target === lightBox) {
              lightBox.style.display = "none";
              document.body.style.overflow = "visible";
          }
      });
  }

  // Side navigation toggle
  document.querySelector('.side-nav-toggle')?.addEventListener('click', function() {
      document.body.classList.toggle('side-nav-open');
  });
});