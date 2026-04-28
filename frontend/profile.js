const usernameInput = document.getElementById("username");
const nameInput = document.getElementById("name");
const imgUrlInput = document.getElementById("img_url");
const profileImagePreview = document.getElementById("profileImagePreview");
const dobInput = document.getElementById("dob");
const heightInput = document.getElementById("height");
const weightInput = document.getElementById("weight");
const skillButtons = Array.from(document.querySelectorAll(".skill-chip"));
const saveButton = document.querySelector(".profile-save-button");
const messageEl = document.getElementById("profileMessage");

let selectedSkillLevel = "";

function setMessage(text, type = "") {
  messageEl.textContent = text;
  messageEl.className = `profile-message ${type}`.trim();
}

function setSelectedSkill(level) {
  selectedSkillLevel = level || "";
  skillButtons.forEach((button) => {
    const isSelected = button.textContent === selectedSkillLevel;
    button.classList.toggle("active", isSelected);
  });
}

function setProfileImage(url) {
  const imageUrl = url || "";
  imgUrlInput.value = imageUrl;

  if (imageUrl) {
    profileImagePreview.src = imageUrl;
    profileImagePreview.style.display = "block";
  } else {
    profileImagePreview.removeAttribute("src");
    profileImagePreview.style.display = "none";
  }
}

async function loadProfile() {
  const username = localStorage.getItem("gymUser");

  if (!username) {
    setMessage("No logged-in user found. Please log in first.", "error");
    saveButton.disabled = true;
    return;
  }

  usernameInput.value = username;

  try {
    const response = await fetch(
      `../backend/profile.php?username=${encodeURIComponent(username)}`,
    );
    const data = await response.json();

    if (data.status !== "success") {
      setMessage(data.message || "Unable to load profile.", "error");
      return;
    }

    const profile = data.profile;
    nameInput.value = profile.name || "";
    setProfileImage(profile.img_url || "");
    dobInput.value = profile.DoB || "";
    heightInput.value = profile.height || "";
    weightInput.value = profile.weight || "";
    setSelectedSkill(profile.skill_level || "");
    setMessage("");
  } catch (error) {
    console.error(error);
    setMessage("An error occurred while loading the profile.", "error");
  }
}

async function saveProfile() {
  const username = usernameInput.value.trim();

  if (!username) {
    setMessage("Username is required.", "error");
    return;
  }

  try {
    const response = await fetch("../backend/profile.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        username,
        name: nameInput.value.trim(),
        img_url: imgUrlInput.value.trim(),
        DoB: dobInput.value,
        height: heightInput.value,
        weight: weightInput.value,
        skill_level: selectedSkillLevel,
      }),
    });

    const data = await response.json();

    if (data.status !== "success") {
      setMessage(data.message || "Unable to save profile.", "error");
      return;
    }

    const profile = data.profile;
    nameInput.value = profile.name || "";
    setProfileImage(profile.img_url || "");
    dobInput.value = profile.DoB || "";
    heightInput.value = profile.height || "";
    weightInput.value = profile.weight || "";
    setSelectedSkill(profile.skill_level || "");
    setMessage(data.message || "Profile saved.", "success");
  } catch (error) {
    console.error(error);
    setMessage("An error occurred while saving the profile.", "error");
  }
}

skillButtons.forEach((button) => {
  button.addEventListener("click", () => {
    setSelectedSkill(button.textContent);
  });
});

imgUrlInput.addEventListener("input", () => {
  setProfileImage(imgUrlInput.value.trim());
});

profileImagePreview.addEventListener("error", () => {
  profileImagePreview.style.display = "none";
});

saveButton.addEventListener("click", saveProfile);
loadProfile();
