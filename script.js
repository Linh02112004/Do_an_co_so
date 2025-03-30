document.addEventListener('DOMContentLoaded', () => {
    const searchBox = document.getElementById('searchBox');
    const searchButton = document.getElementById('searchButton');

    searchButton.addEventListener('click', filterEvents);
    searchBox.addEventListener('input', resetSearch); // Tự động hiển thị toàn bộ khi xóa nội dung tìm kiếm

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
});
