document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('nav-links');
  const logo = document.getElementById('logo');
  const uploadButton = document.getElementById('uploadButton');
  const pricingModal = document.getElementById('pricingModal');
  const closePricing = document.getElementById('closePricing');

  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('show');
    logo.classList.toggle('hide');

    // Switch between hamburger and close icon
    if (hamburger.innerHTML === '☰') {
        hamburger.innerHTML = '×'; // Change to close icon
        }
    else {
        hamburger.innerHTML = '☰'; // Change back to hamburger icon
    }
  });

  // Show pricing modal on Try It Now
  if (uploadButton && pricingModal) {
    uploadButton.addEventListener('click', (e) => {
      e.preventDefault();
      pricingModal.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  }
  // Close modal
  if (closePricing && pricingModal) {
    closePricing.addEventListener('click', () => {
      pricingModal.classList.remove('active');
      document.body.style.overflow = '';
    });
  }
  // Plan button actions
  document.querySelectorAll('.select-plan').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const plan = btn.getAttribute('data-plan');
      if (plan === 'user') {
        window.location.href = 'register.php'; // or your registration/login page
      } else if (plan === 'subscriber') {
        window.location.href = 'subscribe.php'; // or your subscribe page
      } else {
        window.location.href = 'mailto:support@example.com?subject=TRADE%20Constituent%20or%20LGU%20Inquiry';
      }
    });
  });
});