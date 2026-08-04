document.addEventListener('DOMContentLoaded', function () {
    const titleInput = document.querySelector('[name*="[title]"]');
    const slugInput = document.querySelector('[name*="[slug]"]');

    if (!titleInput || !slugInput) return;

    const form = titleInput.closest('form');
    if (!form) return;

    const url = form.dataset.translitUrl;
    if (!url) return;

    let isSlugEdited = false;
    let timer;

    titleInput.addEventListener('input', function () {
        if (isSlugEdited) return;
        clearTimeout(timer);

        timer = setTimeout(() => {
            const titleValue = titleInput.value.trim();
            if (!titleValue) {
                slugInput.value = '';
                return;
            }

            fetch(`${url}?text=${encodeURIComponent(titleValue)}`)
                .then(response => response.json())
                .then(data => {
                    if (!isSlugEdited && titleInput.value.trim() === data.text) {
                        slugInput.value = data.result;
                    }
                })
                .catch(err => console.error('Translit Error:', err));
        }, 300);
    });

    slugInput.addEventListener('input', function () {
        isSlugEdited = true;
        if (slugInput.value === '') {
            isSlugEdited = false;
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const contentTextarea = document.querySelector('[name*="[content]"]');
    const contentEditor = document.getElementById('form-content-container');

    if (!contentTextarea || !contentEditor) return;

    contentTextarea.style.display = 'none';

    const quill = new Quill(contentEditor, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{'header': [1, 2, 3, false]}],
                ['bold', 'italic', 'underline', 'strike'],
                [{'list': 'ordered'}, {'list': 'bullet'}],
                ['link', 'blockquote', 'code-block'],
                ['clean'],
            ],
        },
    });

    if (contentTextarea.value) {
        quill.clipboard.dangerouslyPasteHTML(contentTextarea.value);
    } else {
        contentTextarea.value = quill.getSemanticHTML();
    }

    quill.on('text-change', function () {        
        contentTextarea.value = quill.getSemanticHTML();
        contentTextarea.dispatchEvent(new Event('change'));
    });
});
