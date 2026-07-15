// =========================================
// RECORDS & REPORTS CENTER
// =========================================

let currentPage = 1;
let searchTimer = null;

// =========================================
// INITIALIZE
// =========================================

document.addEventListener("DOMContentLoaded", () => {
  loadHistory();

  initializeEvents();
});

// =========================================
// EVENTS
// =========================================

function initializeEvents() {
  document.getElementById("applyFilter").addEventListener("click", () => {
    currentPage = 1;
    loadHistory();
  });

  document
    .getElementById("resetFilter")
    .addEventListener("click", resetFilters);

  document.getElementById("searchBox").addEventListener("keyup", () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
      currentPage = 1;
      loadHistory();
    }, 400);
  });
}

// =========================================
// RESET FILTERS
// =========================================

function resetFilters() {
  document.getElementById("searchBox").value = "";

  document.getElementById("dateFrom").value = "";

  document.getElementById("dateTo").value = "";

  document.getElementById("statusFilter").value = "";

  document.getElementById("receptionFilter").value = "";

  document.getElementById("queueType").value = "";

  document.getElementById("sortBy").value = "desc";

  currentPage = 1;

  loadHistory();
}

// =========================================
// LOAD HISTORY
// =========================================

function loadHistory() {
  const params = new URLSearchParams({
    page: currentPage,

    search: document.getElementById("searchBox").value,

    dateFrom: document.getElementById("dateFrom").value,

    dateTo: document.getElementById("dateTo").value,

    status: document.getElementById("statusFilter").value,

    reception: document.getElementById("receptionFilter").value,

    queueType: document.getElementById("queueType").value,

    sort: document.getElementById("sortBy").value,
  });

  showLoading();

  fetch("api/get_history.php?" + params)
    .then((response) => response.json())

    .then((data) => {
      renderSummary(data.summary);

      renderTable(data.records);

      renderPagination(data.pagination);

      populateReceptionFilter(data.receptions);
    })

    .catch((error) => {
      console.error(error);

      document.getElementById("historyTable").innerHTML = `

                <tr>

                    <td colspan="9" class="text-center text-danger p-5">

                        Unable to load history.

                    </td>

                </tr>

            `;
    });
}

// =========================================
// LOADING
// =========================================

function showLoading() {
  document.getElementById("historyTable").innerHTML = `

        <tr>

            <td colspan="9" class="loading-row text-center">

                <div class="spinner-border text-primary mb-3"></div>

                <br>

                Loading queue records...

            </td>

        </tr>

    `;
}

// =========================================
// SUMMARY
// =========================================

function renderSummary(summary) {
  if (!summary) return;

  document.getElementById("cardTotal").innerText = summary.total ?? "--";

  document.getElementById("cardCompleted").innerText =
    summary.completed ?? "--";

  document.getElementById("cardMissing").innerText = summary.missing ?? "--";

  document.getElementById("cardCancelled").innerText =
    summary.cancelled ?? "--";

  document.getElementById("cardWaiting").innerText =
    summary.average_wait ?? "--";

  document.getElementById("cardService").innerText =
    summary.average_service ?? "--";
}

// =========================================
// TABLE
// =========================================

function renderTable(records) {
  const tbody = document.getElementById("historyTable");

  if (!records || records.length === 0) {
    tbody.innerHTML = `

        <tr>

            <td colspan="9" class="text-center p-5">

                No queue records found.

            </td>

        </tr>

        `;

    return;
  }

  let html = "";

  records.forEach((queue) => {
    html += `

        <tr>

            <td>

                <strong>${queue.queue_number}</strong>

            </td>

            <td>

                ${statusBadge(queue.status)}

            </td>

            <td>

                ${queue.reception_name ?? "-"}

            </td>

            <td>

                ${queue.issued_at ?? "-"}

            </td>

            <td>

                ${queue.called_at ?? "-"}

            </td>

            <td>

                ${queue.completed_at ?? "-"}

            </td>

            <td>

                ${queue.waiting_time ?? "-"}

            </td>

            <td>

                ${queue.service_time ?? "-"}

            </td>

            <td>

                <button

                    class="btn-action"

                    onclick="viewHistory(${queue.id})">

                    <i class="fas fa-eye"></i>

                </button>

            </td>

        </tr>

        `;
  });

  tbody.innerHTML = html;
}

// =========================================
// STATUS BADGE
// =========================================

function statusBadge(status) {
  let css = "";

  switch (status) {
    case "done":
      css = "completed";

      break;

    case "waiting":
      css = "waiting";

      break;

    case "called":
      css = "called";

      break;

    case "cancelled":
      css = "cancelled";

      break;

    case "missing":
      css = "missing";

      break;

    case "returned":
      css = "returned";

      break;

    default:
      css = "waiting";
  }

  return `<span class="badge-status status-${css}">
        ${status.toUpperCase()}
    </span>`;
}

// =========================================
// PAGINATION
// =========================================

function renderPagination(data) {
  if (!data) return;

  document.getElementById("paginationInfo").innerHTML =
    `Showing ${data.from} - ${data.to} of ${data.total}`;

  const pagination = document.getElementById("pagination");

  let html = "";

  for (let i = 1; i <= data.pages; i++) {
    html += `

        <li class="page-item ${i === currentPage ? "active" : ""}">

            <button

                class="page-link"

                onclick="gotoPage(${i})">

                ${i}

            </button>

        </li>

        `;
  }

  pagination.innerHTML = html;
}

// =========================================
// PAGE
// =========================================

function gotoPage(page) {
  currentPage = page;

  loadHistory();
}

// =========================================
// RECEPTION FILTER
// =========================================

function populateReceptionFilter(list) {
  if (!Array.isArray(list)) {
    return;
  }

  const select = document.getElementById("receptionFilter");

  if (select.options.length > 1) {
    return;
  }

  list.forEach((item) => {
    select.innerHTML += `
            <option value="${item}">
                ${item}
            </option>
        `;
  });
}

// =========================================
// VIEW DETAILS
// =========================================

function viewHistory(id) {
  const modal = new bootstrap.Modal(document.getElementById("historyModal"));

  document.getElementById("historyDetails").innerHTML = `

        <div class="text-center p-5">

            <div class="spinner-border text-primary"></div>

        </div>

    `;

  modal.show();

  fetch("api/get_history_details.php?id=" + id)
    .then((res) => res.text())

    .then((html) => {
      document.getElementById("historyDetails").innerHTML = html;
    });
}

function printReport() {
  const params = new URLSearchParams({
    search: document.getElementById("searchBox").value,

    dateFrom: document.getElementById("dateFrom").value,

    dateTo: document.getElementById("dateTo").value,

    status: document.getElementById("statusFilter").value,

    reception: document.getElementById("receptionFilter").value,

    sort: document.getElementById("sortBy").value,
  });

  window.open("api/print_report.php?" + params, "_blank");
}

function printReport() {
  const params = new URLSearchParams({
    search: document.getElementById("searchBox").value,

    dateFrom: document.getElementById("dateFrom").value,

    dateTo: document.getElementById("dateTo").value,

    status: document.getElementById("statusFilter").value,

    reception: document.getElementById("receptionFilter").value,

    sort: document.getElementById("sortBy").value,
  });

  window.open("api/print_report.php?" + params, "_blank");
}

function exportExcel() {
  const params = new URLSearchParams({
    search: document.getElementById("searchBox").value,

    dateFrom: document.getElementById("dateFrom").value,

    dateTo: document.getElementById("dateTo").value,

    status: document.getElementById("statusFilter").value,

    reception: document.getElementById("receptionFilter").value,

    sort: document.getElementById("sortBy").value,
  });

  window.open("api/export_excel.php?" + params, "_blank");
}

function exportPDF() {
  const params = new URLSearchParams({
    search: document.getElementById("searchBox").value,

    dateFrom: document.getElementById("dateFrom").value,

    dateTo: document.getElementById("dateTo").value,

    status: document.getElementById("statusFilter").value,

    reception: document.getElementById("receptionFilter").value,

    sort: document.getElementById("sortBy").value,
  });

  window.open("api/export_pdf.php?" + params, "_blank");
}

function printReport() {
  const params = new URLSearchParams({
    search: document.getElementById("searchBox").value,

    dateFrom: document.getElementById("dateFrom").value,

    dateTo: document.getElementById("dateTo").value,

    status: document.getElementById("statusFilter").value,

    reception: document.getElementById("receptionFilter").value,

    sort: document.getElementById("sortBy").value,
  });

  window.open("api/print_report.php?" + params, "_blank");
}
