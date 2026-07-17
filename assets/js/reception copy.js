/* ============================================================
   RECEPTION MODULE
   PART 1 - Dashboard & Rendering Engine
============================================================ */

const Reception = {
  dashboard: {},

  selectedMissingQueue: null,

  selectedReturnQueue: null,

  selectedVoice: null,

  currentCalledQueueId: null,

  currentCalledQueueNumber: null,
};

/* ============================================================
   DASHBOARD LOADER
============================================================ */

async function loadReceptionDashboard() {
  try {
    const response = await fetch("actions/get_reception_dashboard.php");

    Reception.dashboard = await response.json();

    if (!Reception.dashboard.success) {
      console.error("Unable to load dashboard.");

      return;
    }

    updateDashboardCounter();

    updateNowServing();

    renderActiveQueues();

    renderUpcomingQueues();
  } catch (error) {
    console.error(error);
  }
}

/* ============================================================
   HEADER COUNTER
============================================================ */

function updateDashboardCounter() {
  const counter = document.getElementById("active-count");

  if (!counter) return;

  counter.textContent =
    Reception.dashboard.statistics.called +
    "/" +
    Reception.dashboard.activeSlots;
}

/* ============================================================
   NOW SERVING
============================================================ */

function updateNowServing() {
  const serving = document.getElementById("nowServingNumber");

  if (!serving) return;

  const queue = Reception.dashboard.current;

  if (!queue) {
    serving.textContent = "---";

    Reception.currentCalledQueueId = null;

    Reception.currentCalledQueueNumber = null;

    updateActionButtonsState();

    return;
  }

  Reception.currentCalledQueueId = queue.id;

  Reception.currentCalledQueueNumber = String(queue.queue_number).padStart(
    3,
    "0",
  );

  serving.textContent = Reception.currentCalledQueueNumber;

  updateActionButtonsState();
}

/* ============================================================
   ACTIVE QUEUES
============================================================ */

function renderActiveQueues() {
  const container = document.getElementById("activeQueueContainer");

  if (!container) return;

  const queues = Reception.dashboard.activeQueues;

  if (!queues.length) {
    container.innerHTML = getEmptyActiveQueue();

    return;
  }

  let html = "";

  queues.forEach((queue) => {
    html += createActiveQueueCard(queue);
  });

  container.innerHTML = html;
}

/* ============================================================
   UPCOMING QUEUES
============================================================ */

function renderUpcomingQueues() {
  const container = document.getElementById("upcomingQueueContainer");

  if (!container) return;

  const queues = Reception.dashboard.upcomingQueues;

  if (!queues.length) {
    container.innerHTML = getEmptyUpcomingQueue();

    return;
  }

  let html = "";

  queues.forEach((queue) => {
    html += createUpcomingQueueCard(queue);
  });

  container.innerHTML = html;
}

/* ============================================================
   ACTIVE QUEUE CARD
============================================================ */

function createActiveQueueCard(queue) {
  const number = String(queue.queue_number).padStart(3, "0");

  const waiting = formatWaitingTime(queue.waiting_seconds);

  const isCaller =
    Number(queue.called_by) === Number(window.ReceptionConfig.receptionId);

  return `

<div class="active-queue-card"

data-id="${queue.id}"

onclick="recallQueue(${queue.id},'${number}')">

<div class="queue-card-header">

<div class="queue-title">

<i class="fas fa-user"></i>

<strong>Reception :</strong>

${queue.reception_name}

</div>

<span class="queue-badge">

ACTIVE

</span>

</div>

<div class="queue-card-body">

<div class="queue-number-col">

<span class="queue-no">

${number}

</span>

</div>

<div class="queue-info">

<div>

<i class="fas fa-clock"></i>

<strong>Called :</strong>

${queue.called_at}

</div>

<div>

<i class="fas fa-stopwatch"></i>

<strong>Waiting :</strong>

${waiting}

</div>

</div>

<div class="queue-actions">

${
  isCaller
    ? `

<button class="action-icon done"

onclick="event.stopPropagation();doneQueue(${queue.id})">

<i class="fas fa-check-circle"></i>

</button>

<button class="action-icon missing"

onclick="event.stopPropagation();showMissingModal(${queue.id},'${number}')">

<i class="fas fa-user-slash"></i>

</button>

`
    : `

<button class="action-icon done" disabled>

<i class="fas fa-check-circle"></i>

</button>

<button class="action-icon missing" disabled>

<i class="fas fa-user-slash"></i>

</button>

`
}

</div>

</div>

</div>

`;
}

/* ============================================================
   UPCOMING QUEUE CARD
============================================================ */

function createUpcomingQueueCard(queue) {
  const number = String(queue.queue_number).padStart(3, "0");

  const returned = Number(queue.priority_order) > 0;

  return `

<div class="upcoming-card">

${
  returned
    ? `<span class="returned-badge">

RETURNED

</span>`
    : ``
}

<div class="upcoming-number">

${number}

</div>

</div>

`;
}

/* ============================================================
   EMPTY STATES
============================================================ */

function getEmptyActiveQueue() {
  return `

<div class="empty-active">

<i class="fas fa-users fa-4x text-secondary mb-3"></i>

<h5>No Active Queue</h5>

<small class="text-secondary">

Click "Next Queue"

</small>

</div>

`;
}

function getEmptyUpcomingQueue() {
  return `

<div class="text-center text-secondary py-4">

No Upcoming Queue

</div>

`;
}

/* ============================================================
   HELPERS
============================================================ */

function formatWaitingTime(seconds) {
  seconds = Number(seconds);

  const minutes = Math.floor(seconds / 60);

  const remain = seconds % 60;

  return (
    String(minutes).padStart(2, "0") + ":" + String(remain).padStart(2, "0")
  );
}

/* ============================================================
   PART 2 - Queue Actions
============================================================ */

/* ============================================================
   NEXT QUEUE
============================================================ */

async function actionNext() {
  const btn = document.getElementById("actionNextBtn");

  if (btn) btn.disabled = true;

  try {
    const response = await fetch("actions/next_queue.php");

    const data = await response.json();

    if (btn) btn.disabled = false;

    if (!data.success) {
      showToast("No waiting queue.", "warning");

      return;
    }

    Reception.currentCalledQueueId = data.id;
    Reception.currentCalledQueueNumber = data.number;

    const speech = new SpeechSynthesisUtterance("Queue Number " + data.number);

    speech.rate = 0.9;

    setSpeechVoice(speech);

    speech.onend = async function () {
      await loadReceptionDashboard();

      showToast("Queue " + data.number + " has been called.", "success");
    };

    speechSynthesis.speak(speech);
  } catch (error) {
    console.error(error);

    if (btn) btn.disabled = false;

    showToast("Unable to call next queue.", "danger");
  }
}

/* ============================================================
   RECALL QUEUE
============================================================ */

async function recallQueue(id, number) {
  const speech = new SpeechSynthesisUtterance("Queue Number " + number);

  speech.rate = 0.9;

  setSpeechVoice(speech);

  speech.onend = async function () {
    await fetch("actions/call_queue.php?id=" + id);

    Reception.currentCalledQueueId = id;

    Reception.currentCalledQueueNumber = number;

    updateActionButtonsState();
  };

  speechSynthesis.speak(speech);
}

/* ============================================================
   DONE QUEUE
============================================================ */

async function doneQueue(id) {
  try {
    const response = await fetch("actions/done_queue.php?id=" + id);

    const result = await response.text();

    if (result.trim() !== "success") {
      showToast(result, "danger");

      return;
    }

    showToast("Queue marked as completed.", "success");

    await loadReceptionDashboard();
  } catch (error) {
    console.error(error);

    showToast("Unable to complete queue.", "danger");
  }
}

/* ============================================================
   MISSING QUEUE
============================================================ */

async function skipQueue(id) {
  try {
    await fetch("actions/cancel_queue.php?id=" + id);

    showToast("Queue marked as Missing.", "warning");

    await loadReceptionDashboard();
  } catch (error) {
    console.error(error);
  }
}

/* ============================================================
   RETURN QUEUE
============================================================ */

async function returnQueue(id) {
  try {
    const response = await fetch("actions/return_queue.php?id=" + id);

    const result = await response.text();

    if (result.trim() !== "success") {
      showToast(result, "danger");

      return;
    }

    showToast("Patient moved to Priority Queue.", "success");

    await loadReceptionDashboard();
  } catch (error) {
    console.error(error);
  }
}

/* ============================================================
   NOW SERVING ANNOUNCEMENT
============================================================ */

function announceCurrentQueue() {
  if (!Reception.currentCalledQueueNumber) return;

  const speech = new SpeechSynthesisUtterance(
    "Queue Number " + Reception.currentCalledQueueNumber,
  );

  setSpeechVoice(speech);

  speech.rate = 0.9;

  speechSynthesis.speak(speech);
}

/* ============================================================
   ACTION BUTTONS
============================================================ */

function updateActionButtonsState() {
  const hasQueue = Reception.currentCalledQueueId !== null;

  const doneBtn = document.getElementById("actionDoneBtn");

  if (doneBtn) doneBtn.disabled = !hasQueue;

  const skipBtn = document.getElementById("actionCancelBtn");

  if (skipBtn) skipBtn.disabled = !hasQueue;
}

/* ============================================================
   ACTION BAR BUTTONS
============================================================ */

async function actionDone() {
  if (!Reception.currentCalledQueueId) return;

  await doneQueue(Reception.currentCalledQueueId);
}

function actionMissing() {
  if (!Reception.currentCalledQueueId) return;

  showMissingModal(
    Reception.currentCalledQueueId,

    Reception.currentCalledQueueNumber,
  );
}

/* ============================================================
   REFRESH
============================================================ */

function refreshDashboard() {
  loadReceptionDashboard();
}

/* ============================================================
   PART 3 - Voice, Modals & Initialization
============================================================ */

/* ============================================================
   VOICE SETTINGS
============================================================ */

let availableVoices = [];

function loadVoices() {
  availableVoices = speechSynthesis.getVoices();

  const select = document.getElementById("voiceSelect");

  if (!select) return;

  select.innerHTML = "";

  availableVoices.forEach((voice, index) => {
    const option = document.createElement("option");

    option.value = index;

    option.textContent = `${voice.name} (${voice.lang})`;

    select.appendChild(option);
  });
}

function setSpeechVoice(utterance) {
  const select = document.getElementById("voiceSelect");

  if (!select) return;

  const index = Number(select.value);

  if (availableVoices[index]) {
    utterance.voice = availableVoices[index];
  }
}

/* ============================================================
   TOAST
============================================================ */

function showToast(message, type = "success") {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      toast: true,

      position: "top-end",

      timer: 2500,

      timerProgressBar: true,

      showConfirmButton: false,

      icon: type,

      title: message,
    });

    return;
  }

  console.log(type.toUpperCase(), message);
}

/* ============================================================
   MISSING MODAL
============================================================ */

function showMissingModal(id, queueNumber) {
  Reception.selectedMissingQueue = id;

  const queue = document.getElementById("missingQueueNumber");

  if (queue) {
    queue.textContent = queueNumber;
  }

  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("missingModal"),
  );

  modal.show();
}

async function confirmMissing() {
  if (!Reception.selectedMissingQueue) return;

  await skipQueue(Reception.selectedMissingQueue);

  bootstrap.Modal.getInstance(document.getElementById("missingModal"))?.hide();

  Reception.selectedMissingQueue = null;
}

/* ============================================================
   RETURN MODAL
============================================================ */

function showReturnModal(id, queueNumber) {
  Reception.selectedReturnQueue = id;

  const queue = document.getElementById("returnQueueNumber");

  if (queue) {
    queue.textContent = queueNumber;
  }

  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("returnModal"),
  );

  modal.show();
}

async function confirmReturn() {
  if (!Reception.selectedReturnQueue) return;

  await returnQueue(Reception.selectedReturnQueue);

  bootstrap.Modal.getInstance(document.getElementById("returnModal"))?.hide();

  Reception.selectedReturnQueue = null;
}

/* ============================================================
   HISTORY
============================================================ */

async function loadHistory(url, tableId) {
  try {
    const response = await fetch(url);

    const html = await response.text();

    document.getElementById(tableId).innerHTML = html;
  } catch (error) {
    console.error(error);
  }
}

function loadCompletedHistory() {
  loadHistory(
    "actions/get_completed_history.php",

    "completedHistoryContainer",
  );
}

function loadMissingHistory() {
  loadHistory(
    "actions/get_missing_history.php",

    "missingHistoryContainer",
  );
}

/* ============================================================
   AUTO REFRESH
============================================================ */

function startDashboardPolling() {
  setInterval(() => {
    loadReceptionDashboard();
  }, 3000);
}

/* ============================================================
   INITIALIZATION
============================================================ */

document.addEventListener("DOMContentLoaded", async function () {
  loadVoices();

  if (speechSynthesis.onvoiceschanged !== undefined) {
    speechSynthesis.onvoiceschanged = loadVoices;
  }

  await loadReceptionDashboard();

  startDashboardPolling();

  const nextBtn = document.getElementById("actionNextBtn");

  if (nextBtn) {
    nextBtn.addEventListener(
      "click",

      actionNext,
    );
  }

  const doneBtn = document.getElementById("actionDoneBtn");

  if (doneBtn) {
    doneBtn.addEventListener(
      "click",

      actionDone,
    );
  }

  const missingBtn = document.getElementById("actionCancelBtn");

  if (missingBtn) {
    missingBtn.addEventListener(
      "click",

      actionMissing,
    );
  }

  const recallBtn = document.getElementById("nowServingNumber");

  if (recallBtn) {
    recallBtn.addEventListener(
      "click",

      announceCurrentQueue,
    );
  }

  const confirmMissingBtn = document.getElementById("confirmMissingBtn");

  if (confirmMissingBtn) {
    confirmMissingBtn.addEventListener(
      "click",

      confirmMissing,
    );
  }

  const confirmReturnBtn = document.getElementById("confirmReturnBtn");

  if (confirmReturnBtn) {
    confirmReturnBtn.addEventListener(
      "click",

      confirmReturn,
    );
  }
});

/* ============================================================
   DEBUG
============================================================ */

window.Reception = Reception;
