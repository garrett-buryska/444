// form inputs
const usernameInput = document.getElementById("username");
const nameInput = document.getElementById("name");
const imgUrlInput = document.getElementById("img_url");
const profileImagePreview = document.getElementById(
  "profileImagePreview",
);
const dobInput = document.getElementById("dob");
const heightInput = document.getElementById("height");
const weightInput = document.getElementById("weight");
const skillButtons = Array.from(
  document.querySelectorAll(".skill-chip"),
);
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
    button.classList.toggle("active", isSelected); // add active class when active
  });
}

// Allows user to set profile images via image URl

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

// Loads the profile information from the database

async function loadProfile() {
  try {
    const response = await fetch("../backend/profile.php");
    const data = await response.json();

    if (data.status !== "success") {
      setMessage(data.message || "Unable to load profile.", "error");
      return;
    }

    const profile = data.profile;

    // Retreives username, name, img_url, DoB, height, weight, and skill level from the database
    usernameInput.value = profile.username || "";
    nameInput.value = profile.name || "";
    setProfileImage(profile.img_url || "");
    dobInput.value = profile.DoB || "";
    heightInput.value = profile.height || "";
    weightInput.value = profile.weight || "";
    setSelectedSkill(profile.skill_level || "");
    setMessage("");
  } catch (error) {
    setMessage(
      "An error occurred while loading the profile.",
      "error",
    );
  }
}

// Updates the database when the user edits their profile

async function saveProfile() {
  try {
    const response = await fetch("../backend/profile.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
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

    // For profile fill with database imformation or leave blank

    const profile = data.profile;
    usernameInput.value = profile.username || "";
    nameInput.value = profile.name || "";
    setProfileImage(profile.img_url || "");
    dobInput.value = profile.DoB || "";
    heightInput.value = profile.height || "";
    weightInput.value = profile.weight || "";
    setSelectedSkill(profile.skill_level || "");
    setMessage(data.message || "Profile saved.", "success");
  } catch (error) {
    setMessage(
      "An error occurred while saving the profile.",
      "error",
    );
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
