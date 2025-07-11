// Menu Buttons
document.getElementById("logout-btn")?.addEventListener("click", function () {
    if (confirm("Are you sure you want to log out?"))
        window.location.href = "index.php";
});

document.getElementById("settings-btn")?.addEventListener("click", function () {
    window.location.href = "settings.html";
});

document.getElementById("support-btn")?.addEventListener("click", function () {
    window.location.href = "support.html";
});

document.getElementById("details-btn")?.addEventListener("click", function () {
    window.location.href = "profile_details.html";
});

// Profile Details Page
document.addEventListener("DOMContentLoaded", function () {
    const viewMode = document.getElementById("viewMode");
    const editMode = document.getElementById("editMode");
    const editBtn = document.getElementById("editBtn");
    const cancelBtn = document.getElementById("cancelBtn");
    const profileForm = document.getElementById("profileForm");

    loadUserData();

    editBtn.addEventListener("click", showEditMode);
    cancelBtn.addEventListener("click", showViewMode);
    profileForm.addEventListener("submit", saveProfile);

    function loadUserData() {
        fetch("get_profile.php")
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    document.getElementById("viewUsername").textContent = data.user.username;
                    document.getElementById("viewEmail").textContent = data.user.email;

                    document.getElementById("username").value = data.user.username;
                    document.getElementById("email").value = data.user.email;
                } else {
                    alert("Error loading profile: " + data.message);
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("Error loading profile data");
            });
    }

    function showEditMode() {
        viewMode.classList.add("hidden");
        editMode.classList.remove("hidden");
    }

    function showViewMode() {
        editMode.classList.add("hidden");
        viewMode.classList.remove("hidden");
    }

    function saveProfile(e) {
        e.preventDefault();
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirmPassword").value;

        if (password && password !== confirmPassword) {
            alert("Passwords do not match");
            return;
        }

        const formData = new FormData(profileForm);

        fetch("update_profile.php", {
            method: "POST",
            body: formData
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert("Profile updated successfully");
                    loadUserData();
                    showViewMode();
                } else {
                    alert("Error updating profile: " + data.message);
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("Error updating profile data");
            });
    }
});
