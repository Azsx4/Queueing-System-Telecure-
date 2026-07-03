let selectedMissingQueue = null;
let selectedVoice = null;

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

  const confirmBtn = document.getElementById("confirmMissingBtn");

  if (confirmBtn) {
    confirmBtn.addEventListener("click", function () {
      if (!selectedMissingQueue) return;

      fetch("actions/cancel_queue.php?id=" + selectedMissingQueue).then(() => {
        location.reload();
      });
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
  fetch("actions/done_queue.php?id=" + id).then(() => {
    location.reload();
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
