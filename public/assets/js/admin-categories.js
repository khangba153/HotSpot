// Điền form thêm hoặc sửa danh mục khi mở hộp thoại.
var categoryModal = document.getElementById('category-modal');
var categoryImageInput = document.getElementById('category-image');
var categoryImagePathInput = document.getElementById('category-image-path');
var categoryImagePreview = document.getElementById('category-image-preview');
var categoryPreviewObjectUrl = '';

// Mở form với đúng dữ liệu đang hiển thị trong database.
function openCategoryModal(event) {
    var button = event.relatedTarget;
    var form = document.getElementById('category-form');
    form.reset();
    form.action = form.dataset.createUrl;
    document.getElementById('category-modal-title').textContent = 'Thêm danh mục mới';
    document.getElementById('category-submit').textContent = 'Thêm danh mục';
    if (button && button.dataset.mode === 'edit') {
        form.action = form.dataset.updateUrl;
        document.getElementById('category-id').value = button.dataset.id;
        document.getElementById('category-name').value = button.dataset.name;
        document.getElementById('category-description').value = button.dataset.description;
        categoryImagePathInput.value = button.dataset.imagePath;
        document.getElementById('category-modal-title').textContent = 'Sửa danh mục';
        document.getElementById('category-submit').textContent = 'Lưu thay đổi';
    }
    document.getElementById('category-label-image-path').value = '';
    if (button && button.dataset.mode === 'edit') {
        document.getElementById('category-label-image-path').value = button.dataset.cardLabelImagePath || '';
    }
    refreshCategoryPreview();
    refreshCategoryLabelPreview();
}

function focusCategoryName() {
    document.getElementById('category-name').focus();
}

// Xem trước ảnh trên máy; file chỉ được gửi khi bấm lưu.
function refreshCategoryPreview() {
    if (categoryPreviewObjectUrl !== '') {
        URL.revokeObjectURL(categoryPreviewObjectUrl);
        categoryPreviewObjectUrl = '';
    }
    var imagePath = categoryImagePathInput.value.trim();
    categoryImagePreview.hidden = true;
    categoryImagePreview.removeAttribute('src');
    if (categoryImageInput.files.length > 0) {
        var imageFile = categoryImageInput.files[0];
        if (!['image/png', 'image/jpeg', 'image/webp'].includes(imageFile.type) || imageFile.size > 2 * 1024 * 1024) {
            categoryImageInput.setCustomValidity('Chọn ảnh PNG, JPG hoặc WebP tối đa 2 MB.');
            categoryImageInput.reportValidity();
            return;
        }
        categoryPreviewObjectUrl = URL.createObjectURL(imageFile);
        imagePath = categoryPreviewObjectUrl;
    }
    categoryImageInput.setCustomValidity('');
    if (imagePath.startsWith('assets/images/') || imagePath.startsWith('uploads/categories/')
        || imagePath.startsWith('https://') || imagePath.startsWith('blob:')) {
        categoryImagePreview.src = imagePath;
        categoryImagePreview.hidden = false;
    }
}

function hideBrokenCategoryPreview() {
    categoryImagePreview.hidden = true;
}
categoryModal.addEventListener('show.bs.modal', openCategoryModal);
categoryModal.addEventListener('shown.bs.modal', focusCategoryName);
categoryImageInput.addEventListener('change', refreshCategoryPreview);
categoryImagePathInput.addEventListener('input', refreshCategoryPreview);
categoryImagePreview.addEventListener('error', hideBrokenCategoryPreview);
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

var categoryLabelImageInput = document.getElementById('category-label-image');
var categoryLabelImagePathInput = document.getElementById('category-label-image-path');
var categoryLabelImagePreview = document.getElementById('category-label-image-preview');
var categoryLabelPreviewObjectUrl = '';
function refreshCategoryLabelPreview() {
    if (categoryLabelPreviewObjectUrl !== '') {
        URL.revokeObjectURL(categoryLabelPreviewObjectUrl);
        categoryLabelPreviewObjectUrl = '';
    }
    var imagePath = categoryLabelImagePathInput.value.trim();
    categoryLabelImagePreview.hidden = true;
    categoryLabelImagePreview.removeAttribute('src');
    if (categoryLabelImageInput.files.length > 0) {
        var imageFile = categoryLabelImageInput.files[0];
        if (!['image/png', 'image/jpeg', 'image/webp'].includes(imageFile.type) || imageFile.size > 2 * 1024 * 1024) {
            categoryLabelImageInput.setCustomValidity('Chọn ảnh PNG, JPG hoặc WebP tối đa 2 MB.');
            categoryLabelImageInput.reportValidity();
            return;
        }
        categoryLabelPreviewObjectUrl = URL.createObjectURL(imageFile);
        imagePath = categoryLabelPreviewObjectUrl;
    }
    categoryLabelImageInput.setCustomValidity('');
    if (imagePath.startsWith('assets/images/') || imagePath.startsWith('uploads/categories/')
        || imagePath.startsWith('https://') || imagePath.startsWith('blob:')) {
        categoryLabelImagePreview.src = imagePath;
        categoryLabelImagePreview.hidden = false;
    }
}

function hideBrokenCategoryLabelPreview() {
    categoryLabelImagePreview.hidden = true;
}
categoryLabelImageInput.addEventListener('change', refreshCategoryLabelPreview);
categoryLabelImagePathInput.addEventListener('input', refreshCategoryLabelPreview);
categoryLabelImagePreview.addEventListener('error', hideBrokenCategoryLabelPreview);
