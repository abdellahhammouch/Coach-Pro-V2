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