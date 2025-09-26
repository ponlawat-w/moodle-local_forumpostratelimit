// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * JavaScript interjector for forum view page
 *
 * @module      local/forumpostratelimit
 * @copyright   2025 Ponlawat Weerapanpisit <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Disable input elements for posting a new post in a forum view page.
 * @param {string} message Display message
 */
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

/**
 * Disable input elements for posting a new post in a forum discussion page.
 * @param {string} message Display message
 */
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
        const {
            target
        } = e;
        if (target.attributes.getNamedItem('data-action')?.value === 'collapsible-link') {
            setInterval(disableforms, 500);
        }
    });
};
