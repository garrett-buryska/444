document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const usernameInput = document.getElementById("username").value;
  const passwordInput = document.getElementById("password").value;
  const messageDiv = document.getElementById("message");

  fetch("../backend/login.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      username: usernameInput,
      password: passwordInput,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        window.location.href = "workouts.html";
      } else {
        messageDiv.className = "auth-message error";
        messageDiv.textContent = data.message;
      }
    })
    .catch((error) => {
      messageDiv.className = "auth-message error";
      messageDiv.textContent = "An error occurred connecting to the server.";
    });
});
