document.addEventListener('DOMContentLoaded', () => {
    const arrow = document.getElementById('arrowDown');
    const dropdown = document.getElementById('dropdown');
    const userMenu = document.getElementById('userMenu');
    const userNameElement = document.getElementById('userName');
    const createEventLink = document.getElementById('createEventLink');
    const loginLink = document.getElementById('loginLink');
    const registerLink = document.getElementById('registerLink');
    const eventsList = document.getElementById('eventsList');
    const searchBox = document.getElementById('searchBox');
    const searchButton = document.getElementById('searchButton');
    
    const completedEventsList = document.createElement('div');
    completedEventsList.classList.add('event-list');
    document.querySelector('main').appendChild(completedEventsList);

    searchButton.addEventListener('click', filterEvents);
    searchBox.addEventListener('input', resetSearch); // Tự động hiển thị toàn bộ khi xóa nội dung tìm kiếm
    
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
        userNameElement.innerText = userName ? `Xin chào, ${userName}` : 'Xin chào!';
    }

    function displayEvents(events) {
        eventsList.innerHTML = '';
        completedEventsList.innerHTML = '';
        
        events.forEach(event => {
            let progress = (event.raised_amount / event.target_amount) * 100;
            if (progress > 100) progress = 100;

            const div = document.createElement('div');
            div.classList.add('event-card');
            div.setAttribute('data-title', event.title.toLowerCase()); // Lưu tên sự kiện để tìm kiếm
            div.innerHTML = `
                <h3>${event.title}</h3>
                <p>${event.description}</p>
                <p><strong>Người tạo:</strong> ${event.creator_name}</p>
                <p><strong>Tiến độ:</strong> ${event.raised_amount} VNĐ / ${event.target_amount} VNĐ</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${progress}%;"></div>
                </div>
                <button class="donate-button" onclick="donate(${event.id})">Quyên góp</button>
            `;
            
            if (progress >= 100) {
                completedEventsList.appendChild(div);
            } else {
                eventsList.appendChild(div);
            }
        });
    }

    function filterEvents() {
        const searchValue = searchBox.value.toLowerCase();
        document.querySelectorAll('.event-card').forEach(card => {
            if (card.getAttribute('data-title').includes(searchValue)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function resetSearch() {
        if (searchBox.value.trim() === '') {
            document.querySelectorAll('.event-card').forEach(card => {
                card.style.display = 'block';
            });
        }
    }

    window.donate = function(eventId) {
        window.location.href = `event_detail.php?id=${eventId}`;
    };
});
