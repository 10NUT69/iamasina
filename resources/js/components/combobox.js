const instances = new WeakMap();

function normaliseText(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

function normaliseOption(option) {
    return {
        value: String(option.value ?? option.id ?? ''),
        label: String(option.label ?? option.name ?? option.nume ?? ''),
        name: String(option.name ?? option.label ?? option.nume ?? ''),
        slug: String(option.slug ?? ''),
        group: option.group ? String(option.group) : '',
    };
}

class HybridCombobox {
    constructor(root) {
        this.root = root;
        this.hidden = root.querySelector('[data-combobox-value]');
        this.input = root.querySelector('[data-combobox-input]');
        this.control = root.querySelector('[data-combobox-control]');
        this.listbox = root.querySelector('[data-combobox-listbox]');
        this.clearButton = root.querySelector('[data-combobox-clear]');
        this.toggleButton = root.querySelector('[data-combobox-toggle]');
        this.label = root.dataset.comboboxLabel || this.hidden?.dataset.comboboxLabel || '';
        this.placeholder = root.dataset.comboboxPlaceholder || this.label;
        this.searchable = root.dataset.comboboxSearchable !== 'false';
        this.touchPickerQuery = window.matchMedia?.('(hover: none), (pointer: coarse)') || null;
        this.options = this.readOptionsFromDom();
        this.filteredOptions = [...this.options];
        this.activeIndex = -1;
        this.suppressHiddenSync = false;

        if (!this.root || !this.hidden || !this.input || !this.listbox) {
            return;
        }

        this.bindEvents();
        this.syncInputMode();
        this.syncDisabled();
        this.setValue(this.hidden.value || '', { dispatch: false, keepQuery: false });

        this.observer = new MutationObserver(() => {
            this.syncDisabled();
            this.root.classList.toggle('is-invalid', this.hidden.getAttribute('aria-invalid') === 'true');
        });
        this.observer.observe(this.hidden, {
            attributes: true,
            attributeFilter: ['disabled', 'aria-invalid', 'value'],
        });
    }

    usesTouchPicker() {
        return !!this.touchPickerQuery?.matches;
    }

    syncInputMode() {
        const usePicker = this.usesTouchPicker();
        this.input.readOnly = !this.searchable || usePicker;
        this.input.inputMode = usePicker ? 'none' : 'text';
    }

    focusInputIfSearchable() {
        this.syncInputMode();

        if (!this.usesTouchPicker() && !this.input.disabled && !this.input.readOnly) {
            this.input.focus();
            return;
        }

        this.input.blur();
    }

    readOptionsFromDom() {
        return Array.from(this.root.querySelectorAll('[data-combobox-option]')).map((option) => normaliseOption({
            value: option.dataset.value,
            label: option.dataset.label || option.textContent,
            name: option.dataset.name || option.dataset.label || option.textContent,
            slug: option.dataset.slug,
            group: option.closest('[data-combobox-group]')?.querySelector('.ia-combobox__group-label')?.textContent || '',
        })).filter((option) => option.label !== '');
    }

    bindEvents() {
        this.touchPickerQuery?.addEventListener?.('change', () => this.syncInputMode());

        this.control.addEventListener('pointerdown', (event) => {
            if (!this.usesTouchPicker() || this.hidden.disabled) return;
            if (event.target.closest('[data-combobox-clear]')) return;

            event.preventDefault();
            this.toggle();
            this.input.blur();
        });

        this.control.addEventListener('mousedown', (event) => {
            if (this.hidden.disabled) return;
            if (this.usesTouchPicker()) return;
            if (event.target.closest('[data-combobox-clear]')) return;
            event.preventDefault();
            this.open();
            this.focusInputIfSearchable();
        });

        this.toggleButton?.addEventListener('click', (event) => {
            event.preventDefault();
            if (this.hidden.disabled) return;
            this.toggle();
            this.focusInputIfSearchable();
        });

        this.clearButton?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.clear();
            this.focusInputIfSearchable();
        });

        this.input.addEventListener('focus', () => {
            if (!this.hidden.disabled) {
                this.open();
            }
        });

        this.input.addEventListener('input', () => {
            if (!this.searchable) {
                this.input.value = this.selectedOption()?.label || '';
                return;
            }

            const selected = this.selectedOption();
            if (selected && this.input.value !== selected.label) {
                this.setValue('', { dispatch: true, keepQuery: true });
            }

            this.root.classList.toggle('has-query', this.input.value.trim() !== '');
            this.filter(this.input.value);
            this.open();
        });

        this.input.addEventListener('keydown', (event) => {
            if (this.hidden.disabled) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.open();
                this.moveActive(1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.open();
                this.moveActive(-1);
            } else if (event.key === 'Enter') {
                if (!this.root.classList.contains('is-open')) return;
                event.preventDefault();
                const option = this.filteredOptions[this.activeIndex] || this.filteredOptions[0];
                if (option) {
                    this.setValue(option.value);
                    this.close();
                }
            } else if (event.key === 'Escape') {
                event.preventDefault();
                this.close();
                const selected = this.selectedOption();
                this.input.value = selected?.label || '';
                this.root.classList.toggle('has-query', false);
            }
        });

        this.hidden.addEventListener('change', () => {
            if (this.suppressHiddenSync) return;
            this.setValue(this.hidden.value || '', { dispatch: false, keepQuery: false });
        });
    }

    syncDisabled() {
        const disabled = this.hidden.disabled;
        this.input.disabled = disabled;
        if (this.toggleButton) this.toggleButton.disabled = disabled;
        if (this.clearButton) this.clearButton.disabled = disabled;
        this.root.classList.toggle('is-disabled', disabled);
        this.input.setAttribute('aria-disabled', disabled ? 'true' : 'false');

        if (disabled) {
            this.close();
        }
    }

    selectedOption() {
        const value = String(this.hidden.value || '');
        if (!value) return null;

        return this.options.find((option) => option.value === value) || null;
    }

    clearSelectedMeta() {
        delete this.hidden.dataset.selectedLabel;
        delete this.hidden.dataset.selectedName;
        delete this.hidden.dataset.selectedSlug;
    }

    setSelectedMeta(option) {
        this.hidden.dataset.selectedLabel = option.label;
        this.hidden.dataset.selectedName = option.name || option.label;
        this.hidden.dataset.selectedSlug = option.slug || '';
    }

    setValue(value, { dispatch = true, keepQuery = false } = {}) {
        const nextValue = String(value || '');
        const option = this.options.find((item) => item.value === nextValue);

        this.hidden.value = option ? option.value : '';

        if (option) {
            this.setSelectedMeta(option);
            if (!keepQuery) {
                this.input.value = option.label;
            }
        } else {
            this.clearSelectedMeta();
            if (!keepQuery) {
                this.input.value = '';
            }
        }

        this.root.classList.toggle('has-value', !!option);
        this.root.classList.toggle('has-query', !option && this.input.value.trim() !== '');
        if (this.clearButton) {
            this.clearButton.hidden = !option;
        }

        this.filter(keepQuery ? this.input.value : '');
        this.updateSelectedStates();

        if (dispatch) {
            this.suppressHiddenSync = true;
            this.hidden.dispatchEvent(new Event('input', { bubbles: true }));
            this.hidden.dispatchEvent(new Event('change', { bubbles: true }));
            this.suppressHiddenSync = false;
        }
    }

    clear({ dispatch = true } = {}) {
        this.setValue('', { dispatch });
        this.input.value = '';
        this.filter('');
        this.open();
    }

    filter(query) {
        const needle = normaliseText(query);
        const matches = !needle
            ? [...this.options]
            : this.options.filter((option) => normaliseText(`${option.label} ${option.name}`).includes(needle));

        if (needle) {
            const seenValues = new Set();
            this.filteredOptions = matches.filter((option) => {
                if (seenValues.has(option.value)) {
                    return false;
                }

                seenValues.add(option.value);
                return true;
            });
        } else {
            this.filteredOptions = matches;
        }

        this.activeIndex = this.filteredOptions.length ? 0 : -1;
        this.renderOptions();
    }

    groupedOptions() {
        const groups = [];

        this.filteredOptions.forEach((option) => {
            const groupName = option.group || '';
            let group = groups.find((item) => item.label === groupName);
            if (!group) {
                group = { label: groupName, options: [] };
                groups.push(group);
            }
            group.options.push(option);
        });

        return groups;
    }

    renderOptions() {
        this.listbox.innerHTML = '';

        if (!this.filteredOptions.length) {
            const empty = document.createElement('div');
            empty.className = 'ia-combobox__empty';
            empty.textContent = 'Nicio optiune gasita';
            this.listbox.appendChild(empty);
            this.input.removeAttribute('aria-activedescendant');
            return;
        }

        let optionIndex = 0;
        this.groupedOptions().forEach((group) => {
            const groupEl = document.createElement('div');
            groupEl.className = 'ia-combobox__group';

            if (group.label) {
                const labelEl = document.createElement('div');
                labelEl.className = 'ia-combobox__group-label';
                labelEl.textContent = group.label;
                groupEl.appendChild(labelEl);
            }

            group.options.forEach((option) => {
                const button = document.createElement('button');
                const id = `${this.input.id}-option-${optionIndex}`;
                button.type = 'button';
                button.id = id;
                button.className = 'ia-combobox__option';
                button.textContent = option.label;
                button.dataset.value = option.value;
                button.dataset.label = option.label;
                button.dataset.name = option.name || option.label;
                button.dataset.slug = option.slug || '';
                button.setAttribute('role', 'option');
                button.setAttribute('aria-selected', this.hidden.value === option.value ? 'true' : 'false');

                if (optionIndex === this.activeIndex) {
                    button.classList.add('is-active');
                    this.input.setAttribute('aria-activedescendant', id);
                }

                if (this.hidden.value === option.value) {
                    button.classList.add('is-selected');
                }

                button.addEventListener('mousedown', (event) => event.preventDefault());
                button.addEventListener('click', () => {
                    this.setValue(option.value);
                    this.close();
                    this.focusInputIfSearchable();
                });

                groupEl.appendChild(button);
                optionIndex++;
            });

            this.listbox.appendChild(groupEl);
        });
    }

    updateSelectedStates() {
        this.listbox.querySelectorAll('[data-value]').forEach((option) => {
            const selected = option.dataset.value === this.hidden.value && this.hidden.value !== '';
            option.classList.toggle('is-selected', selected);
            option.setAttribute('aria-selected', selected ? 'true' : 'false');
        });
    }

    open() {
        if (this.hidden.disabled) return;

        document.querySelectorAll('[data-combobox].is-open').forEach((root) => {
            if (root !== this.root) {
                instances.get(root)?.close();
            }
        });

        this.filter(this.searchable ? this.input.value : '');
        this.root.classList.add('is-open');
        this.input.setAttribute('aria-expanded', 'true');
    }

    close() {
        this.root.classList.remove('is-open');
        this.input.setAttribute('aria-expanded', 'false');
        this.input.removeAttribute('aria-activedescendant');
    }

    toggle() {
        if (this.root.classList.contains('is-open')) {
            this.close();
        } else {
            this.open();
        }
    }

    moveActive(direction) {
        if (!this.filteredOptions.length) return;

        this.activeIndex += direction;
        if (this.activeIndex < 0) this.activeIndex = this.filteredOptions.length - 1;
        if (this.activeIndex >= this.filteredOptions.length) this.activeIndex = 0;
        this.renderOptions();

        const active = this.listbox.querySelector('.ia-combobox__option.is-active');
        active?.scrollIntoView({ block: 'nearest' });
    }

    setOptions(options, selectedValue = '', { dispatch = false } = {}) {
        this.options = options.map(normaliseOption).filter((option) => option.label !== '');
        this.setValue(selectedValue, { dispatch });
        this.filter('');
    }

    setDisabled(disabled) {
        this.hidden.disabled = !!disabled;
        this.syncDisabled();
    }

    setInvalid(invalid) {
        this.root.classList.toggle('is-invalid', !!invalid);
        if (invalid) {
            this.hidden.setAttribute('aria-invalid', 'true');
        } else {
            this.hidden.removeAttribute('aria-invalid');
        }
    }
}

function resolveInstance(target) {
    if (!target) return null;

    const element = typeof target === 'string' ? document.getElementById(target) : target;
    if (!element) return null;

    const root = element.matches?.('[data-combobox]')
        ? element
        : element.closest?.('[data-combobox]');

    return root ? instances.get(root) || null : null;
}

function initComboboxes(scope = document) {
    scope.querySelectorAll('[data-combobox]').forEach((root) => {
        if (instances.has(root)) return;
        instances.set(root, new HybridCombobox(root));
    });
}

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-combobox]')) return;
    document.querySelectorAll('[data-combobox].is-open').forEach((root) => {
        instances.get(root)?.close();
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('[data-combobox].is-open').forEach((root) => {
        instances.get(root)?.close();
    });
});

document.addEventListener('DOMContentLoaded', () => initComboboxes());

window.iaCombobox = {
    init: initComboboxes,
    get: resolveInstance,
    setOptions(target, options, selectedValue = '', config = {}) {
        resolveInstance(target)?.setOptions(options, selectedValue, config);
    },
    setValue(target, value, config = {}) {
        resolveInstance(target)?.setValue(value, config);
    },
    clear(target, config = {}) {
        resolveInstance(target)?.clear(config);
    },
    enable(target) {
        resolveInstance(target)?.setDisabled(false);
    },
    disable(target) {
        resolveInstance(target)?.setDisabled(true);
    },
    selectedOption(target) {
        return resolveInstance(target)?.selectedOption() || null;
    },
    setInvalid(target, invalid) {
        resolveInstance(target)?.setInvalid(invalid);
    },
    refresh(target) {
        const instance = resolveInstance(target);
        if (instance) {
            instance.options = instance.readOptionsFromDom();
            instance.setValue(instance.hidden.value || '', { dispatch: false });
        }
    },
};
