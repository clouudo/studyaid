document.addEventListener('DOMContentLoaded', function() {
    const editor = document.getElementById('noteContent');
    const preview = document.getElementById('preview');
    const toolbar = document.getElementById('toolbar');

    function debounce(fn, ms) {
        let id;
        return (...args) => {
            clearTimeout(id);
            id = setTimeout(() => fn(...args), ms);
        }
    }

    function render() {
        const raw = editor.value || '';
        const html = marked.parse(raw, { gfm: true, breaks: true });
        preview.innerHTML = DOMPurify.sanitize(html);
    }

    const debounceRender = debounce(render, 100);
    editor.addEventListener('input', debounceRender);
    render();

    function getSelection() {
        return {
            start: editor.selectionStart,
            end: editor.selectionEnd,
            value: editor.value
        };
    }

    function setSelection(start, end) {
        editor.selectionStart = start;
        editor.selectionEnd = end;
        editor.focus();
    }

    function replaceSelection(transform) {
        const { start, end, value } = getSelection();
        const before = value.slice(0, start);
        const selected = value.slice(start, end);
        const after = value.slice(end);

        const { text, cursorStart, cursorEnd } = transform(selected);
        editor.value = before + text + after;
        setSelection(before.length + cursorStart, before.length + cursorEnd);
        render();
    }

    function wrapInline(wrapperLeft, wrapperRight = wrapperLeft) {
        return (selected) => {
            const text = (selected && selected.length) ? selected : 'text';
            const wrapped = `${wrapperLeft}${text}${wrapperRight}`;
            return {
                text: wrapped, cursorStart: wrapperLeft.length, cursorEnd: wrapperLeft.length + text.length
            };
        };
    }

    function toggleHeading(level = 1) {
        const prefix = '#'.repeat(level) + ' ';
        return (selected) => {
            const text = selected || 'Heading';
            const lines = text.split('\n').map(line => {
                const clean = line.replace(/^(#{1,6}\s+)?/, '');
                return prefix + clean;
            });
            const out = lines.join('\n');
            return { text: out, cursorStart: 0, cursorEnd: out.length };
        };
    }

    function prefixLines(prefix, defaultText = 'List item'){
        return (selected) => {
            const text = selected || defaultText;
            const out = text.split('\n').map(l => ( l ? `${prefix} ${l}` : `${prefix} `)).join('\n');
            return { text: out, cursorStart: 0, cursorEnd: out.length };
        };
    }

    function orderedList(defaultText = 'First item\nSecond item'){
        return (selected) =>{
            const text = selected || defaultText;
            const out = text.split('\n').map((l, i) => `${i + 1}. ${l || ''}`).join('\n');
            return { text: out, cursorStart: 0, cursorEnd: out.length };
        }
    }

    const undoStack = [];
    const redoStack = [];
    let lastSnapshot = editor.value;

    function snapshot(){
        if (editor.value !== lastSnapshot) {
            undoStack.push(lastSnapshot);
            lastSnapshot = editor.value;
            redoStack.length = 0;
        }
    }

    editor.addEventListener('input', snapshot);

    function undo(){
        if (!undoStack.length) return;
        redoStack.push(lastSnapshot);
        editor.value = undoStack.pop();
        lastSnapshot = editor.value;
        render();
    }

    function redo(){
        if (!redoStack.length) return;
        undoStack.push(lastSnapshot);
        editor.value = redoStack.pop();
        lastSnapshot = editor.value;
        render();
    }

    const buttons = toolbar.querySelectorAll('button');
    if (buttons.length >= 7) {
        buttons[0].addEventListener('click', undo);
        buttons[1].addEventListener('click', redo);
        buttons[2].addEventListener('click', () => replaceSelection(wrapInline('**', '**')));
        buttons[3].addEventListener('click', () => replaceSelection(wrapInline('*', '*')));
        buttons[4].addEventListener('click', () => replaceSelection(toggleHeading(1)));
        buttons[5].addEventListener('click', () => replaceSelection(prefixLines('-')));
        buttons[6].addEventListener('click', () => replaceSelection(orderedList()));
    }

    editor.addEventListener('keydown', (e) => {
        const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
        const ctrl = isMac ? e.metaKey : e.ctrlKey;

        if (ctrl && e.key.toLowerCase() === 'b') {
            e.preventDefault();
            replaceSelection(wrapInline('**', '**'));
        } else if (ctrl && e.key.toLowerCase() === 'i') {
            e.preventDefault();
            replaceSelection(wrapInline('*', '*'));
        }
    });
});