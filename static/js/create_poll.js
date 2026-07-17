let currentOptionType = 'text';

document.addEventListener('DOMContentLoaded', function() {
    checkSession();
    setupEventListeners();
    initOptionType();
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

function initOptionType() {
    document.querySelector('.type-option[data-type="text"]').classList.add('active');
    updateOptionPlaceholders();
}

function switchOptionType(type) {
    currentOptionType = type;
    
    document.querySelectorAll('.type-option').forEach(el => el.classList.remove('active'));
    document.querySelector('.type-option[data-type="' + type + '"]').classList.add('active');
    
    if (type === 'image') {
        convertOptionsToImage();
    } else {
        convertOptionsToText();
    }
}

function updateOptionPlaceholders() {
    const container = document.getElementById('options-container');
    const inputs = container.querySelectorAll('input[type="text"]');
    
    inputs.forEach((input, index) => {
        if (currentOptionType === 'text') {
            input.placeholder = `请输入第${index + 1}项（35个字以内）`;
        } else {
            input.placeholder = '请输入选项文字';
        }
    });
}

function convertOptionsToImage() {
    const container = document.getElementById('options-container');
    const items = container.querySelectorAll('.option-item');
    
    items.forEach((item, index) => {
        const textInput = item.querySelector('input[type="text"]');
        const textValue = textInput ? textInput.value : '';
        
        const newItem = document.createElement('div');
        newItem.className = 'image-option-item';
        newItem.innerHTML = `
            <div class="image-upload-area" onclick="triggerImageUpload(this)">
                <input type="file" class="image-file" accept="image/*" onchange="handleImageUpload(this)" style="display: none;">
                <span class="upload-icon">+</span>
                <span class="upload-text">添加图片</span>
            </div>
            <input type="text" class="option-input" placeholder="请输入选项文字" maxlength="35" value="${textValue}">
            <button type="button" class="btn btn-danger remove-option">删除</button>
        `;
        
        item.parentNode.replaceChild(newItem, item);
        setupRemoveOption(newItem.querySelector('.remove-option'));
    });
}

function convertOptionsToText() {
    const container = document.getElementById('options-container');
    const items = container.querySelectorAll('.image-option-item');
    
    items.forEach((item, index) => {
        const textInput = item.querySelector('input[type="text"]');
        const textValue = textInput ? textInput.value : '';
        
        const newItem = document.createElement('div');
        newItem.className = 'option-item';
        newItem.innerHTML = `
            <input type="text" class="option-input" placeholder="请输入第${index + 1}项（35个字以内）" required maxlength="35" value="${textValue}">
            <button type="button" class="btn btn-danger remove-option">删除</button>
        `;
        
        item.parentNode.replaceChild(newItem, item);
        setupRemoveOption(newItem.querySelector('.remove-option'));
    });
}

function triggerImageUpload(area) {
    area.querySelector('.image-file').click();
}

function handleImageUpload(input) {
    const file = input.files[0];
    if (!file) return;
    
    const area = input.parentElement;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            const maxWidth = 300;
            const maxHeight = 300;
            let width = img.width;
            let height = img.height;
            
            if (width > maxWidth) {
                height = Math.round((height * maxWidth) / width);
                width = maxWidth;
            }
            if (height > maxHeight) {
                width = Math.round((width * maxHeight) / height);
                height = maxHeight;
            }
            
            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);
            
            const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.7);
            
            area.classList.add('has-image');
            area.innerHTML = `
                <img src="${compressedDataUrl}" alt="选项图片">
                <input type="file" class="image-file" accept="image/*" onchange="handleImageUpload(this)" style="display: none;">
            `;
        };
        img.src = e.target.result;
    };
    
    reader.readAsDataURL(file);
}

function setupEventListeners() {
    document.getElementById('add-option-btn').addEventListener('click', function() {
        const container = document.getElementById('options-container');
        const optionCount = container.querySelectorAll('.option-item, .image-option-item').length;
        
        if (currentOptionType === 'text') {
            const div = document.createElement('div');
            div.className = 'option-item';
            div.innerHTML = `
                <input type="text" class="option-input" placeholder="请输入第${optionCount + 1}项（35个字以内）" required maxlength="35">
                <button type="button" class="btn btn-danger remove-option">删除</button>
            `;
            container.appendChild(div);
            setupRemoveOption(div.querySelector('.remove-option'));
        } else {
            const div = document.createElement('div');
            div.className = 'image-option-item';
            div.innerHTML = `
                <div class="image-upload-area" onclick="triggerImageUpload(this)">
                    <input type="file" class="image-file" accept="image/*" onchange="handleImageUpload(this)" style="display: none;">
                    <span class="upload-icon">+</span>
                    <span class="upload-text">添加图片</span>
                </div>
                <input type="text" class="option-input" placeholder="请输入选项文字" maxlength="35">
                <button type="button" class="btn btn-danger remove-option">删除</button>
            `;
            container.appendChild(div);
            setupRemoveOption(div.querySelector('.remove-option'));
        }
    });

    document.querySelectorAll('.remove-option').forEach(setupRemoveOption);

    document.getElementById('is_multiple').addEventListener('change', function() {
        const container = document.getElementById('max-options-container');
        container.style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('create-poll-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const options = [];
        const container = document.getElementById('options-container');
        
        if (currentOptionType === 'text') {
            const items = container.querySelectorAll('.option-item');
            items.forEach(item => {
                const textInput = item.querySelector('input[type="text"]');
                if (textInput && textInput.value.trim()) {
                    options.push({
                        text: textInput.value.trim(),
                        image: null
                    });
                }
            });
        } else {
            const items = container.querySelectorAll('.image-option-item');
            items.forEach(item => {
                const textInput = item.querySelector('input[type="text"]');
                const imageArea = item.querySelector('.image-upload-area');
                const img = imageArea.querySelector('img');
                
                options.push({
                    text: textInput ? textInput.value.trim() : '',
                    image: img ? img.src : null
                });
            });
        }
        
        if (options.length < 2) {
            alert('至少需要两个有效选项');
            return;
        }

        const formData = {
            title: document.getElementById('title').value,
            description: document.getElementById('description').value,
            topic: document.getElementById('topic').value,
            option_type: currentOptionType,
            options: options,
            is_multiple: document.getElementById('is_multiple').checked ? 1 : 0,
            max_options: document.getElementById('is_multiple').checked ? parseInt(document.getElementById('max_options').value) : 1,
            end_time: document.getElementById('end_time').value || null,
            is_anonymous: document.getElementById('is_anonymous').checked ? 1 : 0
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
        const optionCount = container.querySelectorAll('.option-item, .image-option-item').length;
        
        if (optionCount > 2) {
            this.parentElement.remove();
        } else {
            alert('至少需要两个选项');
        }
    });
}
