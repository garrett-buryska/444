const logout = async () => {
  try {
    await fetch("../backend/auth.php?action=logout", {
      credentials: "include",
    });
  } catch (error) {
    console.error("Error during logout:", error);
  }
  window.location.href = "login.html";
};

async function checkAuth() {
  const isAuthPage =
    window.location.pathname.includes("login.html") ||
    window.location.pathname.includes("register.html") ||
    window.location.pathname.includes("create-account.html");

  try {
    const response = await fetch("../backend/auth.php", {
      credentials: "include",
    });
    const data = await response.json();

    if (data.status !== "success" && !isAuthPage) {
      window.location.href = "login.html";
    } else if (data.status === "success" && isAuthPage) {
      window.location.href = "workouts.html";
    }
  } catch (error) {
    console.error("Error checking authentication:", error);
    if (!isAuthPage) {
      window.location.href = "login.html";
    }
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const navbarContainer = document.getElementById("navbar");
  checkAuth();
  if (navbarContainer) {
    navbarContainer.innerHTML = `
    <div class="navbar">
        <a href="#" class="nav-brand">Jim's Gym</a>

        <ul class="nav-links">
          <li><a href="profile.html">My Profile</a></li>
          <li><a href="workouts.html">Start a Workout</a></li>
          <li><a href="history.html">Previous Workouts</a></li>
          <button class="logout-link" onClick="logout()">Logout</button>
        </ul>
    </div>
    `;
  }
});
