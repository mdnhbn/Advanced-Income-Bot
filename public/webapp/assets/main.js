document.addEventListener("DOMContentLoaded", () => {
    const tg = window.Telegram.WebApp;
    tg.expand(); // Expand the web app window

    const params = new URLSearchParams(window.location.search);
    const taskUrl = params.get('url');
    const timerDuration = parseInt(params.get('timer'), 10) || 30;

    const taskFrame = document.getElementById('task-frame');
    const timerElement = document.getElementById('timer');
    const claimButton = document.getElementById('claim-button');

    if (taskUrl) {
        taskFrame.src = taskUrl;
    }

    let timeLeft = timerDuration;
    timerElement.textContent = timeLeft;

    const interval = setInterval(() => {
        timeLeft--;
        timerElement.textContent = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(interval);
            timerElement.parentElement.innerHTML = "Claim Reward";
            claimButton.disabled = false;
            claimButton.classList.add('active');
        }
    }, 1000);

    claimButton.addEventListener('click', () => {
        if (!claimButton.disabled) {
            // Send data back to the bot that the task is complete
            tg.sendData(JSON.stringify({ status: 'completed', taskId: params.get('taskId') }));
            tg.close();
        }
    });

    // Handle user leaving the page early
    window.addEventListener('beforeunload', (event) => {
        if (timeLeft > 0) {
            tg.sendData(JSON.stringify({ status: 'failed', reason: 'closed_early' }));
        }
    });
});
