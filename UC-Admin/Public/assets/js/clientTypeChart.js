document.addEventListener("DOMContentLoaded", () => {
  fetch("dashboard.dbf/clientTypeData.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        console.error(data.error);
        return;
      }

      const labels = data.map((item) => item.ClientType);
      const counts = data.map((item) => item.count);

      const ctx = document.getElementById("clientTypeChart").getContext("2d");
      new Chart(ctx, {
        type: "pie",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Number of Clients by Type",
              data: counts,
              backgroundColor: [
                "#2196f3", // Student
                "#4caf50", // Faculty
                "#ff9800", // Personnel
                "#9c27b0", // Freshman
                "#f44336", // New Personnel
                "#607d8b", // Default
              ],
              borderColor: "#fff",
              borderWidth: 2,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: "bottom",
              labels: {
                font: { size: 14 },
                color: "#333",
              },
            },
            title: {
              display: true,
              text: "Registered Clients by Type",
              font: { size: 18, weight: "bold" },
              color: "#004a8f",
            },
          },
        },
      });
    })
    .catch((error) => console.error("Error fetching data:", error));
});
