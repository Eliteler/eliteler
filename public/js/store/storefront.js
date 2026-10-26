/*
 | GoBiz storefront — the shared cart, product options and WhatsApp checkout of every store theme.
 | Themes only supply HTML (with the data-* hooks below) and styling; all behaviour lives here.
 | Set up by resources/views/templates/stores/shared/scripts.blade.php:  GoBizStore.init(config, hooks)
 |
 | Cart
 |   [data-cart-items]                 list; one row per item from <template data-cart-item> (anywhere on the page)
 |     inside the template:            [data-cart-image] <img>, [data-cart-name], [data-cart-variant] (hidden without option; text goes
 |                                     into its [data-cart-variant-name] when it has one),
 |                                     [data-cart-subtitle], [data-cart-unit-price], [data-cart-line-total], [data-cart-qty],
 |                                     buttons [data-cart-decrease] [data-cart-increase] [data-cart-remove]
 |   [data-cart-count]                 total quantity        [data-cart-total]   grand total
 |   [data-cart-when-empty] / [data-cart-when-filled]       shown only when the cart is empty / has items
 |   [data-cart-open]                  opens the cart        [data-cart-checkout] goes to checkout (checks opening hours)
 |
 | Add to cart from listings
 |   [data-add-to-cart] data-product-id data-name data-price data-image data-subtitle; data-then="cart" also opens the cart
 |
 | Product page
 |   [data-product] data-product-id data-name data-price data-image data-subtitle data-initial-option data-active-class
 |     a JSON script tag marked data-product-options: [{id,name,price,regularPrice,inStock}]
 |     [data-option="<index>"]         option buttons (sold-out ones rendered disabled)
 |     [data-option-label] [data-product-price] [data-product-regular-price] [data-product-stock] (+ [data-product-stock-text])
 |     [data-product-discount] (+ [data-product-discount-value])  percent off, hidden when not on sale (optional)
 |     [data-qty-value] with [data-qty-step="-1"|"1"]
 |     [data-product-add]              adds the product; data-then="cart" also opens the cart
 |
 | Checkout
 |   form [data-order-form]: customer_name, customer_phone, delivery_address, delivery_method (radios), delivery_note
 |   [data-address-field]              wrapper of the address, hidden for take away / dine in
 |
 | Drawer / modal: [data-cart-drawer] and [data-order-modal] use Bootstrap when it is on the page;
 | other themes pass hooks: openCart, closeCart, openCheckout, closeCheckout, notify(message, type).
 */
(function (window, document) {
    "use strict";

    const DELIVERY = "Order For Delivery";

    let config = null;
    let hooks = {};
    let items = [];
    let orderBusy = false;

    // ------------------------------------------------------------------ helpers

    function escapeHtml(value) {
        return String(value ?? "").replace(
            /[&<>"'`]/g,
            (c) =>
                ({
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#39;",
                    "`": "&#96;",
                })[c],
        );
    }

    /** Same rules as formatCurrencyBase() in app/helpers.php */
    function formatMoney(amount) {
        const money = config.money;
        const number = Number(amount) || 0;
        const separators = {
            "1,234,567.89": [",", "."],
            "12,34,567.89": [",", ".", true],
            "1.234.567,89": [".", ","],
            "1 234 567,89": [" ", ","],
            "1'234'567.89": ["'", "."],
        };
        const [group, point, indian] =
            separators[money.format] || separators["1,234,567.89"];
        const [whole, fraction] = Math.abs(number)
            .toFixed(money.decimals)
            .split(".");

        const grouped =
            indian && whole.length > 3
                ? whole.slice(0, -3).replace(/\B(?=(\d{2})+(?!\d))/g, group) +
                  group +
                  whole.slice(-3)
                : whole.replace(/\B(?=(\d{3})+(?!\d))/g, group);

        const formatted =
            (number < 0 ? "-" : "") +
            grouped +
            (fraction ? point + fraction : "");

        return money.symbolFirst
            ? money.symbol + formatted
            : formatted + money.symbol;
    }

    function text(key) {
        return (config.texts && config.texts[key]) || key;
    }

    function notify(message, type = "success") {
        if (hooks.notify) {
            hooks.notify(message, type);
            return;
        }

        let toast = document.getElementById("gobiz-store-toast");
        if (!toast) {
            toast = Object.assign(document.createElement("div"), {
                id: "gobiz-store-toast",
                role: "status",
            });
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.dataset.type = type;
        toast.classList.add("is-visible");
        clearTimeout(toast.hideTimer);
        toast.hideTimer = setTimeout(
            () => toast.classList.remove("is-visible"),
            2500,
        );
    }

    function all(selector, root = document) {
        return Array.from(root.querySelectorAll(selector));
    }

    function show(elements, visible) {
        elements.forEach((element) => (element.hidden = !visible));
    }

    function bootstrapComponent(name, element) {
        return element && window.bootstrap && window.bootstrap[name]
            ? window.bootstrap[name].getOrCreateInstance(element)
            : null;
    }

    // ------------------------------------------------------------------ cart storage

    function keyFor(productId, variantId) {
        return variantId ? `${productId}_var_${variantId}` : String(productId);
    }

    /** Reads carts saved by this script and by the older per-theme carts (same storage key) */
    function normalize(raw) {
        if (!raw || typeof raw !== "object") return null;

        let productId = raw.productId ?? raw.product_id ?? raw.id;
        let variantId = raw.variantId ?? raw.variant_id ?? null;
        if (productId === undefined || productId === null || productId === "")
            return null;

        productId = String(productId);
        const combined = productId.match(/^(.+)_var_(.+)$/);
        if (combined) {
            productId = combined[1];
            variantId = variantId ?? combined[2];
        }

        const qty = parseInt(raw.qty ?? raw.quantity, 10);
        const price = Number(raw.price);
        if (!(qty > 0) || !Number.isFinite(price)) return null;

        return {
            key: keyFor(productId, variantId),
            productId,
            variantId:
                variantId !== null && variantId !== undefined
                    ? String(variantId)
                    : null,
            name: String(raw.name ?? raw.product_name ?? raw.title ?? ""),
            variantName: raw.variantName ?? raw.variant_name ?? null,
            price,
            qty,
            image: String(raw.image ?? raw.product_image ?? raw.img ?? ""),
            subtitle: String(raw.subtitle ?? ""),
        };
    }

    function load() {
        try {
            const saved = JSON.parse(
                window.localStorage.getItem(config.storageKey) || "[]",
            );
            items = (Array.isArray(saved) ? saved : [])
                .map(normalize)
                .filter(Boolean);
        } catch (error) {
            items = [];
        }
    }

    function save() {
        try {
            window.localStorage.setItem(
                config.storageKey,
                JSON.stringify(items),
            );
        } catch (error) {
            // Private mode or full storage: the cart still works for this page
        }
        render();
        document.dispatchEvent(
            new CustomEvent("gobiz:cart-change", {
                detail: { items: items.slice() },
            }),
        );
    }

    function addItem(item, qty = 1) {
        const entry = normalize({ ...item, qty });
        if (!entry) return;

        const existing = items.find((line) => line.key === entry.key);
        if (existing) {
            existing.qty += entry.qty;
            notify(text("cartUpdated"));
        } else {
            items.push(entry);
            notify(text("addedToCart"));
        }
        save();
    }

    function changeQty(key, delta) {
        const line = items.find((entry) => entry.key === key);
        if (!line) return;

        line.qty += delta;
        if (line.qty <= 0) {
            items = items.filter((entry) => entry.key !== key);
            notify(text("itemRemoved"));
        }
        save();
    }

    function removeItem(key) {
        items = items.filter((entry) => entry.key !== key);
        notify(text("itemRemoved"));
        save();
    }

    const totalQty = () => items.reduce((sum, line) => sum + line.qty, 0);
    const totalPrice = () =>
        items.reduce((sum, line) => sum + line.qty * line.price, 0);

    // ------------------------------------------------------------------ cart display

    function render() {
        const template = document.querySelector("template[data-cart-item]");

        all("[data-cart-items]").forEach((list) => {
            list.replaceChildren();
            if (!template) return;

            items.forEach((line) => {
                const row = template.content.firstElementChild.cloneNode(true);
                row.dataset.cartKey = line.key;

                const fill = (selector, value) =>
                    all(selector, row).forEach(
                        (element) => (element.textContent = value),
                    );
                fill("[data-cart-name]", line.name);
                fill("[data-cart-subtitle]", line.subtitle);
                fill("[data-cart-unit-price]", formatMoney(line.price));
                fill(
                    "[data-cart-line-total]",
                    formatMoney(line.price * line.qty),
                );
                fill("[data-cart-qty]", line.qty);
                all("[data-cart-variant]", row).forEach((element) => {
                    (
                        element.querySelector("[data-cart-variant-name]") ||
                        element
                    ).textContent = line.variantName || "";
                    element.hidden = !line.variantName;
                });
                all("[data-cart-subtitle]", row).forEach(
                    (element) => (element.hidden = !line.subtitle),
                );
                all("img[data-cart-image]", row).forEach((image) => {
                    image.src = line.image || image.dataset.fallback || "";
                    image.alt = line.name;
                    if (image.dataset.fallback) {
                        image.onerror = () => {
                            image.onerror = null;
                            image.src = image.dataset.fallback;
                        };
                    }
                });

                list.appendChild(row);
            });
        });

        // Universal Real-Time Badge Count Synchronization (Header, Bottom Bar & Legacy Elements)
        const currentCount = totalQty();
        const allBadgeSelectors = [
            "[data-cart-count]",
            "[data-cart-badge]",
            "#bottomBarCartBadge",
            "#headerCartBadge",
            "#badge",
            ".store-bottom-cart-badge",
            ".store-bottom-cart-count-badge",
            ".store-cart-counter-badge",
        ];

        all(allBadgeSelectors.join(", ")).forEach((element) => {
            element.textContent = currentCount;
            // Show badge if count > 0, otherwise hide badge smoothly
            if (
                element.id === "bottomBarCartBadge" ||
                element.id === "badge" ||
                element.classList.contains("store-bottom-cart-badge") ||
                element.classList.contains("store-bottom-cart-count-badge")
            ) {
                element.style.display =
                    currentCount > 0 ? "inline-flex" : "none";
            }
        });

        all("[data-cart-total]").forEach(
            (element) => (element.textContent = formatMoney(totalPrice())),
        );
        show(all("[data-cart-when-empty]"), items.length === 0);
        show(all("[data-cart-when-filled]"), items.length > 0);
    }

    // ------------------------------------------------------------------ drawer and checkout modal

    function openCart() {
        if (hooks.openCart) return hooks.openCart();
        bootstrapComponent(
            "Offcanvas",
            document.querySelector("[data-cart-drawer]") ||
                document.getElementById("offcanvasEnd"),
        )?.show();
    }

    function closeCart() {
        if (hooks.closeCart) return hooks.closeCart();
        bootstrapComponent(
            "Offcanvas",
            document.querySelector("[data-cart-drawer]") ||
                document.getElementById("offcanvasEnd"),
        )?.hide();
    }

    function openCheckout() {
        if (hooks.openCheckout) return hooks.openCheckout();
        bootstrapComponent(
            "Modal",
            document.querySelector("[data-order-modal]") ||
                document.getElementById("orderModal"),
        )?.show();
    }

    function closeCheckout() {
        if (hooks.closeCheckout) return hooks.closeCheckout();
        bootstrapComponent(
            "Modal",
            document.querySelector("[data-order-modal]") ||
                document.getElementById("orderModal"),
        )?.hide();
    }

    /** Opening hours on the visitor's clock; no hours set = always open; overnight hours supported */
    function isOpen(now = new Date()) {
        const hours = config.hours;
        if (
            !hours ||
            typeof hours !== "object" ||
            Object.keys(hours).length === 0
        )
            return true;

        const days = [
            "sunday",
            "monday",
            "tuesday",
            "wednesday",
            "thursday",
            "friday",
            "saturday",
        ];
        const valid = (span) => span && span.start && span.end;
        const today = hours[days[now.getDay()]];
        const yesterday = hours[days[(now.getDay() + 6) % 7]];
        const time =
            String(now.getHours()).padStart(2, "0") +
            ":" +
            String(now.getMinutes()).padStart(2, "0");

        // Late hours of the day before (e.g. Friday 18:00–02:00 still open at 01:30 on Saturday)
        if (
            valid(yesterday) &&
            yesterday.start > yesterday.end &&
            time <= yesterday.end
        )
            return true;
        if (!valid(today)) return false;

        return today.start <= today.end
            ? time >= today.start && time <= today.end
            : time >= today.start;
    }

    function checkout() {
        if (items.length === 0) return;

        if (!isOpen()) {
            notify(text("closed"), "error");
            return;
        }

        closeCart();
        setTimeout(openCheckout, 300);
    }

    function selectedDelivery(form) {
        const checked = form.querySelector(
            'input[name="delivery_method"]:checked',
        );
        if (checked) return checked.value;

        const firstEnabled = Object.keys(config.delivery || {}).find(
            (method) => config.delivery[method],
        );
        return firstEnabled || DELIVERY;
    }

    /** The address is only needed when the order is delivered */
    function syncAddressField(form) {
        const needed = selectedDelivery(form) === DELIVERY;
        all("[data-address-field]", form).forEach(
            (field) => (field.hidden = !needed),
        );
        all('[name="delivery_address"]', form).forEach(
            (input) => (input.required = needed),
        );
    }

    /** Chat link to the store's WhatsApp number with the text filled in */
    function whatsappUrl(message) {
        return `https://api.whatsapp.com/send/?phone=${encodeURIComponent(config.whatsapp.number)}&text=${encodeURIComponent(message)}`;
    }

    function whatsappText(order) {
        const line = "- - - - - - - - - - - - - - - - - - - -";
        let message = `🎉 *${text("newOrder")}* \n${line}\n📦 *${text("orderDetails")}* \n\n`;

        items.forEach((item, index) => {
            const option = item.variantName ? ` [${item.variantName}]` : "";
            message += `${index + 1}. *${item.name}*${option}\n`;
            message += `   └ ${item.qty} X ${formatMoney(item.price)} = ${formatMoney(item.price * item.qty)}\n`;
        });

        message += `${line}\n*${text("total")}* : *${formatMoney(totalPrice())}*\n\n`;
        message += `📞 *${text("customerDetails")}* \n`;
        message += `${text("customerName")} : ${order.customer_name}\n`;
        message += `${text("contactNumber")} : ${order.customer_phone}\n`;
        if (order.delivery_address)
            message += `${text("deliveryAddress")} : ${order.delivery_address}\n`;
        message += `${text("deliveryType")} : ${text(order.delivery_method)}\n`;
        if (order.delivery_note)
            message += `${text("notes")} : ${order.delivery_note}\n`;
        if (config.whatsapp.message)
            message += `\n*${config.whatsapp.message}*`;

        return message;
    }

    function csrfToken() {
        return (
            config.csrf ||
            document.querySelector('meta[name="csrf-token"]')?.content ||
            ""
        );
    }

    function setBusy(form, busy) {
        orderBusy = busy;
        all('[type="submit"], [data-order-submit]', form).forEach((button) => {
            button.disabled = busy;
            button.setAttribute("aria-busy", busy ? "true" : "false");
        });
    }

    async function placeOrder(form) {
        if (orderBusy || items.length === 0) return;

        const data = new FormData(form);
        const order = {
            customer_name: String(data.get("customer_name") || "").trim(),
            customer_phone: String(data.get("customer_phone") || "").trim(),
            delivery_address: String(data.get("delivery_address") || "").trim(),
            delivery_method: selectedDelivery(form),
            delivery_note: String(data.get("delivery_note") || "").trim(),
        };

        const addressNeeded = order.delivery_method === DELIVERY;
        if (
            order.customer_name.length < 2 ||
            order.customer_phone.replace(/\D/g, "").length < 6 ||
            (addressNeeded && !order.delivery_address)
        ) {
            notify(text("fillFields"), "error");
            return;
        }
        if (!addressNeeded) order.delivery_address = "";

        if (!isOpen()) {
            notify(text("closed"), "error");
            return;
        }

        // Opened now, while the click still counts as the shopper's action, so it isn't blocked as a popup
        const whatsappWindow = config.whatsapp.number
            ? window.open("", "_blank")
            : null;
        const message = whatsappText(order);
        setBusy(form, true);

        try {
            const response = await fetch(config.orderUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken(),
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    ...order,
                    store_id: config.storeId,
                    total_price: totalPrice(),
                    order_items: items.map((item) => ({
                        product_id: item.productId,
                        variant_id: item.variantId,
                        product_name: item.name,
                        variant_name: item.variantName,
                        price: item.price,
                        qty: item.qty,
                        product_image: item.image,
                        subtitle: item.subtitle,
                    })),
                }),
            });
            const result = await response.json().catch(() => ({}));

            if (!response.ok || result.status !== "success") {
                throw new Error(result.message || text("orderFailed"));
            }

            if (config.whatsapp.number) {
                const url = whatsappUrl(message);
                if (whatsappWindow) whatsappWindow.location.href = url;
                else window.location.href = url;
            }

            items = [];
            save();
            form.reset();
            syncAddressField(form);
            closeCheckout();
            notify(text("orderPlaced"));
        } catch (error) {
            if (whatsappWindow) whatsappWindow.close();
            notify(error.message || text("orderFailed"), "error");
        } finally {
            setBusy(form, false);
        }
    }

    // ------------------------------------------------------------------ product page

    function initProduct(root) {
        const optionsScript = root.querySelector(
            "script[data-product-options]",
        );
        const options = optionsScript
            ? JSON.parse(optionsScript.textContent || "[]")
            : [];
        const activeClass = root.dataset.activeClass || "active";
        const startIndex = parseInt(root.dataset.initialOption || "0", 10);
        let current =
            options.length && options[startIndex] && options[startIndex].inStock
                ? options[startIndex]
                : null;
        let qty = 1;

        const addButtons = all("[data-product-add]", root);
        const setBuyable = (buyable) =>
            addButtons.forEach((button) => (button.disabled = !buyable));

        function select(index) {
            const option = options[index];
            if (!option || !option.inStock) return;
            current = option;

            all("[data-option]", root).forEach((button) => {
                const active = Number(button.dataset.option) === index;
                button.classList.toggle(activeClass, active);
                button.setAttribute("aria-pressed", active ? "true" : "false");
            });
            all("[data-option-label]", root).forEach(
                (element) => (element.textContent = option.name),
            );
            all("[data-product-price]", root).forEach(
                (element) => (element.textContent = formatMoney(option.price)),
            );
            all("[data-product-regular-price]", root).forEach((element) => {
                const onSale =
                    option.regularPrice && option.regularPrice > option.price;
                element.textContent = onSale
                    ? formatMoney(option.regularPrice)
                    : "";
                element.hidden = !onSale;
            });
            all("[data-product-discount]", root).forEach((element) => {
                const onSale =
                    option.regularPrice && option.regularPrice > option.price;
                const value =
                    element.querySelector("[data-product-discount-value]") ||
                    element;
                value.textContent = onSale
                    ? Math.round(
                          ((option.regularPrice - option.price) /
                              option.regularPrice) *
                              100,
                      )
                    : "";
                element.hidden = !onSale;
            });
            all("[data-product-stock]", root).forEach((element) => {
                element.dataset.state = "in";
                const label =
                    element.querySelector("[data-product-stock-text]") ||
                    element;
                label.textContent = text("inStock");
            });
            setBuyable(true);
        }

        root.addEventListener("click", (event) => {
            const optionButton = event.target.closest("[data-option]");
            if (optionButton && root.contains(optionButton)) {
                select(Number(optionButton.dataset.option));
                return;
            }

            const step = event.target.closest("[data-qty-step]");
            if (step && root.contains(step)) {
                qty = Math.max(1, qty + Number(step.dataset.qtyStep));
                all("[data-qty-value]", root).forEach(
                    (element) => (element.textContent = qty),
                );
                return;
            }

            const add = event.target.closest("[data-product-add]");
            if (!add || !root.contains(add) || add.disabled) return;

            if (options.length && !current) {
                notify(text("optionOutOfStock"), "error");
                return;
            }

            addItem(
                {
                    productId: root.dataset.productId,
                    variantId: current ? current.id : null,
                    name: root.dataset.name,
                    variantName: current ? current.name : null,
                    price: current ? current.price : Number(root.dataset.price),
                    image: root.dataset.image,
                    subtitle: root.dataset.subtitle,
                },
                qty,
            );

            if (add.dataset.then === "cart") openCart();
        });
    }

    // ------------------------------------------------------------------ wiring

    function bind() {
        document.addEventListener("click", (event) => {
            const target = event.target;

            const listingAdd = target.closest("[data-add-to-cart]");
            if (listingAdd && !listingAdd.disabled) {
                event.preventDefault();
                const data = listingAdd.dataset;
                addItem(
                    {
                        productId: data.productId,
                        name: data.name,
                        price: Number(data.price),
                        image: data.image,
                        subtitle: data.subtitle,
                    },
                    1,
                );
                if (data.then === "cart") openCart();
                return;
            }

            const row = target.closest("[data-cart-key]");
            if (row) {
                if (target.closest("[data-cart-increase]"))
                    return changeQty(row.dataset.cartKey, 1);
                if (target.closest("[data-cart-decrease]"))
                    return changeQty(row.dataset.cartKey, -1);
                if (target.closest("[data-cart-remove]"))
                    return removeItem(row.dataset.cartKey);
            }

            if (target.closest("[data-cart-open]")) {
                event.preventDefault();
                openCart();
                return;
            }

            if (target.closest("[data-cart-checkout]")) {
                event.preventDefault();
                checkout();
            }
        });

        all("form[data-order-form]").forEach((form) => {
            form.addEventListener("submit", (event) => {
                event.preventDefault();
                placeOrder(form);
            });
            form.addEventListener("change", (event) => {
                if (event.target.name === "delivery_method")
                    syncAddressField(form);
            });
            syncAddressField(form);
        });

        all("[data-product]").forEach(initProduct);
    }

    function injectStyles() {
        if (document.getElementById("gobiz-storefront-style")) return;
        const style = document.createElement("style");
        style.id = "gobiz-storefront-style";
        style.textContent = `
            [data-cart-items] [hidden], [data-cart-when-empty][hidden], [data-cart-when-filled][hidden],
            [data-product] [hidden], [data-address-field][hidden] { display: none !important; }
            #gobiz-store-toast { position: fixed; left: 50%; bottom: 88px; transform: translate(-50%, 20px); z-index: 2000;
                max-width: calc(100vw - 32px); padding: 10px 16px; border-radius: 10px; background: #0f172a; color: #fff;
                font-size: 14px; box-shadow: 0 10px 30px rgba(0,0,0,.2); opacity: 0; pointer-events: none; transition: all .25s ease; }
            #gobiz-store-toast.is-visible { opacity: 1; transform: translate(-50%, 0); }
            #gobiz-store-toast[data-type="error"] { background: #b91c1c; }`;
        document.head.appendChild(style);
    }

    function init(settings, themeHooks = {}) {
        config = settings;
        hooks = themeHooks;

        const start = () => {
            injectStyles();
            load();
            bind();
            render();
        };

        if (document.readyState === "loading")
            document.addEventListener("DOMContentLoaded", start);
        else start();
    }

    window.GoBizStore = {
        init,
        formatMoney,
        escapeHtml,
        openCart,
        checkout,
        isOpen,
        addItem,
        whatsappUrl,
        items: () => items.slice(),
    };
})(window, document);

// ------------------------------------------------------------------ Swiper Loop Warning Auto-Fix
document.addEventListener("DOMContentLoaded", function () {
    const swiperContainers = document.querySelectorAll("swiper-container");
    swiperContainers.forEach((container) => {
        const slides = container.querySelectorAll("swiper-slide");
        const slidesPerView = parseInt(
            container.getAttribute("slides-per-view") || "1",
            10,
        );

        if (slides.length <= slidesPerView) {
            container.removeAttribute("loop");
            container.setAttribute("loop", "false");
            if (container.swiper) {
                container.swiper.params.loop = false;
                container.swiper.update();
            }
        }
    });
});

// ------------------------------------------------------------------ PWA Install Prompt Handler
let deferredPrompt = null;
window.addEventListener("beforeinstallprompt", function (event) {
    event.preventDefault();
    deferredPrompt = event;

    const installBtn = document.querySelector("[data-pwa-install]");
    if (installBtn) {
        installBtn.hidden = false;
        installBtn.addEventListener("click", function () {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(() => {
                    deferredPrompt = null;
                });
            }
        });
    }
});

// ------------------------------------------------------------------ Preloader Dismiss
window.addEventListener("load", function () {
    const preloader = document.querySelector(".preloader-wrapper");
    if (preloader) {
        preloader.style.transition = "opacity 0.4s ease";
        preloader.style.opacity = "0";

        setTimeout(function () {
            preloader.style.display = "none";
        }, 400);
    }
});
