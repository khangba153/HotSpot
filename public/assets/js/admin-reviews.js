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
    renderAdminPagination(pagination, reviewPage, pages, selectReviewPage);
    document.getElementById('review-empty').hidden = matching.length !== 0;
    var summaryText = 'Không có đánh giá phù hợp.';
    if (matching.length > 0) {
        summaryText = 'Hiển thị ' + (start + 1) + '–' + end
            + ' trên tổng ' + matching.length + ' đánh giá phù hợp';
    }
    document.getElementById('review-summary').textContent = summaryText;
}
function selectReviewPage(event) {
    reviewPage = Number(event.currentTarget.dataset.page);
    showReviews();
}
function resetReviewPage() { reviewPage = 1; showReviews(); }
reviewSearch.addEventListener('input', resetReviewPage);
reviewStatus.addEventListener('change', resetReviewPage);
reviewPlace.addEventListener('change', resetReviewPage);
showReviews();
