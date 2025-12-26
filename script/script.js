
const userType = document.getElementById("userType");
const coachFields = document.getElementById("coachFields");

if (userType && coachFields) {
    userType.addEventListener("change", function () {
        coachFields.style.display = (this.value === "coach") ? "block" : "none";
    });
}

function showSection(sectionName) {
    const allSections = document.querySelectorAll(".dashboard-section");
    allSections.forEach((el) => {
        el.style.display = "none";
    });

    const target = document.getElementById(sectionName + "Section");
    if (target) target.style.display = "block";

    const sidebarLinks = document.querySelectorAll(".sidebar-link");
    sidebarLinks.forEach((link) => link.classList.remove("active"));

    sidebarLinks.forEach((link) => {
        const onclick = link.getAttribute("onclick") || "";
        if (onclick.includes("showSection('" + sectionName + "')") || onclick.includes('showSection("' + sectionName + '")')) {
            link.classList.add("active");
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("overviewSection")) {
        showSection("overview");
    } else {
        const first = document.querySelector(".dashboard-section");
        if (first && first.id && first.id.endsWith("Section")) {
            showSection(first.id.replace("Section", ""));
        }
    }
});

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = "flex";
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = "none";
}

document.addEventListener("click", function (e) {
    if (e.target.classList.contains("modal")) {
        e.target.style.display = "none";
    }

});

function viewCoachProfileModal(coachId) {
    const contentDiv = document.getElementById("coachModalContent" + coachId);
    const modal = document.getElementById("coachModal");
    const modalContent = document.getElementById("modalContent");

    if (!contentDiv || !modal || !modalContent) return;

    modalContent.innerHTML = contentDiv.innerHTML;
    modal.style.display = "flex";
}

document.addEventListener("click", function(e) {
    const modal = document.getElementById("coachModal");
    if (!modal) return;

    if (e.target === modal) {
        modal.style.display = "none";
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const tagsContainer = document.getElementById("tags");
    const hiddenInput = document.getElementById("hiddenInput");
    const choices = document.querySelectorAll(".choice");

    if (!tagsContainer || !hiddenInput || choices.length === 0) return;

    let selected = [];

    function syncHidden() {
        hiddenInput.value = selected.join(", ");
    }

    function renderTags() {
        tagsContainer.innerHTML = "";

        selected.forEach((value) => {
            const tag = document.createElement("span");
            tag.className = "tag";
            tag.style.display = "inline-flex";
            tag.style.alignItems = "center";
            tag.style.gap = "8px";
            tag.style.padding = "6px 10px";
            tag.style.margin = "5px";
            tag.style.borderRadius = "20px";
            tag.style.background = "#F5E6D3";
            tag.style.color = "#333";
            tag.style.fontWeight = "600";
            tag.textContent = value;

            const removeBtn = document.createElement("button");
            removeBtn.type = "button";
            removeBtn.textContent = "×";
            removeBtn.style.border = "none";
            removeBtn.style.background = "transparent";
            removeBtn.style.cursor = "pointer";
            removeBtn.style.fontSize = "18px";
            removeBtn.style.lineHeight = "1";

            removeBtn.addEventListener("click", () => {
                selected = selected.filter((v) => v !== value);
                // update choice UI
                choices.forEach((c) => {
                    if ((c.dataset.value || "").trim() === value) {
                        c.classList.remove("active");
                    }
                });
                renderTags();
                syncHidden();
            });

            tag.appendChild(removeBtn);
            tagsContainer.appendChild(tag);
        });
    }

    choices.forEach((choice) => {
        choice.addEventListener("click", () => {
            const value = (choice.dataset.value || "").trim();
            if (!value) return;

            if (selected.includes(value)) {
                selected = selected.filter((v) => v !== value);
                choice.classList.remove("active");
            } else {
                selected.push(value);
                choice.classList.add("active");
            }

            renderTags();
            syncHidden();
        });
    });

    if (hiddenInput.value.trim() !== "") {
        selected = hiddenInput.value.split(",").map(s => s.trim()).filter(Boolean);

        choices.forEach((c) => {
            const v = (c.dataset.value || "").trim();
            if (selected.includes(v)) c.classList.add("active");
        });

        renderTags();
        syncHidden();
    }
});

