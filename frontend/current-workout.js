// get workout ID from URL query parameters
const urlParams = new URLSearchParams(window.location.search);
const workoutId = urlParams.get("id");
const display = document.getElementById("workout-display");

if (!workoutId) {
    display.innerHTML =
        "<div class='card'><h3>Error</h3><p>No workout ID provided.</p></div>";
} else {
    fetchWorkoutData(workoutId);
}

// Toggle display of exercise info
async function fetchWorkoutData(id) {
    try {
        // Fetch workout details from backend
        const response = await fetch(`../backend/get-workout-details.php?id=${id}`);
        const data = await response.json();

        if (data.error) throw new Error(data.error);

        if (data.length === 0) {
            display.innerHTML = "<div class='card'><p>No exercises found.</p></div>";
            return;
        }

        display.innerHTML = "";

        // Loop through each lift and create a card for it
        data.forEach((lift, index) => {
            const card = document.createElement("div");
            card.className = "card";
            const infoId = `info-${index}`;

            let setsHTML = "";
            lift.sets.forEach((set) => {
                const isChecked = set.completed ? "checked" : "";
                const doneClass = set.completed ? "done" : "";

                // Display logic: "10 Reps at 50 lbs"
                let displayText = set.set_text || "";
                if (set.weight && set.weight > 0) {
                    displayText += ` at ${set.weight} lbs`;
                }

                // Create HTML for each set with checkbox and display text
                setsHTML += `
                <div class="set-row ${doneClass}">
                    <input type="checkbox" data-lift-id="${lift.liftID}" data-set-number="${set.set_number}" onchange="this.parentElement.classList.toggle('done')" ${isChecked}>
                    <span class="set-label">Set ${set.set_number}</span>
                    <span class="set-text-display">${displayText}</span>
                </div>
              `;
            });

            // Create card HTML with exercise info and sets
            card.innerHTML = `
                <h3>${lift.activity_name} 
                    <button class="info-btn" onclick="toggleInfo('${infoId}')">i</button>
                </h3>
                <div id="${infoId}" class="info-container">
                    <p>${lift.description || "No description available."}</p>
                    ${lift.youtube_link ? `<a href="${lift.youtube_link}" target="_blank">Watch Tutorial</a>` : "No video available."}
                </div>
                <p><strong>Category:</strong> ${lift.main_muscle_group ? lift.main_muscle_group.toUpperCase() : "N/A"}</p>
                <div class="sets-list">
                    ${setsHTML}
                </div>
            `;
            display.appendChild(card);
        });
    } catch (err) {
        display.innerHTML = `<div class='card'><p>Error loading workout: ${err.message}</p></div>`;
    }
}

// Function to toggle display of exercise info
function toggleInfo(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === "block" ? "none" : "block";
}

// Function to handle finishing the workout and sending data to backend
async function finishWorkout() {
    const button = document.querySelector(".btn-finish");
    button.disabled = true;
    button.textContent = "Finishing...";

    const setsPayload = [];
    const checkboxes = document.querySelectorAll('#workout-display input[type="checkbox"]');

    // Loop through checkboxes to build payload of completed sets
    checkboxes.forEach((cb) => {
        setsPayload.push({
            liftID: cb.dataset.liftId,
            set_number: cb.dataset.setNumber,
            completed: cb.checked,
        });
    });

    // Validate workout ID before sending data
    if (!workoutId) {
        alert("Workout ID is missing.");
        button.disabled = false;
        button.textContent = "Finish Workout";
        return;
    }

    // Send POST request to backend to save workout results
    try {
        const response = await fetch("../backend/finish-workout.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ workoutId: workoutId, sets: setsPayload }),
        });
        const result = await response.json();
        if (result.status === "success") {
            window.location.href = "workouts.html";
        } else {
            alert("Error: " + result.message);
            button.disabled = false;
            button.textContent = "Finish Workout";
        }
    } catch (error) {
        alert("An error occurred.");
        button.disabled = false;
        button.textContent = "Finish Workout";
    }
}