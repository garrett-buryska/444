const imgUrlInput = document.getElementById("img_url");

document
  .getElementById("createAccountForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();

    const usernameInput = document.getElementById("username").value;
    const passwordInput = document.getElementById("password").value;
    const nameInput = document.getElementById("name").value;
    const imgUrlVal = document.getElementById("img_url").value;
    const dobVal = document.getElementById("dob").value;
    const weightVal = parseFloat(document.getElementById("weight").value);
    const heightVal = parseFloat(document.getElementById("height").value);
    const skillInput = document.getElementById("skill").value;
    const messageDiv = document.getElementById("message");

    fetch("../backend/create-account.php", {
      method: "POST",
      body: JSON.stringify({
        username: usernameInput,
        password: passwordInput,
        name: nameInput,
        img_url: imgUrlVal,
        dob: dobVal,
        weight: weightVal,
        height: heightVal,
        skill: skillInput,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          messageDiv.className = "auth-message success";
          messageDiv.textContent =
            "Account created successfully! Redirecting to workouts...";
          setTimeout(() => {
            window.location.href = "login.html";
          }, 2000);
        } else {
          messageDiv.className = "auth-message error";
          messageDiv.textContent = data.message || "Registration failed.";
        }
      })
      .catch((error) => {
        messageDiv.className = "auth-message error";
        messageDiv.textContent =
          "An error occurred connecting to the server." + error.message;
      });
  });
