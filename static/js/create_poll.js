document.addEventListener('DOMContentLoaded', function() {
    checkSession();
    setupEventListeners();
});

function checkSession() {
    fetch('../api/check_session.php', { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                window.location.href = 'login.html';
                return;
            }
            document.getElementById('user-info').textContent = `欢迎, ${data.data.username}`;
        })
        .catch(() => {
            window.location.href = 'login.html';
        });
}

function setupEventListeners() {
    document.getElementById('logout-btn').addEventListener('click', function() {
        fetch('../api/login.php', { method: 'DELETE' })
            .then(() => {
                window.location.href = 'login.html';
            });
    });

    document.getElementById('add-option-btn').addEventListener('click', function() {
        const container = document.getElementById('options-container');
        const optionCount = container.querySelectorAll('.option-item').length;
        
        const div = document.createElement('div');
        div.className = 'option-item';
        div.innerHTML = `
            <input type="text" class="option-input" placeholder="选项 ${optionCount + 1}" required>
            <button type="button" class="btn btn-danger remove-option">删除</button>
        `;
        
        container.appendChild(div);
        setupRemoveOption(div.querySelector('.remove-option'));
    });

    document.querySelectorAll('.remove-option').forEach(setupRemoveOption);

    document.getElementById('is_multiple').addEventListener('change', function() {
        const container = document.getElementById('max-options-container');
        container.style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('create-poll-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const options = Array.from(document.querySelectorAll('.option-input'))
            .map(input => input.value.trim())
            .filter(value => value);
        
        if (options.length < 2) {
            alert('至少需要两个有效选项');
            return;
        }

        const formData = {
            title: document.getElementById('title').value,
            description: document.getElementById('description').value,
            topic: document.getElementById('topic').value,
            options: options,
            is_multiple: document.getElementById('is_multiple').checked ? 1 : 0,
            max_options: document.getElementById('is_multiple').checked ? parseInt(document.getElementById('max_options').value) : 1,
            end_time: document.getElementById('end_time').value || null
        };

        fetch('../api/create_poll.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData),
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('投票创建成功！');
                window.location.href = 'index.html';
            } else {
                alert('创建失败：' + data.message);
            }
        })
        .catch(error => {
            alert('创建失败：网络错误');
            console.error('Error:', error);
        });
    });
}

function setupRemoveOption(button) {
    button.addEventListener('click', function() {
        const container = document.getElementById('options-container');
        const optionCount = container.querySelectorAll('.option-item').length;
        
        if (optionCount > 2) {
            this.parentElement.remove();
        } else {
            alert('至少需要两个选项');
        }
    });
}