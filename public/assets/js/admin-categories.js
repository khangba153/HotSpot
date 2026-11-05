// Điền form thêm hoặc sửa danh mục khi mở hộp thoại.
var categoryModal = document.getElementById('category-modal');
categoryModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var editing = button.dataset.mode === 'edit';
    var form = document.getElementById('category-form');
    form.action = editing ? form.dataset.updateUrl : form.dataset.createUrl;
    document.getElementById('category-id').value = editing ? button.dataset.id : '';
    document.getElementById('category-name').value = editing ? button.dataset.name : '';
    document.getElementById('category-description').value = editing ? button.dataset.description : '';
    document.getElementById('category-modal-title').textContent = editing ? 'Sửa danh mục' : 'Thêm danh mục mới';
    document.getElementById('category-submit').textContent = editing ? 'Lưu thay đổi' : 'Thêm danh mục';
});
categoryModal.addEventListener('shown.bs.modal', function () { document.getElementById('category-name').focus(); });
var categorySearch = document.getElementById('category-search');
var categoryFilter = document.getElementById('category-filter');
function filterCategories() {
    var rows = document.querySelectorAll('.category-row');
    var query = categorySearch.value.trim().toLocaleLowerCase();
    var count = 0;
    for (var index = 0; index < rows.length; index++) {
        var row = rows[index];
        var visible = row.dataset.name.toLocaleLowerCase().indexOf(query) !== -1 && (categoryFilter.value === '' || row.dataset.active === categoryFilter.value);
        row.hidden = !visible;
        if (visible) { count++; }
    }
    document.getElementById('category-empty').hidden = count !== 0;
}
categorySearch.addEventListener('input', filterCategories);
categoryFilter.addEventListener('change', filterCategories);
