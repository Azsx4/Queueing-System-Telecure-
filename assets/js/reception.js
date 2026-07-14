let selectedMissingQueue = null;
let selectedVoice = null;
let selectedReturnQueue = null;

function confirmReturnQueue(row) {
  selectedReturnQueue = row.dataset.id;

  document.getElementById("returnQueueNumber").textContent = row.dataset.queue;

  new bootstrap.Modal(document.getElementById("returnQueueModal")).show();
}

function showMissingModal(id, number) {
  selectedMissingQueue = id;

  document.getElementById("missingQueueNumber").innerHTML = "Queue " + number;

  const modal = new bootstrap.Modal(document.getElementById("missingModal"));

  modal.show();
}

function populateVoiceList() {
  const voiceSelect = document.getElementById("voiceSelect");
  if (!voiceSelect || !window.speechSynthesis) return;

  const voices = window.speechSynthesis.getVoices();
  voiceSelect.innerHTML = "";

  const defaultOption = document.createElement("option");
  defaultOption.value = "";
  defaultOption.textContent = "Voice Settings";
  defaultOption.selected = true;
  voiceSelect.appendChild(defaultOption);

  if (!voices.length) {
    defaultOption.textContent = "No voices available";
    defaultOption.disabled = true;
    return;
  }

  voices.forEach((voice) => {
    const option = document.createElement("option");
    option.value = voice.name;
    option.textContent = `${voice.name}${voice.lang ? ` (${voice.lang})` : ""}`;
    voiceSelect.appendChild(option);
  });

  if (selectedVoice) {
    voiceSelect.value = selectedVoice.name;
  }
}

function setSpeechVoice(utterance) {
  if (!selectedVoice) return;
  utterance.voice = selectedVoice;
}

function showQueueCompletedHistory(status) {
  fetch("actions/get_completed_history.php?status=" + status)
    .then((res) => res.text())

    .then((html) => {
      document.getElementById("queueHistoryTitle").innerHTML =
        status == "done" ? "Completed Queues" : "Missing / Skipped Queues";

      document.getElementById("queueHistoryContent").innerHTML = html;

      new bootstrap.Modal(document.getElementById("queueHistoryModal")).show();
    });
}

function showQueueMissingHistory(status) {
  fetch("actions/get_missing_history.php?status=" + status)
    .then((res) => res.text())

    .then((html) => {
      document.getElementById("queueHistoryTitle").innerHTML =
        status == "done" ? "Completed Queues" : "Missing / Skipped Queues";

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

function showToast(message, type = "success") {
  const toastEl = document.getElementById("actionToast");
  const toastBody = document.getElementById("actionToastMessage");

  toastBody.textContent = message;

  toastEl.classList.remove(
    "text-bg-success",
    "text-bg-danger",
    "text-bg-warning",
    "text-bg-info",
  );

  switch (type) {
    case "danger":
      toastEl.classList.add("text-bg-danger");
      break;

    case "warning":
      toastEl.classList.add("text-bg-warning");
      break;

    case "info":
      toastEl.classList.add("text-bg-info");
      break;

    default:
      toastEl.classList.add("text-bg-success");
  }

  bootstrap.Toast.getOrCreateInstance(toastEl).show();
}

document.addEventListener("DOMContentLoaded", function () {
  const voiceSelect = document.getElementById("voiceSelect");
  if (voiceSelect) {
    voiceSelect.addEventListener("change", function () {
      const voices = window.speechSynthesis.getVoices();
      selectedVoice = voices.find((voice) => voice.name === this.value) || null;
    });
    populateVoiceList();
    if (window.speechSynthesis && "onvoiceschanged" in window.speechSynthesis) {
      window.speechSynthesis.onvoiceschanged = populateVoiceList;
    }
  }
  const confirmReturnBtn = document.getElementById("confirmReturnQueueBtn");

  if (confirmReturnBtn) {
    confirmReturnBtn.addEventListener("click", function () {
      fetch("actions/return_queue.php?id=" + selectedReturnQueue)
        .then((res) => res.text())

        .then((data) => {
          if (data.trim() == "success") {
            showToast("Patient moved to Priority Queue.", "success");

            setTimeout(() => {
              location.reload();
            }, 1800);
          } else {
            showToast(data, "danger");
          }
        });
    });
  }

  const confirmBtn = document.getElementById("confirmMissingBtn");

  if (confirmBtn) {
    confirmBtn.addEventListener("click", function () {
      if (!selectedMissingQueue) return;

      fetch("actions/cancel_queue.php?id=" + selectedMissingQueue).then(() => {
        showToast("Queue marked as Missing.", "warning");

        setTimeout(() => {
          location.reload();
        }, 2000);
      });
    });
  }

  const changeReceptionBtn = document.getElementById("changeReceptionBtn");
  const changeReceptionForm = document.getElementById("changeReceptionForm");
  const confirmChangeReceptionBtn = document.getElementById(
    "confirmChangeReceptionBtn",
  );
  const changeReceptionModalEl = document.getElementById(
    "changeReceptionModal",
  );

  if (
    changeReceptionBtn &&
    changeReceptionModalEl &&
    confirmChangeReceptionBtn &&
    changeReceptionForm
  ) {
    const changeReceptionModal = new bootstrap.Modal(changeReceptionModalEl);

    changeReceptionBtn.addEventListener("click", function () {
      changeReceptionModal.show();
    });

    confirmChangeReceptionBtn.addEventListener("click", function () {
      changeReceptionForm.submit();
    });
  }

  if (window.ReceptionConfig) {
    currentCalledQueueId = window.ReceptionConfig.currentQueueId || null;
    currentCalledQueueNumber =
      window.ReceptionConfig.currentQueueNumber || null;
  }

  updateActionButtonsState();
});

let currentCalledQueueId = null;
let currentCalledQueueNumber = null;

function updateNowServing() {
  const servingDisplay = document.getElementById("nowServingNumber");
  if (!servingDisplay) return;

  servingDisplay.textContent = currentCalledQueueNumber || "---";
}

function updateActionButtonsState() {
  const hasCalledQueue = currentCalledQueueId !== null;

  const actionDoneBtn = document.getElementById("actionDoneBtn");
  if (actionDoneBtn) {
    actionDoneBtn.disabled = !hasCalledQueue;
  }

  const actionCancelBtn = document.getElementById("actionCancelBtn");
  if (actionCancelBtn) {
    actionCancelBtn.disabled = !hasCalledQueue;
  }

  updateNowServing();
}

function actionNext() {
  const btn = document.getElementById("actionNextBtn");
  if (btn) {
    btn.disabled = true;
  }

  fetch("actions/next_queue.php")
    .then((res) => res.json())
    .then((data) => {
      if (btn) {
        btn.disabled = false;
      }

      if (!data.success) {
        alert("No waiting queue.");
        return;
      }

      const speech = new SpeechSynthesisUtterance(
        "Queue Number " + data.number,
      );
      setSpeechVoice(speech);
      speech.rate = 0.9;

      speech.onend = function () {
        location.reload();
      };

      speechSynthesis.speak(speech);
    })
    .catch(() => {
      if (btn) {
        btn.disabled = false;
      }
    });
}

function recallQueue(id, number) {
  const speech = new SpeechSynthesisUtterance("Queue Number " + number);
  setSpeechVoice(speech);
  speech.rate = 0.9;

  speech.onend = function () {
    fetch("actions/call_queue.php?id=" + id).then(() => {
      currentCalledQueueId = id;
      currentCalledQueueNumber = number;
      updateActionButtonsState();
    });
  };

  speechSynthesis.speak(speech);
}

function doneQueue(id) {
  fetch("actions/done_queue.php?id=" + id)
    .then((res) => res.text())

    .then((data) => {
      if (data.trim() === "success") {
        showToast("Queue marked as Completed.");

        setTimeout(() => {
          location.reload();
        }, 2000);
      } else {
        showToast(data, "danger");
      }
    });
}

function skipQueue(id) {
  fetch("actions/cancel_queue.php?id=" + id).then(() => {
    location.reload();
  });
}

function announceCurrentQueue() {
  if (!currentCalledQueueNumber) return;

  const speech = new SpeechSynthesisUtterance(
    "Queue Number " + currentCalledQueueNumber,
  );
  setSpeechVoice(speech);
  speechSynthesis.speak(speech);
}
