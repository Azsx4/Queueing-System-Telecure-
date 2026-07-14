function showQueueCompletedHistory(status) {
  fetch("actions/get_queue_history.php?status=" + status)
    .then((res) => res.text())
    .then((html) => {
      document.getElementById("queueHistoryTitle").innerHTML =
        status === "all"
          ? "All Queues Today"
          : status === "done"
            ? "Completed Queues"
            : "Missing / Skipped Queues";

      document.getElementById("queueHistoryContent").innerHTML = html;
      new bootstrap.Modal(document.getElementById("queueHistoryModal")).show();
    });
}

function openPrintPopup(url) {
  const width = 900;
  const height = 650;
  const left = window.screenX + (window.innerWidth - width) / 2;
  const top = window.screenY + (window.innerHeight - height) / 2;

  window.open(
    url,
    "printWindow",
    `toolbar=no,scrollbars=yes,resizable=yes,width=${width},height=${height},top=${top},left=${left}`,
  );
}
