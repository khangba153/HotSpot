(() => {
  'use strict';

  const form = document.querySelector('[data-review-form]');
  if (!(form instanceof HTMLFormElement)) return;

  const textarea = form.querySelector('textarea[name="content"]');
  const submitButton = form.querySelector('button[type="submit"]');
  const submitLabel = form.querySelector('[data-review-submit-label]');
  const feedback = form.querySelector('[data-review-feedback]');
  const characterCount = form.querySelector('[data-review-count]');
  if (!(textarea instanceof HTMLTextAreaElement) || !(submitButton instanceof HTMLButtonElement)) return;

  const updateCount = () => {
    if (characterCount) characterCount.textContent = String(Array.from(textarea.value).length);
  };
  updateCount();
  textarea.addEventListener('input', updateCount);

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;

    if (feedback instanceof HTMLElement) {
      feedback.hidden = true;
      feedback.textContent = '';
      feedback.classList.remove('is-error', 'is-success');
    }

    const originalLabel = submitLabel?.textContent ?? 'Gửi đánh giá';
    submitButton.disabled = true;
    if (submitLabel) submitLabel.textContent = 'Đang gửi…';
    form.setAttribute('aria-busy', 'true');

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      const result = await response.json();

      if (!response.ok || result.success !== true) {
        const validationErrors = result.errors && typeof result.errors === 'object'
          ? Object.values(result.errors).filter((value) => typeof value === 'string').join(' ')
          : '';
        throw new Error(validationErrors || result.message || 'Chưa thể gửi đánh giá. Vui lòng thử lại.');
      }

      if (feedback instanceof HTMLElement) {
        feedback.textContent = result.message || 'Đánh giá đã được lưu.';
        feedback.hidden = false;
        feedback.classList.add('is-success');
      }
      if (submitLabel) submitLabel.textContent = 'Đã lưu';

      // Reload lại trang để đồng bộ danh sách review công khai do server render.
      window.setTimeout(() => window.location.reload(), 650);
    } catch (error) {
      if (feedback instanceof HTMLElement) {
        feedback.textContent = error instanceof Error ? error.message : 'Lỗi kết nối. Vui lòng thử lại.';
        feedback.hidden = false;
        feedback.classList.add('is-error');
      }
      submitButton.disabled = false;
      if (submitLabel) submitLabel.textContent = originalLabel;
    } finally {
      form.removeAttribute('aria-busy');
    }
  });
})();
