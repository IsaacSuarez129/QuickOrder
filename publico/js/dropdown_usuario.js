document.addEventListener("DOMContentLoaded", () => {
  const btnDropdown = document.getElementById("btnDropdownUsuario");
  const menuUsuario = document.getElementById("menuUsuario");
  const btnLogout = document.getElementById("btnCerrarSesion");
  const contenedorDropdown = btnDropdown
    ? btnDropdown.closest(".qo-user-dropdown")
    : null;

  // 1. Confirmar cierre de sesión
  if (btnLogout) {
    btnLogout.addEventListener("click", (e) => {
      const confirmar = confirm("¿Estás seguro de que deseas cerrar sesión?");
      if (!confirmar) {
        e.preventDefault();
      }
    });
  }

  // 2. Abrir / cerrar desplegable
  if (btnDropdown && menuUsuario) {
    btnDropdown.addEventListener("click", (e) => {
      e.stopPropagation();
      const estaOculto = menuUsuario.hidden;
      menuUsuario.hidden = !estaOculto;
      if (contenedorDropdown) {
        contenedorDropdown.classList.toggle("abierto", estaOculto);
      }
      btnDropdown.setAttribute("aria-expanded", String(estaOculto));
    });

    // 3. Cerrar al hacer clic fuera del desplegable
    document.addEventListener("click", (e) => {
      if (!menuUsuario.hidden && !menuUsuario.contains(e.target)) {
        menuUsuario.hidden = true;
        if (contenedorDropdown) {
          contenedorDropdown.classList.remove("abierto");
        }
        btnDropdown.setAttribute("aria-expanded", "false");
      }
    });
  }
});
