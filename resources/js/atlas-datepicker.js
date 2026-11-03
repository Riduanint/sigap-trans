/**
 * Atlas Datepicker Controller: Flatpickr Integration with Indonesian Locale.
 * SIGAP-TRANS Kalsel.
 */
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';

export function initAtlasDatepickers(root = document) {
    const dateInputs = root.querySelectorAll('input.atlas-datepicker, input[data-atlas-datepicker]');
    if (!dateInputs.length) return;

    flatpickr.localize(Indonesian);

    const instances = new Map();

    dateInputs.forEach((input) => {
        if (input._flatpickr) return;

        const role = input.dataset.dateRole; // 'start' or 'end'
        const pairSelector = input.dataset.datePair;

        const fp = flatpickr(input, {
            locale: Indonesian,
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'j F Y',
            altInputClass: 'atlas-datepicker-alt',
            allowInput: false,
            disableMobile: true,
            prevArrow: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M15 18l-6-6 6-6"/></svg>',
            nextArrow: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M9 18l6-6-6-6"/></svg>',
            onChange: (selectedDates, dateStr, instance) => {
                // Sinkronkan batasan rentang jika berpasangan
                if (pairSelector) {
                    const pairEl = document.querySelector(pairSelector);
                    if (pairEl && pairEl._flatpickr) {
                        if (role === 'start') {
                            pairEl._flatpickr.set('minDate', selectedDates[0] || null);
                        } else if (role === 'end') {
                            pairEl._flatpickr.set('maxDate', selectedDates[0] || null);
                        }
                    }
                }

                // Picu event change pada input asli agar atlas-table.js mendeteksi
                instance.input.dispatchEvent(new Event('change', { bubbles: true }));
            },
            onReady: (selectedDates, dateStr, instance) => {
                if (pairSelector && selectedDates[0]) {
                    const pairEl = document.querySelector(pairSelector);
                    if (pairEl && pairEl._flatpickr) {
                        if (role === 'start') {
                            pairEl._flatpickr.set('minDate', selectedDates[0]);
                        } else if (role === 'end') {
                            pairEl._flatpickr.set('maxDate', selectedDates[0]);
                        }
                    }
                }
            }
        });

        instances.set(input.id || input.name, fp);
    });

    return instances;
}

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => initAtlasDatepickers());
}
