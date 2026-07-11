document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const message = document.getElementById('message');
    
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline-block';
    submitBtn.disabled = true;
    message.style.display = 'none';
    
    fetch('../api/register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({
            username: username,
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
        submitBtn.disabled = false;
        
        message.style.display = 'block';
        if (data.status === 'success') {
            message.className = 'message success';
            message.textContent = data.message;
            
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
        } else {
            message.className = 'message error';
            message.textContent = data.message;
        }
    })
    .catch(error => {
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
        submitBtn.disabled = false;
        
        message.style.display = 'block';
        message.className = 'message error';
        message.textContent = '网络错误，请稍后重试';
    });
});
