// main.js — موقع مفقودات وموجودات الجامعة

// تأكيد الحذف
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
});

// معاينة الصورة قبل الرفع
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            let preview = this.nextElementSibling;
            if (!preview || preview.tagName !== 'IMG') {
                preview = document.createElement('img');
                preview.style.cssText = 'width:100%; max-height:160px; object-fit:cover; border-radius:8px; margin-top:8px;';
                this.parentNode.appendChild(preview);
            }
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
});

// إغلاق الإشعارات
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => alert.style.display = 'none', 5000);
});
