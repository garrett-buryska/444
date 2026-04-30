document.addEventListener("DOMContentLoaded", () => {
  fetch("../backend/get-activities.php")
    .then((response) => response.json())
    .then((activities) => {
      const container = document.getElementById("activity-body");

      activities.forEach((act) => {
        let label = act.set_type;
        if (act.set_type === "reps") label = "lbs";
        if (act.set_type === "body") label = "reps";

        const savedValue = act.max_value !== null ? act.max_value : "";

        const div = document.createElement("div");
        div.className = "activity-item";
        div.setAttribute("data-name", act.activity_name.toLowerCase());

        div.innerHTML = `
                            <span>${act.activity_name}</span>
                            <div class="input-group">
                                <input type="number" 
                                       class="max-input" 
                                       data-activity="${act.activity_name}" 
                                       value="${savedValue}" 
                                       placeholder="0">
                                <span class="type-label">${label}</span>
                            </div>
                        `;
        container.appendChild(div);
      });

      attachListeners();
    })
    .catch((err) => console.error("Error loading activities:", err));
});

function filterActivities() {
  const query = document.getElementById("search-bar").value.toLowerCase();
  const items = document.querySelectorAll(".activity-item");

  items.forEach((item) => {
    const name = item.getAttribute("data-name");
    if (name.includes(query)) {
      item.classList.remove("hidden");
    } else {
      item.classList.add("hidden");
    }
  });
}

function attachListeners() {
  document.querySelectorAll(".max-input").forEach((input) => {
    input.addEventListener("input", function () {
      const element = this;
      const activity = element.dataset.activity;
      const value = element.value;

      fetch("../backend/save-max.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ activity: activity, max_value: value }),
      })
        .then((response) => response.json())
        .then((data) => {
          element.style.borderColor = data.success ? "green" : "red";
        })
        .catch((err) => {
          element.style.borderColor = "red";
        });
    });
  });
}
