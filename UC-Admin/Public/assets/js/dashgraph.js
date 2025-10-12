document.addEventListener("DOMContentLoaded", () => {
  const selector = document.getElementById("graph-selector");
  const allGraphs = document.querySelectorAll(".graph-container");

  function hideAll() {
    allGraphs.forEach((g) => (g.style.display = "none"));
  }
  function show(id) {
    const g = document.getElementById(id);
    if (g) g.style.display = "block";
  }

  hideAll();
  show(selector.value);

  selector.addEventListener("change", () => {
    hideAll();
    show(selector.value);
  });
});

document.querySelectorAll(".nav-rbtn input").forEach((radio) => {
  radio.addEventListener("change", function () {
    // This will trigger when selection changes
    const selectedView = this.value;

    // Here you would update your chart or content
    console.log("Selected view:", selectedView);

    // Example: You might call a function like:
    // updateContent(selectedView);
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const tabs = document.querySelectorAll(".tab");
  const contents = document.querySelectorAll(".tab-content");

  tabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      tabs.forEach((t) => t.classList.remove("active"));
      contents.forEach((c) => (c.style.display = "none"));
      this.classList.add("active");
      const targetId = this.getAttribute("data-target");
      const targetContent = document.getElementById(targetId);
      if (targetContent) {
        targetContent.style.display = "block";
      }
    });
  });
});
//============================Graphs========================
document.addEventListener("DOMContentLoaded", function () {
  fetch("dashboard.dbf/med_den_graph.php")
    .then((response) => response.json())
    .then((data) => {
      const labels = Object.keys(data);
      const values = Object.values(data);

      const ctx = document.getElementById("familyDentalChart").getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Number of Clients",
              data: values,
              backgroundColor: [
                "#4fd1c5",
                "#63b3ed",
                "#fbd38d",
                "#b794f4",
                "#ed8936",
                "#9b2c2c",
                "#f56565",
                "#48bb78",
                "#4299e1",
                "#ed64a6",
                "#ecc94b",
                "#a0aec0",
                "#38b2ac",
                "#9f7aea",
                "#f6ad55",
                "#e53e3e",
                "#3182ce",
                "#2f855a",
                "#c53030",
                "#805ad5",
                "#f687b3",
                "#81e6d9",
                "#cbd5e0",
                "#fc8181",
              ],
              borderRadius: 0,
              barPercentage: 0.4,
              categoryPercentage: 0.7,
              maxBarThickness: 100,
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
            tooltip: {
              enabled: true,
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: "Number of Clients",
                font: {
                  weight: "bold",
                  size: 14,
                },
              },
            },
            x: {
              ticks: {
                padding: 1,
                color: "#2a4365",
                font: {
                  weight: "bold",
                  size: 9,
                },
                maxRotation: 20,
                minRotation: 10,
              },
              grid: {
                offset: true,
              },

              categoryPercentage: 0.6,
              barPercentage: 1.7,
            },
          },
        },
      });
    })
    .catch((error) => console.error("Error loading graph data:", error));
});
//=====
document.addEventListener("DOMContentLoaded", function () {
  fetch("dashboard.dbf/fam_med_graph.php")
    .then((response) => response.json())
    .then((data) => {
      const labels = Object.keys(data);
      const values = Object.values(data);
      const ctx = document
        .getElementById("familyMedicalChart")
        .getContext("2d");

      new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Total Cases",
              data: values,
              backgroundColor: [
                "#4fd1c5",
                "#63b3ed",
                "#fbd38d",
                "#b794f4",
                "#ed8936",
                "#9b2c2c",
                "#f56565",
                "#48bb78",
                "#4299e1",
                "#ed64a6",
                "#ecc94b",
                "#a0aec0",
                "#38b2ac",
                "#9f7aea",
                "#f6ad55",
                "#e53e3e",
                "#3182ce",
                "#2f855a",
                "#c53030",
                "#805ad5",
                "#f687b3",
                "#81e6d9",
                "#cbd5e0",
                "#fc8181",
              ],
              borderRadius: 0,
              barPercentage: 0.6,
              categoryPercentage: 0.7,
              maxBarThickness: 100,
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
            tooltip: {
              enabled: true,
            },
          },
          scales: {
            x: {
              ticks: {
                font: {
                  size: 9,
                  weight: "bold",
                },
                maxRotation: 0,
                minRotation: 0,
              },
              grid: {
                offset: true,
              },
            },
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: "Number of Clients",
                font: {
                  weight: "bold",
                  size: 14,
                },
              },
            },
          },
        },
      });
    })
    .catch((error) => console.error("Error loading chart data:", error));
});
//=====
document.addEventListener("DOMContentLoaded", function () {
  fetch("dashboard.dbf/pers_soc_graph.php")
    .then((res) => res.json())
    .then((data) => {
      const labels = Object.keys(data);
      const yesData = labels.map((l) => data[l].yes);
      const noData = labels.map((l) => data[l].no);
      const frmData = labels.map((l) => data[l].former);
      const ctx = document
        .getElementById("personalSocialChart")
        .getContext("2d");

      new Chart(ctx, {
        type: "bar",
        data: {
          labels,
          datasets: [
            {
              label: "Yes",
              data: yesData,
              backgroundColor: "#4fd1c5",
              borderRadius: 5,
            },
            {
              label: "No",
              data: noData,
              backgroundColor: "#f56565",
              borderRadius: 5,
            },
            {
              label: "Former",
              data: frmData,
              backgroundColor: "#f6ad55",
              borderRadius: 5,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "top",
            },
            tooltip: {
              enabled: true,
            },
          },
          scales: {
            x: {
              stacked: true,
              ticks: {
                font: {
                  size: 12,
                  weight: "bold",
                },
                color: "#2a4365",
              },
              grid: {
                display: false,
              },
            },
            y: {
              stacked: true,
              beginAtZero: true,
              title: {
                display: true,
                text: "Number of Clients",
                font: {
                  size: 14,
                  weight: "bold",
                },
              },
            },
          },
        },
      });
    })
    .catch((err) => console.error("Error loading personal social data:", err));
});
//=====
document.addEventListener("DOMContentLoaded", function () {
  fetch("dashboard.dbf/female_health_graph.php")
    .then((res) => res.json())
    .then((data) => {
      const labels = Object.keys(data);
      const regularityData = labels.map((label) => data[label]["regular"]);
      const irregularityData = labels.map((label) => data[label]["irregular"]);
      const yesData = labels.map((label) => data[label]["yes"]);
      const noData = labels.map((label) => data[label]["no"]);

      const ctx = document.getElementById("femaleHealthChart").getContext("2d");

      new Chart(ctx, {
        type: "bar",
        data: {
          labels,
          datasets: [
            {
              label: "Regular",
              data: regularityData,
              backgroundColor: "#4fd1c5",
              borderRadius: 5,
            },
            {
              label: "Irregular",
              data: irregularityData,
              backgroundColor: "#f56565",
              borderRadius: 5,
            },
            {
              label: "Yes",
              data: yesData,
              backgroundColor: "#f6ad55",
              borderRadius: 5,
            },
            {
              label: "No",
              data: noData,
              backgroundColor: "#9b2c2c",
              borderRadius: 5,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "top",
            },
            tooltip: {
              enabled: true,
            },
          },
          scales: {
            x: {
              stacked: true,
              ticks: {
                font: {
                  size: 12,
                  weight: "bold",
                },
                color: "#2a4365",
              },
              grid: {
                display: false,
              },
            },
            y: {
              stacked: true,
              beginAtZero: true,
              title: {
                display: true,
                text: "Number of Clients",
                font: {
                  size: 14,
                  weight: "bold",
                },
              },
            },
          },
        },
      });
    })
    .catch((err) => console.error("Error loading female health data:", err));
});
//=====
document.addEventListener("DOMContentLoaded", function () {
  const chartContainer = document.getElementById("consultationgraph");
  const yearSelector = document.getElementById("yearSelector");
  const canvas = document.getElementById("consultationChart");
  let chartInstance = null;

  function loadConsultationChart(year) {
    fetch(`dashboard.dbf/consultation_graph.php?year=${year}`)
      .then((response) => {
        if (!response.ok) throw new Error("Failed to fetch data");
        return response.json();
      })
      .then((data) => {
        if (!Array.isArray(data) || data.length !== 12) {
          console.warn("Data incomplete, defaulting to zeros");
          data = Array(12).fill(0);
        }

        const ctx = canvas.getContext("2d");

        if (chartInstance) {
          chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
          type: "bar",
          data: {
            labels: [
              "Jan",
              "Feb",
              "Mar",
              "Apr",
              "May",
              "Jun",
              "Jul",
              "Aug",
              "Sep",
              "Oct",
              "Nov",
              "Dec",
            ],
            datasets: [
              {
                label: `Consultations in ${year}`,
                data: data,
                backgroundColor: [
                  "#4fd1c5",
                  "#63b3ed",
                  "#fbd38d",
                  "#b794f4",
                  "#ed8936",
                  "#9b2c2c",
                  "#f56565",
                  "#48bb78",
                  "#4299e1",
                  "#ed64a6",
                  "#ecc94b",
                  "#a0aec0",
                ],

                barPercentage: 0.7,
                categoryPercentage: 0.7,
                maxBarThickness: 100,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: (context) =>
                    `${context.dataset.label}: ${context.raw}`,
                },
              },
            },
            scales: {
              y: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: "Number of Consultations",
                  font: { weight: "bold", size: 14 },
                },
                ticks: { precision: 0 },
              },
              x: {
                ticks: {
                  color: "#2a4365",
                  font: { weight: "bold", size: 12 },
                },
                grid: { offset: true },
                categoryPercentage: 0.6,
                barPercentage: 1.7,
              },
            },
          },
        });
      })
      .catch((error) => {
        console.error("Error:", error);
        chartContainer.innerHTML =
          '<p class="error">Failed to load chart data.</p>';
      });
  }

  // Initial chart load
  loadConsultationChart(yearSelector.value);

  // Load chart on year change
  yearSelector.addEventListener("change", () => {
    loadConsultationChart(yearSelector.value);
  });
});

//=====
