document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Evita que o formulário seja enviado de maneira tradicional

    let form = document.getElementById('loginForm');
    let formData = new FormData(form);

    let data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    let jsonData = JSON.stringify(data);

    let xhr = new XMLHttpRequest();
    xhr.open('POST', 'authenticate.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');

    // Mostrar a progress bar
    let loginButton = document.getElementById('loginButton');
    let progressBar = document.getElementById('progressBar');
    loginButton.classList.add('loading');

    xhr.onload = function() {
        loginButton.classList.remove('loading');

        if (xhr.status === 200) {
            let response = JSON.parse(xhr.responseText);
            if (response.success) {
                window.location.href = 'login.php';
            } else {
                document.getElementById('error-message').textContent = response.message;
            }
        } else {
            document.getElementById('error-message').textContent = 'Erro no servidor. Tente novamente mais tarde.';
        }
    };

    xhr.send(jsonData);
});

