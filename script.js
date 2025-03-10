document.addEventListener('DOMContentLoaded', () => {
    const arrow = document.getElementById('arrowDown');
    const dropdown = document.getElementById('dropdown');
    const userMenu = document.getElementById('userMenu');
    const userNameElement = document.getElementById('userName');
    const createEventLink = document.getElementById('createEventLink');
    const loginLink = document.getElementById('loginLink');
    const registerLink = document.getElementById('registerLink');
    const slideshowContainer = document.getElementById('slideshow');
    let slideIndex = 0;

    if (sessionStorage.getItem('loggedIn')) {
        const userName = "Người dùng";
        userNameElement.innerText = userName;
        userMenu.style.display = 'flex';
        loginLink.style.display = 'none';
        registerLink.style.display = 'none';
        createEventLink.style.display = 'block';
    }

    arrow.addEventListener('click', (event) => {
        event.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    });

    document.addEventListener('click', () => {
        dropdown.style.display = 'none';
    });

    fetch('events.php')
        .then(response => response.json())
        .then(data => {
            

            const completed = data.filter(event => event.raised_amount >= event.target_amount).length;
            const notStarted = data.filter(event => event.raised_amount === 0).length;
            const ongoing = data.length - completed - notStarted;

            const ctx = document.getElementById('eventChart').getContext('2d');
            new Chart(ctx, {
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
                        legend: { display: true, position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ' + context.raw;
                                }
                            }
                        }
                    }
                }
            });

            const eventsList = document.getElementById('eventsList');
            data.forEach(event => {
                const div = document.createElement('div');
                div.innerHTML = `<h3>${event.title}</h3><p>${event.description}</p>`;
                eventsList.appendChild(div);
            });

            // Cập nhật slideshow với ảnh Base64
            slideshowContainer.innerHTML = data.map((event, index) => `
                <div class="slide" style="display: ${index === 0 ? 'block' : 'none'}; position: relative;">
                    <img src="${event.image_path}" alt="${event.title}" style="width:100%; max-height:400px; object-fit:cover;">
                    <div class="event-title" style="position: absolute; bottom: 10px; left: 10px; background: rgba(0,0,0,0.5); color: white; padding: 5px;">${event.title}</div>
                </div>
            `).join('');

            function showSlides(n) {
                let slides = document.querySelectorAll('.slide');
                slideIndex = (n + slides.length) % slides.length;
                slides.forEach((slide, i) => {
                    slide.style.display = i === slideIndex ? 'block' : 'none';
                });
            }

            window.changeSlide = (n) => {
                showSlides(slideIndex + n);
            };
        })
        .catch(error => console.error('Lỗi khi tải dữ liệu sự kiện:', error));
});
