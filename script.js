document.addEventListener('DOMContentLoaded', () => {
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

            const legend = document.getElementById('legend');
            legend.innerHTML = `
                <ul>
                    <li><span style="color:#4CAF50;">&#9679;</span> Hoàn thành: ${completed}</li>
                    <li><span style="color:#FFC107;">&#9679;</span> Chưa bắt đầu: ${notStarted}</li>
                    <li><span style="color:#2196F3;">&#9679;</span> Đang diễn ra: ${ongoing}</li>
                </ul>
            `;

            const eventsList = document.getElementById('eventsList');
            data.forEach(event => {
                const div = document.createElement('div');
                div.innerHTML = `<h3>${event.title}</h3><p>${event.description}</p>`;
                eventsList.appendChild(div);
            });

            // Hiển thị nút tạo sự kiện nếu người dùng đã đăng nhập
            if (sessionStorage.getItem('loggedIn')) {
                document.getElementById('createEventBtn').style.display = 'block';
                document.getElementById('createEventBtn').onclick = () => {
                    window.location.href = 'create_event.html';
                };
            }
        });
});