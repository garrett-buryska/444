document.addEventListener("DOMContentLoaded", () => {
  const workoutList = document.getElementById("workoutList");

  fetch(`../backend/get-history.php`)
    .then((response) => response.json())
    .then((data) => {
      workoutList.innerHTML = ""; // clear default workoutList (loading message)

      if (data.status === "success") {
        if (data.data.length === 0) {
          workoutList.innerHTML =
            '<p class="no-workouts">You have not completed any workouts yet. Time to hit the gym!</p>';
          return;
        }

        data.data.forEach((workout) => {
          const date = new Date(workout.time_stamp).toLocaleString();
          const type = workout.workout_type;
          const exercises = workout.exercises || "No exercises logged";

          const card = document.createElement("div");
          card.className = "history-card";
          card.style.cursor = "pointer"; // clicking takes to workout
          card.onclick = () =>
            (window.location.href = `current-workout.html?id=${workout.workout_Id}`);
          card.innerHTML = `
                                <div class="history-header">
                                    <div class="history-type">${type}</div>
                                    <div class="history-date">${date}</div>
                                </div>
                                <div class="history-exercises">
                                    <strong>Exercises:</strong> ${exercises}
                                </div>
                            `;
          workoutList.appendChild(card);
        });
      } else {
        workoutList.innerHTML = `<p class="error">Error: ${data.message}</p>`;
      }
    })
    .catch(() => {
      workoutList.innerHTML =
        '<p class="error">Failed to load workout history.</p>';
    });
});
