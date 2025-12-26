// fonction d'affichage des coachfields

const userType = document.getElementById("userType");
const coachFields = document.getElementById("coachFields");

userType.addEventListener("change", function () {
    if (this.value === "coach") {
        coachFields.style.display = "block";
    } else {
        coachFields.style.display = "none";
    }
});


// Fonction pour afficher les elements du dashboard d'un sportif

function showSection(sectionName) {

    const sections = ["overviewSection","mybookingsSection","findcoachSection","mycoachesSection","profileSection"];

    sections.forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.style.display = "none";
    });

    const targetId = sectionName + "Section";
    const target = document.getElementById(targetId);
    if (target) target.style.display = "block";

    const sidebarLinks = document.querySelectorAll(".sidebar-link");
    sidebarLinks.forEach((link) => link.classList.remove("active"));

    const activeLink = document.querySelector(
        `.sidebar-link[onclick*="showSection('${sectionName}')"]`
    );
    if (activeLink) activeLink.classList.add("active");
}
document.addEventListener("DOMContentLoaded", () => showSection("overview"));