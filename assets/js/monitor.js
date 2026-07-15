let previousData = {
  active: [],
  upcoming: [],
};

/* ---------------- Clock ---------------- */

function updateClock() {
  const now = new Date();

  document.getElementById("clock").innerHTML = now.toLocaleTimeString();

  document.getElementById("date").innerHTML = now.toLocaleDateString(
    undefined,
    {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    },
  );
}

updateClock();

setInterval(updateClock, 1000);

/* ---------------- Monitor ---------------- */

async function loadMonitor() {
  try {
    const response = await fetch("api/get_monitor_data.php?ts=" + Date.now());

    const data = await response.json();

    updateActiveQueues(data.active);

    updateUpcomingQueues(data.upcoming);
  } catch (e) {
    console.log(e);
  }
}

function updateActiveQueues(active) {
  if (JSON.stringify(active) === JSON.stringify(previousData.active)) {
    return;
  }

  previousData.active = active;

  renderActive(active);
}

function updateUpcomingQueues(upcoming) {
  if (JSON.stringify(upcoming) === JSON.stringify(previousData.upcoming)) {
    return;
  }

  previousData.upcoming = upcoming;

  renderUpcoming(upcoming);
}

function renderActive(active) {
  let html = "";

  if (active.length === 0) {
    html = `

        <div class="col-12">

            <div class="reception-card">

                <small>No Active Queue</small>

                <div class="queue-number">

                    ---

                </div>

            </div>

        </div>

        `;
  } else {
    active.forEach((item) => {
      html += `

            <div class="col-md-4">

                <div class="reception-card queue-animation">

                    <small>

                        ${item.reception}

                    </small>

                    <div class="queue-number">

                        ${item.queue_number}

                    </div>

                </div>

            </div>

            `;
    });
  }

  document.getElementById("activeReceptionContainer").innerHTML = html;
}

function renderUpcoming(upcoming) {
  let html = "";

  for (let i = 0; i < 6; i++) {
    html += `

        <div class="upcoming-card">

            ${upcoming[i] ?? "---"}

        </div>

        `;
  }

  document.getElementById("upcomingContainer").innerHTML = html;
}

loadMonitor();

setInterval(loadMonitor, 2000);
