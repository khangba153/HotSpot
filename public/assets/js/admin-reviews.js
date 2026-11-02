// Lọc và phân trang các đánh giá đã tải.
var reviewSearch = document.getElementById('review-search');
var reviewStatus = document.getElementById('review-status');
var reviewPlace = document.getElementById('review-place');
var reviewRows = document.querySelectorAll('.admin-review-row');
var reviewPage = 1;
var reviewsPerPage = 6;
function showReviews() {
    var query = reviewSearch.value.trim().toLocaleLowerCase();
    var matching = [];
    for (var index = 0; index < reviewRows.length; index++) {
        var row = reviewRows[index];
        row.hidden = true;
        if ((row.dataset.author.toLocaleLowerCase().indexOf(query) !== -1 || row.dataset.title.toLocaleLowerCase().indexOf(query) !== -1)
            && (reviewStatus.value === '' || row.dataset.status === reviewStatus.value)
            && (reviewPlace.value === '' || row.dataset.place === reviewPlace.value)) {
            matching.push(row);
        }
    }
    var pages = Math.ceil(matching.length / reviewsPerPage);
    if (reviewPage > pages) { reviewPage = Math.max(1, pages); }
    var start = (reviewPage - 1) * reviewsPerPage;
    var end = Math.min(start + reviewsPerPage, matching.length);
    for (var rowIndex = start; rowIndex < end; rowIndex++) { matching[rowIndex].hidden = false; }
    var pagination = document.getElementById('review-pagination');
    pagination.textContent = '';
    for (var page = 1; page <= pages; page++) {
        var button = document.createElement('button');
        button.type = 'button';
        button.textContent = String(page);
        button.dataset.page = String(page);
        button.setAttribute('aria-label', 'Trang ' + page);
        if (page === reviewPage) { button.classList.add('selected'); button.setAttribute('aria-current', 'page'); }
        button.addEventListener('click', function (event) { reviewPage = Number(event.currentTarget.dataset.page); showReviews(); });
        pagination.appendChild(button);
    }
    document.getElementById('review-empty').hidden = matching.length !== 0;
    document.getElementById('review-summary').textContent = 'Hiển thị ' + (end - start) + ' trong ' + matching.length + ' đánh giá phù hợp';
}
function resetReviewPage() { reviewPage = 1; showReviews(); }
reviewSearch.addEventListener('input', resetReviewPage);
reviewStatus.addEventListener('change', resetReviewPage);
reviewPlace.addEventListener('change', resetReviewPage);
showReviews();
