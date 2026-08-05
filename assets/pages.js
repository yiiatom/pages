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

document.addEventListener('DOMContentLoaded', function () {
    const list = document.querySelector('.sortable-tree');
    if (!list) return;

    const url = list.dataset.sortUrl;
    if (!url) return;

    function getChildRows(parent) {
        const rows = [];
        const depth = parseInt(parent.dataset.depth);

        let next = parent.nextElementSibling;
        while (next && parseInt(next.dataset.depth) > depth) {
            rows.push(next);
            next = next.nextElementSibling;
        }

        return rows;
    }

    let draggedChildren = [];
    let allowMin = null;
    let allowMax = null;

    new Sortable(list, {
        handle: '.sort-handle',
        animation: 150,
        ghostClass: 'table-active',
        onStart: function (evt) {
            const row = evt.item;
            const depth = parseInt(row.dataset.depth);

            draggedChildren = getChildRows(row);
            draggedChildren.forEach(child => child.classList.add('d-none'));

            allowMin = null;
            let prev = row.previousElementSibling;
            while (prev) {
                if (parseInt(prev.dataset.depth) < depth) {
                    allowMin = prev;
                    break;
                }
                prev = prev.previousElementSibling;
            }

            allowMax = null;
            let next = row.nextElementSibling;
            while (next && draggedChildren.includes(next)) {
                next = next.nextElementSibling;
            }
            while (next) {
                if (parseInt(next.dataset.depth) < depth) {
                    allowMax = next;
                    break;
                }
                next = next.nextElementSibling;
            }
        },
        onMove: function (evt) {
            const depth = parseInt(evt.dragged.dataset.depth);
            const related = evt.related;
            const relatedDepth = parseInt(related.dataset.depth);

            if (relatedDepth < depth) {
                return false;
            }
            if (evt.willInsertAfter) {
                const next = related.nextElementSibling;
                if (next && parseInt(next.dataset.depth) > depth) {
                    return false;
                }
            } else {
                if (relatedDepth !== depth) {
                    return false;
                }
            }
            if (allowMin && related.rowIndex <= allowMin.rowIndex) {
                return false;
            }
            if (allowMax && related.rowIndex >= allowMax.rowIndex) {
                return false;
            }

            return true;
        },
        onEnd: function (evt) {
            const target = evt.item;
            const depth = parseInt(target.dataset.depth);

            let item = target;
            draggedChildren.forEach(child => {
                item.after(child);
                item = child;
                child.classList.remove('d-none')
            });

            draggedChildren = [];

            let first = target;
            while (first.previousElementSibling) {
                if (parseInt(first.previousElementSibling.dataset.depth) < depth) {
                    break;
                }
                first = first.previousElementSibling;
            }

            const positions = {};
            let pos = 1;
            item = first;
            while (item) {
                const itemDepth = parseInt(item.dataset.depth);
                if (itemDepth < depth) {
                    break;
                } else if (itemDepth === depth) {
                    positions[item.dataset.uuid] = pos;
                    pos++;
                }
                item = item.nextElementSibling;
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf"]')?.content,
                },
                body: JSON.stringify(positions),
            }).catch(err => console.error('Sort Error:', err));
        }
    });
});
