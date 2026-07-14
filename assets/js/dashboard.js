/*
==========================================
 Dashboard JS
 Queue System Ver. 3.0
==========================================
*/

const Dashboard = {
  refreshInterval: 30000,
  activityChart: null,
  distributionChart: null,

  api: {
    summary: "api/dashboard_summary.php",

    activity: "api/dashboard_activity.php",

    distribution: "api/dashboard_distribution.php",

    timeline: "api/dashboard_timeline.php",

    reception: "api/dashboard_reception.php",

    insights: "api/dashboard_insights.php",
  },

  init() {
    this.loadActivity();

    this.loadSummary();

    this.loadDistribution();

    this.loadTimeline();

    this.startAutoRefresh();

    this.loadReceptionPerformance();

    this.loadInsights();
  },

  async loadSummary() {
    this.setLoading(true);

    try {
      const response = await fetch(this.api.summary, {
        cache: "no-store",
      });

      if (!response.ok) {
        throw new Error("Unable to connect to API.");
      }

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.message);
      }

      this.renderSummary(result.data);

      this.updateTimestamp(result.timestamp);
    } catch (error) {
      console.error(error);

      this.showError(error.message);
    } finally {
      this.setLoading(false);
    }
  },

  async loadActivity() {
    const response = await fetch("api/dashboard_activity.php");

    const result = await response.json();

    if (!result.success) {
      return;
    }

    if (this.activityChart) {
      this.activityChart.destroy();
    }

    const ctx = document.getElementById("activityChart");

    this.activityChart = new Chart(ctx, {
      type: "bar",

      data: {
        labels: result.labels,

        datasets: [
          {
            label: "Queues",

            data: result.values,

            borderRadius: 8,

            backgroundColor: "#0ea5ff",
          },
        ],
      },

      options: {
        responsive: true,

        maintainAspectRatio: false,

        plugins: {
          legend: {
            display: false,
          },
        },
      },
    });
  },

  async loadDistribution() {
    try {
      const response = await fetch(this.api.distribution);

      const result = await response.json();

      if (!result.success) return;

      if (this.distributionChart) {
        this.distributionChart.destroy();
      }

      const ctx = document.getElementById("distributionChart");

      this.distributionChart = new Chart(ctx, {
        type: "doughnut",

        data: {
          labels: result.labels,

          datasets: [
            {
              data: result.values,

              backgroundColor: [
                "#f59e0b", // Waiting

                "#3b82f6", // Serving

                "#22c55e", // Completed

                "#ef4444", // Missing
              ],

              borderWidth: 0,

              hoverOffset: 12,
            },
          ],
        },

        options: {
          responsive: true,

          maintainAspectRatio: false,

          cutout: "70%",

          plugins: {
            legend: {
              position: "bottom",
            },
          },
        },
      });
    } catch (error) {
      console.error(error);
    }
  },

  async loadTimeline() {
    const response = await fetch(this.api.timeline);

    const result = await response.json();

    if (!result.success) {
      return;
    }

    const container = document.getElementById("timelineFeed");

    container.innerHTML = "";

    result.events.forEach((event) => {
      container.innerHTML += `

        <div class="timeline-item">

            <div class="timeline-time">

                ${event.label}

            </div>

            <div class="timeline-icon bg-${event.color}">

                <i class="fas fa-${event.icon}"></i>

            </div>

            <div class="timeline-text">

                ${event.text}

            </div>

        </div>

        `;
    });
  },

  async loadReceptionPerformance() {
    try {
      const response = await fetch(this.api.reception);

      const result = await response.json();

      if (!result.success) {
        return;
      }

      const tbody = document.querySelector("#receptionPerformanceTable tbody");

      tbody.innerHTML = "";

      if (result.rows.length === 0) {
        tbody.innerHTML = `
                <tr>

                    <td colspan="7"
                        class="text-center text-secondary py-4">

                        No reception activity today.

                    </td>

                </tr>
            `;

        return;
      }

      result.rows.forEach((row) => {
        tbody.innerHTML += `

            <tr>

                <td>

                    <strong>${row.reception}</strong>

                </td>

                <td class="text-center">

                    <span class="badge bg-success">

                        ${row.completed}

                    </span>

                </td>

                <td class="text-center">

                    <span class="badge bg-danger">

                        ${row.missing}

                    </span>

                </td>

                <td class="text-center">

                    ${row.averageWait}

                </td>

                <td class="text-center">

                    ${row.averageService}

                </td>

                <td class="text-center">

                    ${row.returned}

                </td>

                <td>

                    <div class="progress performance-progress">

                        <div
                            class="progress-bar bg-primary"
                            style="width:${row.utilization}%">

                            ${row.utilization}%

                        </div>

                    </div>

                </td>

            </tr>

            `;
      });
    } catch (error) {
      console.error(error);
    }
  },

  async loadInsights() {
    try {
      const response = await fetch(this.api.insights);

      const result = await response.json();

      if (!result.success) {
        return;
      }

      const container = document.getElementById("queueInsights");

      container.innerHTML = "";

      const cards = [
        {
          icon: "fa-trophy",
          color: "success",
          title: "Best Reception",
          value: result.bestReception
            ? result.bestReception.reception_name
            : "-",
          subtitle: result.bestReception
            ? result.bestReception.completed + " Completed"
            : "",
        },

        {
          icon: "fa-clock",
          color: "primary",
          title: "Peak Hour",
          value: result.peakHour ? result.peakHour.hr + ":00" : "-",
          subtitle: result.peakHour ? result.peakHour.total + " Queues" : "",
        },

        {
          icon: "fa-hourglass-half",
          color: "warning",
          title: "Longest Wait",
          value: result.waiting ? "Q-" + result.waiting.queue_number : "-",
          subtitle: result.waiting ? result.waiting.waiting + " Minutes" : "",
        },

        {
          icon: "fa-users",
          color: "info",
          title: "Active Reception",

          value: result.activeReception,

          subtitle: "Receptionists",
        },

        {
          icon: "fa-rotate-left",
          color: "secondary",
          title: "Returned",

          value: result.returned,

          subtitle: "Patients",
        },

        {
          icon: "fa-chart-line",
          color: "success",
          title: "Completion",

          value: result.completion + "%",

          subtitle: "Today's Rate",
        },
      ];

      cards.forEach((card) => {
        container.innerHTML += `

            <div class="col-lg-4 col-md-6">

                <div class="insight-card">

                    <div class="insight-icon bg-${card.color}">

                        <i class="fas ${card.icon}"></i>

                    </div>

                    <div class="insight-title">

                        ${card.title}

                    </div>

                    <div class="insight-value">

                        ${card.value}

                    </div>

                    <div class="insight-subtitle">

                        ${card.subtitle}

                    </div>

                </div>

            </div>

            `;
      });
    } catch (error) {
      console.error(error);
    }
  },

  renderSummary(data) {
    this.setValue("totalQueues", data.total);

    this.setValue("waitingQueues", data.waiting);

    this.setValue("activeQueues", data.active);

    this.setValue("completedQueues", data.completed);

    this.setValue("missingQueues", data.missing);

    this.setValue("completionRate", data.completionRate + "%");

    this.setValue("averageWait", data.averageWait);

    this.setValue("averageService", data.averageService);

    this.setValue("peakHour", data.peakHour);

    this.setValue("utilization", data.utilization + "%");
  },

  setValue(id, value) {
    const el = document.getElementById(id);

    if (!el) return;

    el.textContent = value;
  },

  setLoading(isLoading) {
    const cards = document.querySelectorAll(".dashboard-card");

    cards.forEach((card) => {
      if (isLoading) {
        card.classList.add("dashboard-loading");
      } else {
        card.classList.remove("dashboard-loading");
      }
    });
  },

  showError(message) {
    console.error(message);
  },

  updateTimestamp(time) {
    let label = document.getElementById("dashboardTimestamp");

    if (!label) {
      return;
    }

    label.innerHTML = `<i class="fas fa-clock"></i> Updated ${time}`;
  },

  startAutoRefresh() {
    setInterval(() => {
      this.loadSummary();

      this.loadActivity();

      this.loadDistribution();

      this.loadTimeline();

      this.loadReceptionPerformance();

      this.loadInsights();
    }, this.refreshInterval);
  },
};

document.addEventListener("DOMContentLoaded", () => {
  Dashboard.init();
});
