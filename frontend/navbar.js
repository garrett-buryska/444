const logout = () => {
  localStorage.removeItem("gymUser");
  window.location.href = "login.html";
};

document.addEventListener("DOMContentLoaded", () => {
  const navbarContainer = document.getElementById("navbar");
  if (navbarContainer) {
    navbarContainer.innerHTML = `
    <div class="navbar">
        <a href="#" class="nav-brand">Jim's Gym</a>

        <ul class="nav-links">
          <li><a href="profile.html">My Profile</a></li>
          <li><a href="start.html">Start a Workout</a></li>
          <li><a href="history.html">Previous Workouts</a></li>
          <button class="logout-link" onClick="logout()">Logout</button>
        </ul>
    </div>
    `;
  }
});
