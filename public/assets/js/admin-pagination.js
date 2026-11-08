// Phân trang gọn: chỉ hiện trang đầu, cuối và các trang gần trang hiện tại.
function renderAdminPagination(container, currentPage, pageCount, selectPage) {
    container.textContent = '';
    container.hidden = pageCount <= 1;
    if (pageCount <= 1) {
        return;
    }
    function addButton(label, pageNumber, disabled) {
        var button = document.createElement('button');
        button.type = 'button';
        button.textContent = label;
        button.dataset.page = String(pageNumber);
        button.disabled = disabled;
        button.setAttribute('aria-label', 'Trang ' + pageNumber);
        if (pageNumber === currentPage && label === String(pageNumber)) {
            button.classList.add('selected');
            button.setAttribute('aria-current', 'page');
        }
        button.addEventListener('click', selectPage);
        container.appendChild(button);
    }
    addButton('Trước', Math.max(1, currentPage - 1), currentPage === 1);
    var previousNumber = 0;
    for (var pageNumber = 1; pageNumber <= pageCount; pageNumber++) {
        if (pageNumber !== 1 && pageNumber !== pageCount && Math.abs(pageNumber - currentPage) > 1) {
            continue;
        }
        if (previousNumber > 0 && pageNumber - previousNumber > 1) {
            var gap = document.createElement('span');
            gap.textContent = '…';
            gap.setAttribute('aria-hidden', 'true');
            container.appendChild(gap);
        }
        addButton(String(pageNumber), pageNumber, false);
        previousNumber = pageNumber;
    }
    addButton('Sau', Math.min(pageCount, currentPage + 1), currentPage === pageCount);
}
