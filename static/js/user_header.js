function loadUserHeader() {
    fetch('../api/get_profile.php', {
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const user = data.data;
            const userInfo = document.getElementById('userInfo');
            if (userInfo) {
                userInfo.innerHTML = `
                    <div class="user-dropdown" onclick="toggleDropdown()">
                        <div class="user-avatar" id="userAvatar">
                            <img id="avatarImg" src="${user.avatar || ''}" alt="" ${user.avatar ? '' : 'style="display: none;"'}>
                            <span id="avatarText" ${user.avatar ? 'style="display: none;"' : ''}>${user.username ? user.username.charAt(0).toUpperCase() : '?'}</span>
                        </div>
                        <span>欢迎, <span id="username">${user.username}</span></span>
                        <span style="font-size: 12px;">▼</span>
                        <div class="user-dropdown-content" id="userDropdown">
                            <div class="dropdown-item" onclick="event.stopPropagation(); window.location.href='index.html'">
                                <span class="dropdown-item icon">🏠</span>
                                <span>返回首页</span>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-item" onclick="event.stopPropagation(); logout()">
                                <span class="dropdown-item icon">🚪</span>
                                <span>退出登录</span>
                            </div>
                        </div>
                    </div>
                `;
                userInfo.style.display = 'flex';
            }
            checkAdminStatus();
        }
    })
    .catch(error => {
        console.error('获取用户信息失败:', error);
    });
}

function checkAdminStatus() {
    fetch('../api/admin_login.php', {
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const adminBtn = document.getElementById('adminBtn');
            if (adminBtn) {
                adminBtn.style.display = 'block';
                localStorage.setItem('admin', JSON.stringify(data.admin));
            }
        }
    })
    .catch(error => {
        console.log('非管理员用户');
    });
}

function toggleDropdown() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('userDropdown');
    const userDropdown = document.querySelector('.user-dropdown');
    if (dropdown && userDropdown && !userDropdown.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});

function logout() {
    if (!confirm('确定要退出登录吗？')) {
        return;
    }
    fetch('../api/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ action: 'logout' })
    })
    .then(() => {
        fetch('../api/admin_login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ action: 'logout' })
        })
        .then(() => {
            localStorage.removeItem('user');
            localStorage.removeItem('admin');
            window.location.href = 'index.html';
        });
    });
}
