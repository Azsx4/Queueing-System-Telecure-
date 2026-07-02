let cancelQueueId = null;
let currentCalledQueueId = null;
let currentCalledQueueNumber = null;

function updateNowServing() {
  const servingDisplay = document.getElementById("nowServingNumber");
  if (currentCalledQueueNumber) {
    servingDisplay.textContent = currentCalledQueueNumber;
  } else {
    servingDisplay.textContent = "---";
  }
}

function updateActionButtonsState() {
  const hasCalledQueue = currentCalledQueueId !== null;
  document.getElementById("actionDoneBtn").disabled = !hasCalledQueue;
  document.getElementById("actionNextBtn").disabled = !hasCalledQueue;
  document.getElementById("actionRecallBtn").disabled = !hasCalledQueue;
  document.getElementById("actionCancelBtn").disabled = !hasCalledQueue;
  updateNowServing();
}

function actionCall() {
  // Prefer the first visible waiting queue; fall back to first waiting or Now Serving
  const waitingItems = Array.from(
    document.querySelectorAll('.queue-item[data-status="waiting"]'),
  );

  let waitingQueue = waitingItems.find((item) => {
    const style = window.getComputedStyle(item);
    return (
      style.display !== "none" &&
      style.visibility !== "hidden" &&
      item.offsetParent !== null
    );
  });

  if (!waitingQueue && waitingItems.length > 0) {
    waitingQueue = waitingItems[0];
  }

  // If still not found, try to resolve using the Now Serving display (in case of filters/pagination)
  if (!waitingQueue) {
    const now = document.getElementById("nowServingNumber")?.textContent.trim();
    if (now && now !== "---") {
      const match = Array.from(document.querySelectorAll(".queue-item")).find(
        (item) => item.getAttribute("data-queue") === now,
      );
      if (match && match.getAttribute("data-status") === "waiting") {
        waitingQueue = match;
      } else if (match) {
        // try to find next waiting after nowServing number
        const qNum = parseInt(now, 10);
        waitingQueue = waitingItems.find(
          (item) => parseInt(item.getAttribute("data-queue"), 10) > qNum,
        );
      }
    }
  }

  if (!waitingQueue) {
    alert("No waiting queue available");
    return;
  }

  const queueNumber = waitingQueue
    .querySelector(".queue-mini-number")
    .textContent.trim();
  const queueId = waitingQueue.getAttribute("data-id");

  let speech = new SpeechSynthesisUtterance(queueNumber);
  speech.rate = 0.9;

  speech.onend = function () {
    fetch("actions/call_queue.php?id=" + queueId).then(() => {
      currentCalledQueueId = queueId;
      currentCalledQueueNumber = queueNumber;
      updateActionButtonsState();
    });
  };

  speechSynthesis.speak(speech);
}

function findNextWaitingQueue() {
  const waitingItems = Array.from(
    document.querySelectorAll('.queue-item[data-status="waiting"]'),
  );

  return (
    waitingItems.find((item) => {
      const style = window.getComputedStyle(item);
      return (
        style.display !== "none" &&
        style.visibility !== "hidden" &&
        item.offsetParent !== null
      );
    }) ||
    waitingItems[0] ||
    null
  );
}

function actionDone() {
  if (!currentCalledQueueId) return;

  fetch("actions/done_queue.php?id=" + currentCalledQueueId).then(() => {
    currentCalledQueueId = null;
    currentCalledQueueNumber = null;

    updateActionButtonsState();
    showToast("Queue completed.");

    location.reload();
  });
}

function actionNext() {
  const btn = document.getElementById("actionNextBtn");

  btn.disabled = true;

  fetch("actions/next_queue.php")
    .then((res) => res.json())
    .then((data) => {
      btn.disabled = false;

      if (!data.success) {
        alert("No waiting queue.");
        return;
      }

      let speech = new SpeechSynthesisUtterance("Queue Number " + data.number);

      speech.rate = 0.9;

      speech.onend = function () {
        location.reload();
      };

      speechSynthesis.speak(speech);
    });
}
function actionRecall() {
  if (!currentCalledQueueNumber) return;

  let speech = new SpeechSynthesisUtterance(currentCalledQueueNumber);
  speech.rate = 0.9;
  speechSynthesis.speak(speech);
}

function actionCancel() {
  if (!currentCalledQueueId) return;

  cancelQueueId = currentCalledQueueId;
  document.getElementById("cancelQueueNumber").textContent =
    currentCalledQueueNumber;

  const modalEl = document.getElementById("cancelConfirmModal");
  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}

function callQueue(event, id, number) {
  event.preventDefault();

  let speech = new SpeechSynthesisUtterance(number);

  speech.rate = 0.9;

  speech.onend = function () {
    // Make request in background without navigating (page stays in same position)
    fetch("actions/call_queue.php?id=" + id).then(() => {
      currentCalledQueueId = id;
      currentCalledQueueNumber = number;
      updateActionButtonsState();
    });
  };

  speechSynthesis.speak(speech);
}

function actionSkip() {
  const nextQueue = findNextWaitingQueue();

  if (!nextQueue) {
    alert("No waiting queue");
    return;
  }

  const queueId = nextQueue.dataset.id;

  openCancelModal(new Event("click"), queueId, nextQueue.dataset.queue);
}

function doneQueue(event, id) {
  event.preventDefault();
  fetch("actions/done_queue.php?id=" + id);
}

function openCancelModal(event, id, number) {
  event.preventDefault();
  cancelQueueId = id;
  document.getElementById("cancelQueueNumber").textContent = number;

  const modalEl = document.getElementById("cancelConfirmModal");
  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}

function confirmCancelQueue() {
  if (!cancelQueueId) {
    return;
  }

  fetch("actions/cancel_queue.php?id=" + cancelQueueId).then(() => {
    const modalEl = document.getElementById("cancelConfirmModal");
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
      modal.hide();
    }
    currentCalledQueueId = null;
    currentCalledQueueNumber = null;
    cancelQueueId = null;
    updateActionButtonsState();
  });
}

document.getElementById("queueSearch").addEventListener("keyup", function () {
  let value = this.value.toLowerCase();

  document.querySelectorAll(".queue-item").forEach(function (item) {
    let queue = item.dataset.queue.toLowerCase();

    item.style.display = queue.includes(value) ? "" : "none";
  });
});

document.querySelectorAll(".status-tab").forEach((tab) => {
  tab.addEventListener("click", function () {
    document
      .querySelectorAll(".status-tab")
      .forEach((btn) => btn.classList.remove("active"));

    this.classList.add("active");

    let status = this.dataset.status;

    document.querySelectorAll(".queue-item").forEach((card) => {
      if (status === "all") {
        card.style.display = "";
        return;
      }

      card.style.display = card.dataset.status === status ? "" : "none";
    });
  });
});

function showToast(message) {
  document.getElementById("actionToastMessage").innerHTML = message;

  let toast = new bootstrap.Toast(document.getElementById("actionToast"));

  toast.show();
}

function announceCurrentQueue() {
  if (!currentCalledQueueNumber) return;

  let speech = new SpeechSynthesisUtterance(
    "Queue Number " + currentCalledQueueNumber,
  );

  speechSynthesis.speak(speech);
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
  currentCalledQueueId = ReceptionConfig.currentQueueId;
  currentCalledQueueNumber = ReceptionConfig.currentQueueNumber;

  // Fallback if there is no current queue from PHP
  if (!currentCalledQueueId) {
    const calledQueue = document.querySelector(
      '.queue-item[data-status="called"]',
    );

    if (calledQueue) {
      currentCalledQueueId = calledQueue.dataset.id;
      currentCalledQueueNumber = calledQueue.dataset.queue;
    }
  }

  updateActionButtonsState();
});

function recallQueue(id, number) {
  let speech = new SpeechSynthesisUtterance("Queue Number " + number);

  speech.rate = 0.9;

  speechSynthesis.speak(speech);
}

function doneQueue(id) {
  fetch("actions/done_queue.php?id=" + id).then(() => {
    location.reload();
  });
}

function skipQueue(id) {
  fetch("actions/cancel_queue.php?id=" + id).then(() => {
    location.reload();
  });
}
