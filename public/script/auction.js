function updateCountdown() {
    document.querySelectorAll(".auction-card").forEach(card => {
        let endTime = new Date(card.getAttribute("data-end-time")).getTime();
        let countdownEl = card.querySelector(".countdown");

        function refreshTimer() {
            let now = new Date().getTime();
            let timeLeft = endTime - now;

            if (timeLeft > 0) {
                let days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                let hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                countdownEl.innerHTML = ` <i class="fa-solid fa-clock"></i> Time left : ${days}d ${hours}h ${minutes}m ${seconds}s`;
                countdownEl.style.color = "#87878B";
            } else {
                countdownEl.innerHTML = "Auction Ended";
                countdownEl.style.color = "red";
            }
        }

        refreshTimer();
        setInterval(refreshTimer, 1000);
    });
}
function showBidInput(auctionId) {
    const allBidInputs = document.querySelectorAll('.bid-input-container');
    allBidInputs.forEach(input => {
        input.style.display = 'none';
    });

    const allPlaceBidButtons = document.querySelectorAll('.bid');
    allPlaceBidButtons.forEach(button => {
        button.style.display = 'none';
    });

    const allDetailsButtons = document.querySelectorAll('.details');
    allDetailsButtons.forEach(button => {
        button.style.display = 'none';
    });

    const bidInputContainer = document.querySelector(`#bid-input-container-${auctionId}`);
    bidInputContainer.style.display = 'block';
}
function confirmDelete(auctionId) {
    const verificationInput = document.getElementById('delete-verification-' + auctionId);
    const deleteButton = document.getElementById('delete-button-' + auctionId);

    if (verificationInput.value === 'delete ' + auctionId) {
        deleteButton.disabled = false;
        return true;
    } else {
        alert('Please type "delete {{ auction.label }}" to confirm deletion.');
        deleteButton.disabled = true;
        return false;
    }
}
document.addEventListener("DOMContentLoaded", updateCountdown);
