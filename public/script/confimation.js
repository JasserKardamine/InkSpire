document.addEventListener("DOMContentLoaded", function() {
    const inputs = document.querySelectorAll(".confirm-input");

    inputs.forEach((input, index) => {
        input.addEventListener("input", (event) => {
            if (event.target.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener("keydown", (event) => {
            if (event.key === "Backspace" && input.value === "" && index > 0) {
                inputs[index - 1].focus();
            }
        });

        input.addEventListener("paste", (event) => {

            const pasteData = event.clipboardData.getData("text");
            let confirmationbutton = document.getElementById('confiramtion-button') ; 
            if (pasteData.length === inputs.length) {
                inputs.forEach((input, i) => {
                    input.value = pasteData[i] || ""; 
                });
                inputs[inputs.length - 1].focus();
            }
            confirmationbutton.click();
        });
    });
});
