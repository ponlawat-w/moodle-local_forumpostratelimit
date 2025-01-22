export const disableforumview = message => {
    const form = document.getElementById('mformforum');
    if (!form) {
        return;
    }
    const elements = form.querySelectorAll('input, textarea, button');
    for (const element of elements) {
        element.disabled = true;
    }
    const editor = form.querySelector('[data-fieldtype=editor]');
    if (editor) {
        editor.innerHTML = `<div style="display: none;">${editor.innerHTML}</div>`
            + `<p class="text-danger">${message}</p>`;
    }
};

export const disableforumdiscuss = message => {
    const disableforms = () => {
        const forms = document.querySelectorAll('[data-content=inpage-reply-form]');
        for (const form of forms) {
            const elements = form.querySelectorAll('input, textarea, button');
            for (const element of elements) {
                element.disabled = true;
            }
            const textarea = form.querySelector('textarea');
            if (textarea) {
                textarea.value = message;
                textarea.classList.add('text-danger');
            }
        }
    };
    document.addEventListener('click', e => {
        /** @type {{ target: HTMLAnchorElement }} */
        const { target } = e;
        if (target.attributes.getNamedItem('data-action')?.value === 'collapsible-link') {
            setInterval(disableforms, 500);
        }
    });
};
