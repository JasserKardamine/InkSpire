
document.addEventListener("DOMContentLoaded", function() {
    const inputs = document.querySelectorAll(".confirm-input");

    inputs.forEach((input, index) => {
        input.addEventListener("input", (event) => {
            if (event.target.value.length === 1) {
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus(); 
                }
            }
        });

        input.addEventListener("keydown", (event) => {
            if (event.key === "Backspace" && input.value === "") {
                if (index > 0) {
                    inputs[index - 1].focus(); 
                }
            }
        });
    });
});