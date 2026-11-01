const homeSearchState = {
    form: null,
    input: null,
    categorySelect: null,
    provinceSelect: null,
    results: null,
    feedback: null,
    feedbackText: null,
    clearLink: null,
    heading: null,
    pagination: null,
    tools: null,
    allLink: null,
    timer: null,
    currentRequest: null
};

function initializeHomeSearch() {
    homeSearchState.form = document.getElementById('home-search');

    if (!homeSearchState.form) {
        return;
    }

    homeSearchState.input = homeSearchState.form.querySelector('input[name="keyword"]');
    homeSearchState.categorySelect = homeSearchState.form.querySelector('select[name="category_id"]');
    homeSearchState.provinceSelect = homeSearchState.form.querySelector('select[name="province_code"]');
    homeSearchState.results = document.getElementById('home-search-results');
    homeSearchState.feedback = document.getElementById('home-search-feedback');
    homeSearchState.feedbackText = document.getElementById('home-search-feedback-text');
    homeSearchState.clearLink = document.getElementById('home-search-clear');
    homeSearchState.heading = document.getElementById('featured-places-heading');
    homeSearchState.pagination = document.getElementById('home-pagination');
    homeSearchState.tools = document.getElementById('home-featured-tools');
    homeSearchState.allLink = document.getElementById('home-featured-all-link');

    if (
        !homeSearchState.input
        || !homeSearchState.categorySelect
        || !homeSearchState.provinceSelect
        || !homeSearchState.results
        || !homeSearchState.feedback
        || !homeSearchState.feedbackText
    ) {
        return;
    }

    homeSearchState.input.addEventListener('input', handleKeywordInput);
    homeSearchState.categorySelect.addEventListener('change', handleFilterChange);
    homeSearchState.provinceSelect.addEventListener('change', handleFilterChange);
    homeSearchState.form.addEventListener('submit', handleSearchSubmit);

    if (homeSearchState.clearLink) {
        homeSearchState.clearLink.addEventListener('click', handleClearSearch);
    }
}

function searchPlaces() {
    const searchUrl = new URL(
        homeSearchState.form.dataset.searchUrl,
        window.location.href
    );

    const keyword = homeSearchState.input.value.trim();
    const categoryId = homeSearchState.categorySelect.value;
    const provinceCode = homeSearchState.provinceSelect.value;

    if (keyword !== '') {
        searchUrl.searchParams.set('keyword', keyword);
    } else {
        searchUrl.searchParams.delete('keyword');
    }

    if (categoryId !== '') {
        searchUrl.searchParams.set('category_id', categoryId);
    } else {
        searchUrl.searchParams.delete('category_id');
    }

    if (provinceCode !== '') {
        searchUrl.searchParams.set('province_code', provinceCode);
    } else {
        searchUrl.searchParams.delete('province_code');
    }

    if (homeSearchState.currentRequest !== null) {
        homeSearchState.currentRequest.abort();
    }

    const requestController = new AbortController();
    homeSearchState.currentRequest = requestController;

    fetch(searchUrl, {
        headers: { Accept: 'application/json' },
        signal: requestController.signal
    })
        .then(parseSearchResponse)
        .then(handleSearchData)
        .catch(handleSearchError);

    updateBrowserSearchUrl(keyword, categoryId, provinceCode);
}

function parseSearchResponse(response) {
    if (!response.ok) {
        throw new Error('Request failed');
    }

    return response.json();
}

function handleSearchData(searchResponse) {
    if (
        !searchResponse.success
        || !searchResponse.data
        || typeof searchResponse.data.html !== 'string'
    ) {
        throw new Error('Invalid response');
    }

    const keyword = searchResponse.data.keyword || '';
    const totalCount = Number(searchResponse.data.total_count) || 0;
    const categoryId = homeSearchState.categorySelect.value;
    const provinceCode = homeSearchState.provinceSelect.value;
    const hasActiveFilters = keyword !== '' || categoryId !== '' || provinceCode !== '';

    if (hasActiveFilters && totalCount === 0) {
        homeSearchState.results.innerHTML = '<div class="hs-empty-state" role="status"><h3>Không tìm thấy địa điểm phù hợp</h3><p>Thử thay đổi từ khóa hoặc bộ lọc.</p></div>';
    } else {
        homeSearchState.results.innerHTML = searchResponse.data.html;
    }

    if (homeSearchState.heading) {
        homeSearchState.heading.textContent = hasActiveFilters
            ? 'Kết quả tìm kiếm'
            : 'Địa điểm nổi bật';
    }

    homeSearchState.feedback.hidden = !hasActiveFilters;

    if (hasActiveFilters) {
        homeSearchState.feedbackText.textContent =
            'Tìm thấy ' + totalCount + ' địa điểm phù hợp với bộ lọc hiện tại.';
    } else {
        homeSearchState.feedbackText.textContent = '';
    }

    if (homeSearchState.clearLink) {
        homeSearchState.clearLink.hidden = !hasActiveFilters;
    }

    if (homeSearchState.pagination) {
        homeSearchState.pagination.hidden = hasActiveFilters;
    }

    if (homeSearchState.tools) {
        homeSearchState.tools.hidden = hasActiveFilters;
    }

    if (homeSearchState.allLink) {
        homeSearchState.allLink.hidden = hasActiveFilters;
    }
}

function handleSearchError(error) {
    if (error.name === 'AbortError') {
        return;
    }

    homeSearchState.feedback.hidden = false;
    homeSearchState.feedbackText.textContent =
        'Không thể tìm kiếm lúc này. Vui lòng thử lại.';
}

function updateBrowserSearchUrl(keyword, categoryId, provinceCode) {
    const homeUrl = new URL(homeSearchState.form.action, window.location.href);

    homeUrl.searchParams.set('route', 'home');

    if (keyword !== '') {
        homeUrl.searchParams.set('keyword', keyword);
    } else {
        homeUrl.searchParams.delete('keyword');
    }

    if (categoryId !== '') {
        homeUrl.searchParams.set('category_id', categoryId);
    } else {
        homeUrl.searchParams.delete('category_id');
    }

    if (provinceCode !== '') {
        homeUrl.searchParams.set('province_code', provinceCode);
    } else {
        homeUrl.searchParams.delete('province_code');
    }

    window.history.replaceState(null, '', homeUrl.toString());
}

function handleKeywordInput() {
    window.clearTimeout(homeSearchState.timer);

    if (homeSearchState.currentRequest !== null) {
        homeSearchState.currentRequest.abort();
    }

    homeSearchState.timer = window.setTimeout(searchPlaces, 300);
}

function handleFilterChange() {
    window.clearTimeout(homeSearchState.timer);
    searchPlaces();
}

function handleSearchSubmit(event) {
    event.preventDefault();
    window.clearTimeout(homeSearchState.timer);
    searchPlaces();
}

function handleClearSearch(event) {
    event.preventDefault();

    homeSearchState.input.value = '';
    homeSearchState.categorySelect.value = '';
    homeSearchState.provinceSelect.value = '';

    window.clearTimeout(homeSearchState.timer);
    searchPlaces();
    homeSearchState.input.focus();
}

initializeHomeSearch();
