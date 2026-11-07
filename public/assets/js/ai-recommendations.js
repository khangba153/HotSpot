'use strict';

// Tạo nội dung bằng textContent để không diễn giải dữ liệu thành HTML.
function createRecommendationElement(tagName, className, text) {
    const element = document.createElement(tagName);
    element.className = className;
    element.textContent = text;
    return element;
}

function useRecommendationImageFallback(event) {
    event.target.src = event.target.getAttribute('data-fallback-image');
}

function getRecommendationFallbackImage(categoryId) {
    const images = {
        1: 'assets/images/home/food.webp',
        2: 'assets/images/home/coffee.webp',
        3: 'assets/images/home/travel.webp',
        4: 'assets/images/home/fun.webp',
        5: 'assets/images/home/nature.webp',
        6: 'assets/images/home/culture.webp'
    };
    return images[categoryId] || images[3];
}

function getRecommendationImageUrl(value, fallbackImage) {
    if (typeof value !== 'string' || value === 'assets/images/demo-placeholder.svg') {
        return fallbackImage;
    }
    if (/^(assets|uploads)\/[A-Za-z0-9._/-]+$/.test(value) && !value.includes('..')) {
        return value;
    }
    try {
        const imageUrl = new URL(value);
        if (imageUrl.protocol === 'https:' || imageUrl.protocol === 'http:') {
            return imageUrl.href;
        }
    } catch (error) {
        return fallbackImage;
    }
    return fallbackImage;
}

function createRecommendationCard(place) {
    const placeId = String(place.place_id);
    if (!/^[1-9][0-9]{0,17}$/.test(placeId)) {
        throw new Error('Kết quả có mã địa điểm không hợp lệ.');
    }
    const detailUrl = new URL('index.php', window.location.href);
    detailUrl.searchParams.set('route', 'places/show');
    detailUrl.searchParams.set('id', placeId);

    const column = createRecommendationElement('div', 'col-12 col-md-6 col-lg-4', '');
    const card = createRecommendationElement('article', 'hs-ai-place-card', '');
    const pictureLink = createRecommendationElement('a', 'hs-ai-place-card__picture', '');
    pictureLink.href = detailUrl.href;
    const image = document.createElement('img');
    const fallbackImage = getRecommendationFallbackImage(place.category_id);
    image.src = getRecommendationImageUrl(place.cover_image, fallbackImage);
    image.setAttribute('data-fallback-image', fallbackImage);
    image.alt = String(place.title || 'Ảnh địa điểm');
    image.loading = 'lazy';
    image.addEventListener('error', useRecommendationImageFallback, { once: true });
    pictureLink.appendChild(image);

    const body = createRecommendationElement('div', 'hs-ai-place-card__body', '');
    const heading = createRecommendationElement('h3', '', '');
    const titleLink = createRecommendationElement('a', '', String(place.title || 'Địa điểm'));
    titleLink.href = detailUrl.href;
    heading.appendChild(titleLink);
    body.appendChild(heading);
    const details = [];
    if (place.category_name) {
        details.push(String(place.category_name));
    }
    if (place.province_name) {
        details.push(String(place.province_name));
    }
    body.appendChild(createRecommendationElement('p', 'hs-ai-summary__note', details.join(' · ')));
    body.appendChild(createRecommendationElement('p', 'hs-ai-place-card__address', String(place.address || '')));
    const viewLink = createRecommendationElement('a', 'hs-ai-summary__button', 'Xem địa điểm');
    viewLink.href = detailUrl.href;
    body.appendChild(viewLink);
    card.append(pictureLink, body);
    column.appendChild(card);
    return column;
}

function getRecommendationFilterLabels(filters, catalog) {
    const labels = [];
    if (typeof filters.keyword === 'string' && filters.keyword !== '') {
        labels.push('Từ khóa: ' + filters.keyword);
    }
    const selections = [
        ['categories', 'category_id', 'name'],
        ['provinces', 'province_code', 'province_name'],
        ['tags', 'tag_slug', 'name']
    ];
    for (const selection of selections) {
        let catalogField = selection[1];
        if (catalogField === 'tag_slug') {
            catalogField = 'slug';
        }
        for (const item of catalog[selection[0]] || []) {
            if (filters[selection[1]] !== null && String(item[catalogField]) === String(filters[selection[1]])) {
                labels.push(String(item[selection[2]]));
                break;
            }
        }
    }
    return labels;
}

function renderRecommendationResults(state, data) {
    if (!data || !Array.isArray(data.places) || !data.filters || !Array.isArray(data.filters.unsupported)) {
        throw new Error('Chưa nhận được kết quả gợi ý hợp lệ.');
    }
    const fragment = document.createDocumentFragment();
    for (const place of data.places.slice(0, 6)) {
        fragment.appendChild(createRecommendationCard(place));
    }
    state.cards.replaceChildren(fragment);
    const labels = getRecommendationFilterLabels(data.filters, state.catalog);
    state.filters.textContent = '';
    state.filters.hidden = labels.length === 0;
    if (labels.length > 0) {
        state.filters.textContent = 'Đã lọc theo: ' + labels.join(' · ');
    }
    state.warning.hidden = data.filters.unsupported.length === 0;
    state.warning.textContent = '';
    if (data.filters.unsupported.length > 0) {
        state.warning.textContent = 'Chưa lọc được: ' + data.filters.unsupported.join('; ')
            + '. Kết quả chưa được kiểm tra theo các tiêu chí này.';
    }
    state.result.hidden = false;
}

// Chỉ gửi khi bấm tìm; dùng cache và giới hạn lượt gọi do server kiểm tra.
async function submitRecommendationRequest(event) {
    event.preventDefault();
    const state = event.currentTarget.recommendationState;
    if (state.button.disabled) {
        return;
    }
    const requestText = state.input.value.trim();
    state.result.hidden = true;
    state.cards.replaceChildren();
    state.feedback.hidden = false;
    state.feedback.classList.remove('is-error');
    if (requestText === '' || Array.from(requestText).length > 500) {
        state.feedback.textContent = 'Vui lòng nhập nhu cầu từ 1 đến 500 ký tự.';
        state.feedback.classList.add('is-error');
        return;
    }
    state.button.disabled = true;
    state.button.textContent = 'Đang tìm gợi ý...';
    state.input.readOnly = true;
    state.form.setAttribute('aria-busy', 'true');
    state.feedback.textContent = 'Đang phân tích nhu cầu và tìm địa điểm trong dữ liệu Hot Spot.';
    try {
        const response = await fetch(state.form.action, {
            method: 'POST',
            body: new FormData(state.form),
            credentials: 'same-origin',
            headers: { Accept: 'application/json' }
        });
        const payload = await response.json();
        if (!response.ok || payload.success !== true) {
            let message = 'Chưa thể gợi ý địa điểm. Vui lòng thử lại sau.';
            if (typeof payload.message === 'string' && payload.message !== '') {
                message = payload.message;
            }
            throw new Error(message);
        }
        renderRecommendationResults(state, payload.data);
        state.feedback.textContent = payload.message;
        if (payload.data.places.length > 0) {
            state.feedback.textContent += ' Hiển thị ' + payload.data.places.length
                + ' trong ' + payload.data.total_count + ' kết quả.';
        }
    } catch (error) {
        state.result.hidden = true;
        state.feedback.textContent = 'Không thể kết nối. Vui lòng thử lại sau.';
        if (error instanceof Error && error.name !== 'SyntaxError') {
            state.feedback.textContent = error.message;
        }
        state.feedback.classList.add('is-error');
    } finally {
        state.button.disabled = false;
        state.button.textContent = 'Tìm gợi ý';
        state.input.readOnly = false;
        state.form.removeAttribute('aria-busy');
    }
}

function updateRecommendationInput(event) {
    const state = event.currentTarget.recommendationState;
    state.counter.textContent = Array.from(state.input.value).length + '/500 ký tự';
    state.result.hidden = true;
    state.feedback.hidden = true;
}

// Mở hoặc đóng chỉ thay đổi giao diện, không gửi yêu cầu AI.
function openRecommendationDialog(event) {
    const dialog = document.getElementById(event.currentTarget.getAttribute('aria-controls'));
    if (dialog.open) {
        return;
    }
    dialog.showModal();
    document.documentElement.classList.add('hs-ai-dialog-open');
    const input = dialog.querySelector('[name="request_text"]');
    if (input) {
        input.focus();
    }
}

function closeRecommendationDialog(event) {
    event.currentTarget.closest('dialog').close();
}

function restoreRecommendationDialogFocus() {
    document.documentElement.classList.remove('hs-ai-dialog-open');
    const trigger = document.querySelector('[data-ai-dialog-open]');
    if (trigger) {
        trigger.focus({ preventScroll: true });
    }
}

function closeRecommendationBackdrop(event) {
    const dialog = event.currentTarget;
    const bounds = dialog.getBoundingClientRect();
    if (event.target === dialog && (event.clientX < bounds.left || event.clientX > bounds.right
        || event.clientY < bounds.top || event.clientY > bounds.bottom)) {
        dialog.close();
    }
}

function initializePlaceRecommendations() {
    const section = document.querySelector('[data-ai-recommendations]');
    const trigger = document.querySelector('[data-ai-dialog-open]');
    if (!section || !trigger || typeof section.showModal !== 'function') {
        return;
    }
    trigger.hidden = false;
    trigger.addEventListener('click', openRecommendationDialog);
    section.querySelector('[data-ai-dialog-close]').addEventListener('click', closeRecommendationDialog);
    section.addEventListener('close', restoreRecommendationDialogFocus);
    section.addEventListener('click', closeRecommendationBackdrop);
    const form = document.querySelector('[data-ai-recommendations-form]');
    if (!form) {
        return;
    }
    const state = {
        form: form,
        input: form.querySelector('[name="request_text"]'),
        button: form.querySelector('button[type="submit"]'),
        counter: section.querySelector('[data-ai-request-count]'),
        feedback: section.querySelector('[data-ai-recommendations-feedback]'),
        result: section.querySelector('[data-ai-recommendations-result]'),
        cards: section.querySelector('[data-ai-recommendations-cards]'),
        filters: section.querySelector('[data-ai-applied-filters]'),
        warning: section.querySelector('[data-ai-unsupported]'),
        catalog: JSON.parse(section.getAttribute('data-search-catalog'))
    };
    form.recommendationState = state;
    state.input.recommendationState = state;
    form.addEventListener('submit', submitRecommendationRequest);
    state.input.addEventListener('input', updateRecommendationInput);
}

initializePlaceRecommendations();
