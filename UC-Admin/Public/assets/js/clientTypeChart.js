document.addEventListener("DOMContentLoaded", () => {
  fetch("dashboard.dbf/clientTypeData.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        console.error(data.error);
        return;
      }

      // ✅ Define label mapping and colors
      const labelMap = {
        Student: "Students/Regular",
        Faculty: "Teaching Personnels",
        Personnel: "Non Teaching Personnels",
        Freshman: "Freshman/Applicants",
        NewPersonnel: "Newly Hired Personnels",
      };

      const colorMap = {
        Student: "#007bff", // Blue
        Freshman: "#2ecc71", // Green
        Faculty: "#17a2b8", // Teal
        Personnel: "#f39c12", // Amber
        NewPersonnel: "#9b59b6", // Purple
      };

      // ✅ Filter out any unknown types (not in colorMap)
      const filteredData = data.filter((item) => colorMap[item.ClientType]);

      // ✅ Map filtered data to labels, counts, and colors
      const labels = filteredData.map((item) => labelMap[item.ClientType]);
      const counts = filteredData.map((item) => item.count);
      const backgroundColors = filteredData.map(
        (item) => colorMap[item.ClientType]
      );

      // ✅ Draw the chart after the font is loaded
      document.fonts.ready.then(() => {
        const ctx = document.getElementById("clientTypeChart").getContext("2d");

        new Chart(ctx, {
          type: "pie",
          data: {
            labels: labels,
            datasets: [
              {
                label: "Number of Clients by Type",
                data: counts,
                backgroundColor: backgroundColors,
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
                  font: {
                    family: "Poppins",
                    size: 14,
                  },
                  color: "#333",
                },
              },
              title: {
                display: true,
                text: "Registered Patients Chart by Type",
                font: {
                  family: "Poppins",
                  size: 14,
                  weight: "bold",
                  style: "normal",
                },
                color: "#004a8f",
              },
            },
          },
        });
      });
    })
    .catch((error) => console.error("Error fetching data:", error));
});
