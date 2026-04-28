async function checkAuth() {
  try {
    const response = await fetch("../backend/auth.php");
    const data = await response.json();

    if (data.status === "success") {
      window.location.href = "workouts.html";
    }
  } catch (error) {
    window.location.href = "login.html";
  }
}

checkAuth();
