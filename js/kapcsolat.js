const form = document.querySelector('form');
const result = document.querySelector('#result');

form.addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(form);
    const object = Object.fromEntries(formData);
    const json = JSON.stringify(object);
    result.innerHTML = "Kérjük várjon...";
    fetch('https://api.web3forms.com/submit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: json
    })
    .then(async (response) => {
        let jsonResponse = await response.json();
        if (response.status === 200) {
            result.innerHTML = `
                <div class="success-message">
                    <h2>Üzenet sikeresen elküldve!</h2>
                    <p>Köszönjük, hogy kapcsolatba léptél velünk.</p>
                </div>
            `;
        } else {
            result.innerHTML = `
                <div class="error-message">
                    <h2>Hiba történt!</h2>
                    <p>${jsonResponse.message}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.log(error);
        result.innerHTML = `
            <div class="error-message">
                <h2>Valami hiba történt!</h2>
                <p>Kérjük próbálja újra később.</p>
            </div>
        `;
    })
    .then(() => {
        form.reset();
        setTimeout(() => {
            result.style.display = "none";
        }, 3000);
    });
});