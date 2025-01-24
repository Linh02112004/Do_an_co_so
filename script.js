document.addEventListener('DOMContentLoaded', () => {
    const arrow = document.getElementById('arrowDown');
    const dropdown = document.getElementById('dropdown');
    const userMenu = document.getElementById('userMenu');
    const userNameElement = document.getElementById('userName');
    const createEventLink = document.getElementById('createEventLink');
    const loginLink = document.getElementById('loginLink');
    const registerLink = document.getElementById('registerLink');

    // Hiển thị thông tin người dùng nếu đã đăng nhập
    if (sessionStorage.getItem('loggedIn')) {
        const userName = "Người dùng"; // Thay bằng tên người dùng đã đăng nhập
        userNameElement.innerText = userName;
        userMenu.style.display = 'flex';
        loginLink.style.display = 'none';
        registerLink.style.display = 'none';
        createEventLink.style.display = 'block';
    }

    arrow.addEventListener('click', (event) => {
        event.stopPropagation(); // Ngăn chặn sự kiện click nổi lên
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    });

    // Ẩn menu khi nhấn ra ngoài
    document.addEventListener('click', () => {
        dropdown.style.display = 'none';
    });

    // Lấy dữ liệu sự kiện từ server
    fetch('events.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('eventCount').innerText = data.length;

            const completed = data.filter(event => event.raised_amount >= event.target_amount).length;
            const notStarted = data.filter(event => event.raised_amount === 0).length;
            const ongoing = data.length - completed - notStarted;

            const ctx = document.getElementById('eventChart').getContext('2d');
            const eventChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Hoàn thành', 'Chưa bắt đầu', 'Đang diễn ra'],
                    datasets: [{
                        data: [completed, notStarted, ongoing],
                        backgroundColor: ['#4CAF50', '#FFC107', '#2196F3'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ' + context.raw;
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Hiển thị danh sách sự kiện
            const eventsList = document.getElementById('eventsList');
            data.forEach(event => {
                const div = document.createElement('div');
                div.innerHTML = `<h3>${event.title}</h3><p>${event.description}</p>`;
                eventsList.appendChild(div);
            });
        });
});