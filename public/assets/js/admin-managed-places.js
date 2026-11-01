// Lọc danh sách hiện có theo tên, danh mục, tỉnh và trạng thái.
var managedSearch = document.getElementById('managed-search');
var managedCategory = document.getElementById('managed-category');
var managedProvince = document.getElementById('managed-province');
var managedRows = document.querySelectorAll('.managed-place-row');
var statusButtons = document.querySelectorAll('[data-status-filter]');
var selectedStatus = '';
var currentPage = 1;
var placesPerPage = 7;

function filterManagedPlaces() {
    var searchText = managedSearch.value.trim().toLocaleLowerCase();
    var matchingRows = [];
    for (var index = 0; index < managedRows.length; index++) {
        var row = managedRows[index];
        var matchesTitle = row.dataset.title.toLocaleLowerCase().indexOf(searchText) !== -1;
        var matchesCategory = managedCategory.value === '' || row.dataset.category === managedCategory.value;
        var matchesProvince = managedProvince.value === '' || row.dataset.province === managedProvince.value;
        var matchesStatus = selectedStatus === '' || row.dataset.status === selectedStatus;
        row.hidden = true;
        if (matchesTitle && matchesCategory && matchesProvince && matchesStatus) {
            matchingRows.push(row);
        }
    }
    var pageCount = Math.ceil(matchingRows.length / placesPerPage);
    if (currentPage > pageCount) {
        currentPage = Math.max(1, pageCount);
    }
    var firstIndex = (currentPage - 1) * placesPerPage;
    var lastIndex = Math.min(firstIndex + placesPerPage, matchingRows.length);
    for (var rowIndex = firstIndex; rowIndex < lastIndex; rowIndex++) {
        matchingRows[rowIndex].hidden = false;
    }
    var pagination = document.getElementById('managed-pagination');
    pagination.textContent = '';
    for (var pageNumber = 1; pageNumber <= pageCount; pageNumber++) {
        var pageButton = document.createElement('button');
        pageButton.type = 'button';
        pageButton.textContent = String(pageNumber);
        pageButton.dataset.page = String(pageNumber);
        pageButton.setAttribute('aria-label', 'Trang ' + pageNumber);
        if (pageNumber === currentPage) {
            pageButton.classList.add('selected');
            pageButton.setAttribute('aria-current', 'page');
        }
        pageButton.addEventListener('click', selectManagedPage);
        pagination.appendChild(pageButton);
    }
    document.getElementById('managed-empty').hidden = matchingRows.length !== 0;
    document.getElementById('managed-summary').textContent = 'Hiển thị ' + (lastIndex - firstIndex) + ' trong ' + matchingRows.length + ' địa điểm phù hợp';
}

function selectManagedPage(event) {
    currentPage = Number(event.currentTarget.dataset.page);
    filterManagedPlaces();
}

function resetManagedPage() {
    currentPage = 1;
    filterManagedPlaces();
}

function selectManagedStatus(event) {
    selectedStatus = event.currentTarget.dataset.statusFilter;
    currentPage = 1;
    for (var index = 0; index < statusButtons.length; index++) {
        statusButtons[index].classList.remove('selected');
    }
    event.currentTarget.classList.add('selected');
    filterManagedPlaces();
}

managedSearch.addEventListener('input', resetManagedPage);
managedCategory.addEventListener('change', resetManagedPage);
managedProvince.addEventListener('change', resetManagedPage);
for (var index = 0; index < statusButtons.length; index++) {
    statusButtons[index].addEventListener('click', selectManagedStatus);
}
filterManagedPlaces();
