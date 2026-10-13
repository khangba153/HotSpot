(() => {
    const form = document.getElementById('home-search');

    if (!form) return;

    const input = form.querySelector('input[name="keyword"]');
    const results = document.getElementById('home-search-results');
    const feedback = document.getElementById('home-search-feedback');
    const feedbackText = document.getElementById('home-search-feedback-text');
    const clearLink = document.getElementById('home-search-clear');
    const heading = document.getElementById('featured-places-heading');
    const pagination = document.getElementById('home-pagination');
    const tools = document.getElementById('home-featured-tools');
    const allLink = document.getElementById('home-featured-all-link');

    if (!input || !results || !feedback || !feedbackText) return;

    let timer;
    let currentRequest;

    async function searchPlaces() {
        const url = new URL(form.dataset.searchUrl, window.location.href);
        url.searchParams.set('keyword', input.value.trim());

        if (currentRequest) currentRequest.abort();

        const controller = new AbortController();
        currentRequest = controller;

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                signal: controller.signal
            });

            if (!response.ok) {
                throw new Error('Request failed');
            }

            const result = await response.json();

            if (
                !result.success ||
                !result.data ||
                typeof result.data.html !== 'string'
            ) {
                throw new Error('Invalid response');
            }

            const keyword = result.data.keyword || '';
            const total = Number(result.data.total_count) || 0;

            // Chỉ thay vùng card, không tải lại toàn trang.
            if (keyword !== '' && total === 0) {
                results.innerHTML = `
                    <div class="hs-empty-state" role="status">
                        <span class="hs-empty-state__icon" aria-hidden="true">⌖</span>
                        <h3>Không tìm thấy địa điểm phù hợp</h3>
                        <p>Thử một từ khóa khác hoặc rút gọn từ khóa hiện tại.</p>
                    </div>`;
            } else {
                results.innerHTML = result.data.html;
            }

            if (heading) {
                heading.textContent = keyword
                    ? 'Kết quả tìm kiếm'
                    : 'Địa điểm nổi bật';
            }

            feedback.hidden = keyword === '';
            feedbackText.textContent = keyword
                ? `Tìm thấy ${total} địa điểm phù hợp với từ khóa “${keyword}”.`
                : '';

            if (clearLink) clearLink.hidden = keyword === '';
            if (pagination) pagination.hidden = keyword !== '';
            if (tools) tools.hidden = keyword !== '';
            if (allLink) allLink.hidden = keyword !== '';

        } catch (error) {
            if (error.name === 'AbortError') return;

            feedback.hidden = false;
            feedbackText.textContent =
                'Không thể tìm kiếm lúc này. Vui lòng thử lại.';
        }
    }

    input.addEventListener('input', () => {
        clearTimeout(timer);

        if (currentRequest) currentRequest.abort();

        timer = setTimeout(searchPlaces, 300);
    });

    // Nút tìm kiếm và Enter cũng dùng AJAX khi JavaScript hoạt động.
    // Nếu JavaScript bị tắt, form GET ban đầu vẫn hoạt động.
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        clearTimeout(timer);
        searchPlaces();
    });

    if (clearLink) {
        clearLink.addEventListener('click', (event) => {
            event.preventDefault();
            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.focus();
        });
    }
})();
