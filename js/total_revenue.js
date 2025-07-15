document.addEventListener("DOMContentLoaded", () => {
  const revenueElement = document.getElementById("totalRevenue");

  fetch("/EZMartOrderingSystem/api/total_revenue.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        revenueElement.innerText = "₱" + data.totalRevenue;
      } else {
        throw new Error(data.error || "Unknown error");
      }
    })
    .catch((error) => {
      console.error("Error fetching revenue:", error);
      revenueElement.innerText = "₱0.00";
      revenueElement.style.color = "#999"; // Visual indicator of error
    });
});
