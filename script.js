document.addEventListener('DOMContentLoaded', () => {
    fetch('events.php')
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) return;

            // Cập nhật Slideshow
            const slideshow = document.getElementById('slideshow');
            data.forEach((event, index) => {
                const slide = document.createElement('div');
                slide.classList.add('slide');
                slide.innerHTML = `
                    <img src="data:image/jpeg;base64,${event.image_path}" alt="${event.title}">
                    <div class="event-title">${event.title}</div>
                `;
                if (index === 0) slide.style.display = 'block';
                slideshow.appendChild(slide);
            });

            // Cập nhật danh sách sự kiện
            const eventsList = document.getElementById('eventsList');
            data.forEach(event => {
                const div = document.createElement('div');
                div.innerHTML = `
                    <h3>${event.title}</h3>
                    <p>${event.description}</p>
                    <img src="data:image/jpeg;base64,${event.image_path}" alt="${event.title}" style="width:100px;">
                `;
                eventsList.appendChild(div);
            });
        });
});
