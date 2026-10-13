const root = document.querySelector('[data-permiso-pila]');

if (root) {
    const available = root.querySelector('[data-side="available"]');
    const granted = root.querySelector('[data-side="granted"]');
    const select = root.querySelector('select[name="permissions[]"]');
    const form = root.closest('form');
    const anchors = new WeakMap();

    const items = (list) => [...list.querySelectorAll('.permiso-pila__item')];

    const selectedItems = (list) => items(list).filter((item) => item.getAttribute('aria-selected') === 'true');

    const setTabStop = (list, item) => {
        items(list).forEach((el) => {
            el.tabIndex = el === item ? 0 : -1;
        });
        list.tabIndex = item ? -1 : 0;
    };

    const refresh = () => {
        root.querySelector('[data-count="available"]').textContent = String(items(available).length);
        root.querySelector('[data-count="granted"]').textContent = String(items(granted).length);

        available.closest('.permiso-pila__body').querySelector('.permiso-pila__empty').hidden = items(available).length > 0;
        granted.closest('.permiso-pila__body').querySelector('.permiso-pila__empty').hidden = items(granted).length > 0;

        root.querySelector('[data-action="grant"]').disabled = selectedItems(available).length === 0;
        root.querySelector('[data-action="grant-all"]').disabled = items(available).length === 0;
        root.querySelector('[data-action="revoke"]').disabled = selectedItems(granted).length === 0;
        root.querySelector('[data-action="revoke-all"]').disabled = items(granted).length === 0;
    };

    const syncInput = () => {
        select.replaceChildren();
        items(granted).forEach((item) => {
            select.add(new Option(item.dataset.value, item.dataset.value, true, true));
        });
    };

    const pruneGroups = (list) => {
        list.querySelectorAll('.permiso-pila__group').forEach((group) => {
            if (!group.querySelector('.permiso-pila__item')) {
                group.remove();
            }
        });
    };

    const placeItem = (list, item) => {
        const groupName = item.dataset.group;
        let group = [...list.querySelectorAll(':scope > .permiso-pila__group')].find((el) => el.dataset.group === groupName);

        if (!group) {
            group = document.createElement('div');
            group.className = 'permiso-pila__group';
            group.dataset.group = groupName;
            group.dataset.groupOrder = item.dataset.groupOrder;

            const label = document.createElement('div');
            label.className = 'permiso-pila__group-label';
            label.textContent = groupName;

            group.appendChild(label);

            const order = Number(item.dataset.groupOrder);
            const nextGroup = [...list.querySelectorAll(':scope > .permiso-pila__group')]
                .find((el) => Number(el.dataset.groupOrder) > order);

            list.insertBefore(group, nextGroup || null);
        }

        const order = Number(item.dataset.itemOrder);
        const nextItem = [...group.querySelectorAll(':scope > .permiso-pila__item')]
            .find((el) => Number(el.dataset.itemOrder) > order);

        item.setAttribute('aria-selected', 'false');
        item.tabIndex = -1;
        group.insertBefore(item, nextItem || null);
    };

    const transfer = (from, to, onlySelected) => {
        const moving = onlySelected ? selectedItems(from) : items(from);
        if (!moving.length) {
            return;
        }

        moving.forEach((item) => placeItem(to, item));
        pruneGroups(from);
        anchors.delete(from);

        const remaining = items(from);
        setTabStop(from, remaining[0] || null);

        const arrived = items(to);
        const current = arrived.find((el) => el.tabIndex === 0) || arrived[0] || null;
        setTabStop(to, current);

        moving[0]?.scrollIntoView({ block: 'nearest' });
        syncInput();
        refresh();
    };

    const selectItem = (list, item, event) => {
        const all = items(list);
        const anchor = anchors.get(list);

        if (event.shiftKey && anchor && all.includes(anchor)) {
            const start = all.indexOf(anchor);
            const end = all.indexOf(item);
            const [from, to] = start < end ? [start, end] : [end, start];
            all.forEach((el, index) => {
                el.setAttribute('aria-selected', index >= from && index <= to ? 'true' : 'false');
            });
        } else if (event.metaKey || event.ctrlKey) {
            const selected = item.getAttribute('aria-selected') === 'true';
            item.setAttribute('aria-selected', selected ? 'false' : 'true');
            anchors.set(list, item);
        } else {
            all.forEach((el) => el.setAttribute('aria-selected', el === item ? 'true' : 'false'));
            anchors.set(list, item);
        }

        setTabStop(list, item);
        refresh();
    };

    const onKeyDown = (list, event) => {
        const all = items(list);

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'a') {
            event.preventDefault();
            all.forEach((el) => el.setAttribute('aria-selected', 'true'));
            if (all.length) {
                anchors.set(list, all[all.length - 1]);
            }
            refresh();
            return;
        }

        if (event.key === 'Enter') {
            event.preventDefault();
            return;
        }

        if (!['ArrowDown', 'ArrowUp', 'Home', 'End', ' '].includes(event.key)) {
            return;
        }

        event.preventDefault();
        if (!all.length) {
            return;
        }

        const current = all.find((el) => el === document.activeElement) || all.find((el) => el.tabIndex === 0) || all[0];
        const index = Math.max(0, all.indexOf(current));

        if (event.key === ' ') {
            const selected = current.getAttribute('aria-selected') === 'true';
            current.setAttribute('aria-selected', selected ? 'false' : 'true');
            anchors.set(list, current);
            current.focus();
            refresh();
            return;
        }

        let nextIndex = index;
        if (event.key === 'ArrowDown') {
            nextIndex = Math.min(all.length - 1, index + 1);
        }
        if (event.key === 'ArrowUp') {
            nextIndex = Math.max(0, index - 1);
        }
        if (event.key === 'Home') {
            nextIndex = 0;
        }
        if (event.key === 'End') {
            nextIndex = all.length - 1;
        }

        const next = all[nextIndex];

        if (event.shiftKey) {
            const anchor = anchors.get(list) || current;
            anchors.set(list, anchor);
            const start = all.indexOf(anchor);
            const [from, to] = start < nextIndex ? [start, nextIndex] : [nextIndex, start];
            all.forEach((el, i) => el.setAttribute('aria-selected', i >= from && i <= to ? 'true' : 'false'));
        } else if (!(event.ctrlKey || event.metaKey)) {
            all.forEach((el) => el.setAttribute('aria-selected', el === next ? 'true' : 'false'));
            anchors.set(list, next);
        }

        setTabStop(list, next);
        next.focus();
        refresh();
    };

    [available, granted].forEach((list) => {
        const all = items(list);
        setTabStop(list, all[0] || null);

        list.addEventListener('click', (event) => {
            const item = event.target.closest('.permiso-pila__item');
            if (!item || !list.contains(item)) {
                return;
            }
            selectItem(list, item, event);
        });

        list.addEventListener('dblclick', (event) => {
            const item = event.target.closest('.permiso-pila__item');
            if (!item || !list.contains(item)) {
                return;
            }

            items(list).forEach((el) => el.setAttribute('aria-selected', el === item ? 'true' : 'false'));
            transfer(list, list === available ? granted : available, true);
        });

        list.addEventListener('keydown', (event) => onKeyDown(list, event));
    });

    root.querySelector('.permiso-pila__actions').addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        if (!button || button.disabled) {
            return;
        }

        const action = button.dataset.action;
        if (action === 'grant') {
            transfer(available, granted, true);
        }
        if (action === 'grant-all') {
            transfer(available, granted, false);
        }
        if (action === 'revoke') {
            transfer(granted, available, true);
        }
        if (action === 'revoke-all') {
            transfer(granted, available, false);
        }
    });

    form?.addEventListener('submit', syncInput);
    syncInput();
    refresh();
}
