document.addEventListener('DOMContentLoaded', () => {
    const arrow = document.getElementById('arrowDown');
    const dropdown = document.getElementById('dropdown');
    const userMenu = document.getElementById('userMenu');
    const userNameElement = document.getElementById('userName');
    const createEventLink = document.getElementById('createEventLink');
    const loginLink = document.getElementById('loginLink');
    const registerLink = document.getElementById('registerLink');
    const eventsList = document.getElementById('eventsList');

    initUserMenu();
    setupEventListeners();
    fetchData();

    function initUserMenu() {
        if (sessionStorage.getItem('loggedIn')) {
            userMenu.style.display = 'flex';
            loginLink.style.display = 'none';
            registerLink.style.display = 'none';
            createEventLink.style.display = 'block';
        }
    }

    function setupEventListeners() {
        arrow.addEventListener('click', (event) => {
            event.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', () => {
            dropdown.style.display = 'none';
        });
    }

    function fetchData() {
        fetch('events.php')
            .then(response => response.json())
            .then(data => {
                displayUserName(data.user_name);
                displayEvents(data.events);
            })
            .catch(error => console.error('Lỗi khi tải dữ liệu sự kiện:', error));
    }

    function displayUserName(userName) {
        if (userName) {
            userNameElement.innerText = `Xin chào, ${userName}`;
        } else {
            userNameElement.innerText = 'Xin chào!';
        }
    }

    function displayEvents(events) {
        eventsList.innerHTML = ''; // Xóa danh sách sự kiện hiện tại
        events.forEach(event => {
            const div = document.createElement('div');
            div.innerHTML = `<h3>${event.title}</h3><p>${event.description}</p>`;
            eventsList.appendChild(div);
        });
    }
});