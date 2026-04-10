<!DOCTYPE html>
<html class="light icons-loading" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'APOTEK SUMBER SEHAT')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script>
        (function () {
            const root = document.documentElement;
            const revealIcons = function () {
                root.classList.remove("icons-loading");
            };

            if (!("fonts" in document) || typeof document.fonts.load !== "function") {
                revealIcons();
                return;
            }

            Promise.race([
                document.fonts.load('24px "Material Symbols Outlined"'),
                new Promise(function (resolve) {
                    setTimeout(resolve, 2000);
                }),
            ]).finally(revealIcons);
        })();
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-primary-container": "#c4d2ff",
                        "on-primary": "#ffffff",
                        "on-tertiary-fixed-variant": "#812800",
                        "on-tertiary-container": "#ffc6b2",
                        "tertiary-fixed": "#ffdbcf",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-high": "#e7e8e9",
                        "error-container": "#ffdad6",
                        "on-secondary-fixed": "#021945",
                        "surface-container": "#edeeef",
                        "on-surface-variant": "#434654",
                        "surface-variant": "#e1e3e4",
                        "secondary-fixed": "#dae2ff",
                        "primary-fixed": "#dae2ff",
                        "tertiary-container": "#a33500",
                        "surface-dim": "#d9dadb",
                        "primary-container": "#0052cc",
                        "tertiary-fixed-dim": "#ffb59b",
                        "on-secondary-container": "#415382",
                        "on-primary-fixed": "#001848",
                        "surface-tint": "#0c56d0",
                        "on-primary-fixed-variant": "#0040a2",
                        "inverse-surface": "#2e3132",
                        "surface-bright": "#f8f9fa",
                        "outline": "#737685",
                        "on-error": "#ffffff",
                        "on-tertiary": "#ffffff",
                        "secondary-container": "#b6c8fe",
                        "surface": "#f8f9fa",
                        "surface-container-highest": "#e1e3e4",
                        "background": "#f8f9fa",
                        "secondary": "#4c5d8d",
                        "on-secondary": "#ffffff",
                        "on-tertiary-fixed": "#380d00",
                        "outline-variant": "#c3c6d6",
                        "error": "#ba1a1a",
                        "on-error-container": "#93000a",
                        "primary-fixed-dim": "#b2c5ff",
                        "tertiary": "#7b2600",
                        "inverse-on-surface": "#f0f1f2",
                        "surface-container-low": "#f3f4f5",
                        "on-secondary-fixed-variant": "#344573",
                        "on-surface": "#191c1d",
                        "on-background": "#191c1d",
                        "primary": "#003d9b",
                        "secondary-fixed-dim": "#b4c5fb",
                        "inverse-primary": "#b2c5ff"
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px"
                    },
                    fontFamily: {
                        headline: ["Manrope"],
                        body: ["Inter"],
                        label: ["Inter"]
                    }
                }
            }
        };
    </script>
    <style>
        body {
            font-family: "Inter", sans-serif;
        }
        h1, h2, h3, h4 {
            font-family: "Manrope", sans-serif;
        }
        .material-symbols-outlined {
            font-family: "Material Symbols Outlined", sans-serif;
            font-size: 24px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1em;
            height: 1em;
            white-space: nowrap;
            overflow: hidden;
            flex-shrink: 0;
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
        }
        .icons-loading .material-symbols-outlined {
            color: transparent !important;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e1e3e4;
            border-radius: 9999px;
        }
        @yield('page_style')
    </style>
</head>
<body class="@yield('body_class', 'bg-surface text-on-surface')">
    @yield('content')
    <script>
        (function () {
            const moneyInputs = Array.from(document.querySelectorAll('input[data-currency-input]'));
            if (!moneyInputs.length) {
                return;
            }

            const parseDigits = (value) => String(value ?? '').replace(/[^\d]/g, '');
            const formatRupiah = (digits) => {
                if (!digits) {
                    return '';
                }

                return 'Rp ' + new Intl.NumberFormat('id-ID', {
                    maximumFractionDigits: 0,
                }).format(Number(digits));
            };

            moneyInputs.forEach((input) => {
                const syncDisplay = () => {
                    const digits = parseDigits(input.value);
                    input.value = formatRupiah(digits);
                };

                input.addEventListener('input', syncDisplay);
                input.addEventListener('blur', syncDisplay);

                syncDisplay();
            });

            const boundForms = new WeakSet();
            moneyInputs.forEach((input) => {
                const form = input.form;
                if (!form || boundForms.has(form)) {
                    return;
                }

                boundForms.add(form);
                form.addEventListener('submit', function () {
                    form.querySelectorAll('input[data-currency-input]').forEach((moneyInput) => {
                        moneyInput.value = parseDigits(moneyInput.value);
                    });
                });
            });
        })();
    </script>
</body>
</html>
