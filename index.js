document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('nav-links');
  const logo = document.getElementById('logo');

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
});