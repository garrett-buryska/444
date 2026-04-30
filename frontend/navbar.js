const logout = async () => {
  await fetch("../backend/auth.php?action=logout");
  window.location.href = "login.html";
};

// Checks if user is logged in or not

async function checkAuth() {
  try {
    const response = await fetch("../backend/auth.php");
    const data = await response.json();

    if (data.status !== "success") {
      window.location.href = "login.html";
    }
  } catch (error) {
    window.location.href = "login.html";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const navbarContainer = document.getElementById("navbar");
  checkAuth();

  // The HTML for the navbar layout dependent on screen size
  if (navbarContainer) {
    navbarContainer.innerHTML = `
    <div class="navbar">
        <a href="#" class="nav-brand">Jim's Gym</a>

        <input type="checkbox" id="nav-toggle" class="nav-toggle" />

        <label for="nav-toggle" class="nav-toggle-hamburger" aria-label="Toggle navigation menu">
          <span></span>
          <span></span>
          <span></span>
        </label>

        <ul class="nav-links">
            <li><a href="profile.html">My Profile</a></li>
            <li><a href="activities-max.html">Set Maxes</a></li>
            <li><a href="workouts.html">Start a Workout</a></li>
            <li><a href="history.html">Previous Workouts</a></li>
            <button class="logout-link" onClick="logout()">Logout</button>
        </ul>
    </div>
    `;
  }
});
