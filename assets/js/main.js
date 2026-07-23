const LifeFlow = (() => {
  const api = async (path) => {
    const response = await fetch(path);
    if (!response.ok) {
      throw new Error('Unable to load data');
    }

    return response.json();
  };

  const toast = (icon, title) =>
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon,
      title,
      showConfirmButton: false,
      timer: 2800,
      timerProgressBar: true,
    });

  const initNav = () => {
    const nav = document.querySelector('.lifeflow-nav');

    if (!nav) {
      return;
    }

    const update = () => nav.classList.toggle('scrolled', scrollY > 12);

    update();
    addEventListener('scroll', update, { passive: true });
  };

  const initForms = () => {
    document.querySelectorAll('form.needs-validation').forEach((form) => {
      form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      });
    });
  };

  return { api, toast, initNav, initForms };
})();

document.addEventListener('DOMContentLoaded', () => {
  LifeFlow.initNav();
  LifeFlow.initForms();
});
