document.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("theme") || "default";

  document.documentElement.setAttribute("data-theme", savedTheme);
  const sidebar = document.getElementById("sidebar");

  const content = document.querySelector(".main-content");

  const header = document.querySelector(".top-header");

  const collapsed = localStorage.getItem("sidebarCollapsed") === "true";

  if (collapsed) {
    sidebar?.classList.add("collapsed");

    content?.classList.add("expanded");

    header?.classList.add("expanded");
  }
});

function changeTheme(theme) {
  document.documentElement.setAttribute("data-theme", theme);

  localStorage.setItem("theme", theme);
}

function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");

  const content = document.querySelector(".main-content");

  const header = document.querySelector(".top-header");

  sidebar.classList.toggle("collapsed");

  content.classList.toggle("expanded");

  header.classList.toggle("expanded");

  const collapsed = sidebar.classList.contains("collapsed");

  localStorage.setItem("sidebarCollapsed", collapsed);
}
