jQuery(document).ready(function ($) {
    "use strict";

    var penci_soledaddi = {

        handle_popups: function () {
            // Handle popup
            $('.penci-install-demo-confirm').on('click', function (e) {
                e.preventDefault();
                var popup_parent = $(this).closest('.demo-selector');
                popup_parent.addClass('show-popup');
                $('body').addClass('show-popup-container');
            });
            $(document).on('keyup', function (e) {
                if (e.key === "Escape") {
                    $('.demo-selector').removeClass('show-popup');
                    $('body').removeClass('show-popup-container');
                }
            });
            $('.penci-close-popup, .penci-popup-overlay').on('click', function (e) {
                e.preventDefault();
                $('.demo-selector').removeClass('show-popup');
                $('body').removeClass('show-popup-container');
            });
            $('body').on('click', '.cancel-import', function (e) {
                e.preventDefault();
                $('.demo-selector').removeClass('show-popup');
                $('body').removeClass('show-popup-container');
            });
        },
        init: function () {
            this.confirm();

            this.$progress = $('#penci-soledad-demo-import-progress');
            this.$log = $('#penci-soledad-demo-import-log');
            this.$importer = $('#penci-soledad-demo-importer');
            this.$uninstall = $('#penci-soledad-demo-uninstall');
            this.steps = [];
            this.nocontent = false;
            this.generateImage = false;
            this.needheaderfooter = true;
            this.plugins = '';

            // Import demo data
            if (this.$importer.length) {
                var includeContent,
                    importProducts = true,
                    installPlugin = penci_soledaddi.$importer.find('input[name="install-plugin"]').val(),
                    includeStyle = penci_soledaddi.$importer.find('input[name="include-style"]').val(),
                    includePages = penci_soledaddi.$importer.find('input[name="include-pages"]').val(),
                    includePosts = penci_soledaddi.$importer.find('input[name="include-posts"]').val(),
                    includeProducts = penci_soledaddi.$importer.find('input[name="include-products"]').val(),
                    generateImage = penci_soledaddi.$importer.find('input[name="generate-image"]').val(),
                    isWooCommerce = penci_soledaddi.$importer.find('input[name="woocommerce_demo"]').val();

                if ( isWooCommerce && ! includeProducts ) {
                    includeProducts = false;
                }

                if (installPlugin) {
                    this.steps.push('plugin');
                }

                if ( includePages && includePosts && importProducts ) {
                    this.steps.push('content');
                    this.needheaderfooter = false;
                } else {
                    if ( includePosts ) {
                        this.steps.push('posts');
                    }
                    if ( includePages ) {
                        this.steps.push('pages');
                    }
                    if ( includeProducts ) {
                        this.steps.push('products');
                    }
                }

                if ( generateImage ) {
                    this.generateImage = true;
                }
                if ( includePages || includePosts || includeProducts ) {
                    includeContent = true;
                }
                if (! includeContent ) {
                    this.nocontent = true;
                }
                if (includeStyle) {
                    this.steps.push('customizer');
                }
                if ( includeContent ) {
                    this.steps.push('widgets', 'sliders');
                }
                if ( this.needheaderfooter ) {
                    this.steps.push('header_footer_only');
                }

                var $first_item = penci_soledaddi.steps.shift();
                if ('plugin' === $first_item) {
                    this.install_plugin();
                } else if ('customizer' === $first_item) {
                    this.install_only_customize($first_item);
                } else {
                    this.download($first_item);
                }
            } else if (this.$uninstall.length) {
                this.unintall_demo();
            }

        },

        confirm: function () {
            if ($('.penci-uninstall-demo').length) {
                $('.penci-uninstall-demo').on('click', function (e) {
                    var r = confirm("Are you sure?");
                    if (r !== true) {
                        return false;
                    }
                });
            }
            if ($('.penci-install-demo').length) {
                $('.penci-install-demo').on('click', function (e) {

                    var $form = $(this).closest('.demo-selector'),
                        $list = $('.required_plugins_list');

                    $list.find('.list-item').removeClass('active');

                    if ($('.demos-container').hasClass('has-imported')) {
                        alert("You've imported a demo before, let's Uninstall that demo first before import a new demo - because if you import multiple demos together, it will be mixed.");
                        return false;
                    }

                    if ($form.hasClass('req-elementor')) {
                        $list.find('.elementor').addClass('active');
                    }

                    if ($form.hasClass('req-woocommerce')) {
                        $list.find('.woocommerce').addClass('active');
                    }

                    if ($form.hasClass('req-penci-finance')) {
                        $list.find('.penci-finance').addClass('active');
                    }

                    if ($form.hasClass('req-penci-sport')) {
                        $list.find('.penci-sport').addClass('active');
                    }

                    if ($form.hasClass('req-elementor') || $form.hasClass('req-woocommerce') || $form.hasClass('req-penci-finance') || $form.hasClass('req-penci-sport')) {
                        $('#penci_required_plugins_btn').trigger('click');
                        return false;
                    }

                    var r = confirm("Are you sure you want to import this demo?");
                    if (r !== true) {
                        return false;
                    }
                });
            }
        },

        install_plugin: function () {
            var $plugins = PenciObject.plugins_required,
                $demo_plugins = penci_soledaddi.$importer.find('input[name="install-plugin"]').val();

            if ( $demo_plugins && $demo_plugins.length ) {
                $plugins = $demo_plugins.split(',');
            }

            if (!$plugins.length) {
                penci_soledaddi.$progress.find('.spinner').hide();
                return;
            }
            var plugin = $plugins.shift();

            penci_soledaddi.log('Installing ' + plugin + ' the plugin…');

            $.get(
                ajaxurl, {
                action: 'penci_soledad_install_plugin',
                plugin: plugin,
                _wpnonce: penci_soledaddi.$importer.find('input[name="_wpnonce"]').val()
            },
                function (response) {
                    penci_soledaddi.log(response.data);

                    if ($plugins.length) {
                        setTimeout(function () {
                            penci_soledaddi.install_plugin($plugins);
                        }, 1000);
                    } else {
                        penci_soledaddi.download(penci_soledaddi.steps.shift());
                    }
                }
            ).fail(function () {
                penci_soledaddi.log('Failed');
            });
        },

        download: function (type) {
            penci_soledaddi.log('Downloading ' + type + ' file');

            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_download_file',
                    type: type,
                    demo: penci_soledaddi.$importer.find('input[name="demo"]').val(),
                    _wpnonce: penci_soledaddi.$importer.find('input[name="_wpnonce"]').val()
                },
                function (response) {
                    if (response.success) {
                        penci_soledaddi.import(type);
                    } else {
                        penci_soledaddi.log(response.data);

                        if (penci_soledaddi.steps.length) {
                            penci_soledaddi.download(penci_soledaddi.steps.shift());
                        } else {
                            penci_soledaddi.configTheme();
                        }
                    }
                }
            ).fail(function () {
                penci_soledaddi.log('Failed');
            });
        },
        download_only_pages: function (type) {

            var name_file = type;
            if ('pages' === type) {
                name_file = 'pages';
            }
            penci_soledaddi.log('Downloading ' + name_file + ' file');

            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_download_file',
                    type: type,
                    demo: penci_soledaddi.$importer.find('input[name="demo"]').val(),
                    _wpnonce: penci_soledaddi.$importer.find('input[name="_wpnonce"]').val()
                },
                function (response) {
                    if (response.success) {
                        penci_soledaddi.import_only_page(type);
                    } else {
                        penci_soledaddi.log(response.data);

                        if (penci_soledaddi.steps.length && !penci_soledaddi.nocontent) {
                            penci_soledaddi.download_only_pages(penci_soledaddi.steps.shift());
                        }
                    }
                }
            ).fail(function () {
                penci_soledaddi.log('Failed');
            });
        },
        install_only_customize: function (type) {
            penci_soledaddi.log('Downloading ' + type + ' file');
            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_download_file',
                    type: type,
                    demo: penci_soledaddi.$importer.find('input[name="demo"]').val(),
                    _wpnonce: penci_soledaddi.$importer.find('input[name="_wpnonce"]').val()
                },
                function (response) {
                    if (response.success) {
                        penci_soledaddi.import_customizer(type);

                        if (penci_soledaddi.steps.length && !penci_soledaddi.nocontent) {
                            penci_soledaddi.download_only_pages(penci_soledaddi.steps.shift());
                        }
                    } else {
                        penci_soledaddi.log(response.data);
                    }
                }
            ).fail(function () {
                penci_soledaddi.log('Failed');
            });
        },
        import_customizer: function (type) {
            penci_soledaddi.log('Importing ' + type);

            var data = {
                action: 'penci_soledad_import',
                type: type,
                demo: penci_soledaddi.$importer.find('input[name="demo"]').val(),
                _wpnonce: penci_soledaddi.$importer.find('input[name="_wpnonce"]').val()
            };
            var url = ajaxurl + '?' + $.param(data);
            var evtSource = new EventSource(url);

            evtSource.addEventListener('message', function (message) {
                var data = JSON.parse(message.data);
                switch (data.action) {
                    case 'updateTotal':
                        console.log(data.delta);
                        break;

                    case 'updateDelta':
                        console.log(data.delta);
                        break;

                    case 'complete':
                        evtSource.close();
                        penci_soledaddi.log(type + ' has been imported successfully!');

                        setTimeout(function () {
                            penci_soledaddi.log('Import completed!');
                            penci_soledaddi.$progress.find('.spinner').hide();
                        }, 200);
                        break;
                }
            });

            evtSource.addEventListener('log', function (message) {
                var data = JSON.parse(message.data);
                penci_soledaddi.log(data.message);
            });
        },
        import_only_page: function (type) {

            var name_file = type;
            if ('content_only_pages' === type) {
                name_file = 'pages';
            }

            penci_soledaddi.log('Importing ' + name_file);

            var data = {
                action: 'penci_soledad_import',
                type: type,
                demo: penci_soledaddi.$importer.find('input[name="demo"]').val(),
                _wpnonce: penci_soledaddi.$importer.find('input[name="_wpnonce"]').val()
            };
            var url = ajaxurl + '?' + $.param(data);
            var evtSource = new EventSource(url);

            evtSource.addEventListener('message', function (message) {
                var data = JSON.parse(message.data);
                switch (data.action) {
                    case 'updateTotal':
                        console.log(data.delta);
                        break;

                    case 'updateDelta':
                        console.log(data.delta);
                        break;

                    case 'complete':
                        evtSource.close();
                        penci_soledaddi.log(name_file + ' has been imported successfully!');

                        if (penci_soledaddi.steps.length && !penci_soledaddi.nocontent) {
                            penci_soledaddi.download_only_pages(penci_soledaddi.steps.shift());
                        }

                        break;
                }
            });

            evtSource.addEventListener('log', function (message) {
                var data = JSON.parse(message.data);
                penci_soledaddi.log(data.message);
            });
        },
        import: function (type) {
            penci_soledaddi.log('Importing ' + type);

            var data = {
                action: 'penci_soledad_import',
                type: type,
                images: penci_soledaddi.$importer.find('input[name="include-image"]').val(),
                demo: penci_soledaddi.$importer.find('input[name="demo"]').val(),
                _wpnonce: penci_soledaddi.$importer.find('input[name="_wpnonce"]').val()
            };
            var url = ajaxurl + '?' + $.param(data);
            var evtSource = new EventSource(url);

            evtSource.addEventListener('message', function (message) {
                var data = JSON.parse(message.data);
                switch (data.action) {
                    case 'updateTotal':
                        console.log(data.delta);
                        break;

                    case 'updateDelta':
                        console.log(data.delta);
                        break;

                    case 'complete':
                        evtSource.close();
                        penci_soledaddi.log(type + ' has been imported successfully!');

                        if (penci_soledaddi.steps.length) {
                            penci_soledaddi.download(penci_soledaddi.steps.shift());
                        } else {
                            penci_soledaddi.configTheme();
                        }

                        break;
                }
            });

            evtSource.addEventListener('log', function (message) {
                var data = JSON.parse(message.data);
                penci_soledaddi.log(data.message);
            });
        },

        configTheme: function () {
            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_config_theme',
                    demo: penci_soledaddi.$importer.find('input[name="demo"]').val(),
                    _wpnonce: penci_soledaddi.$importer.find('input[name="_wpnonce"]').val()
                },
                function (response) {
                    if (response.success ) {
                        if ( penci_soledaddi.generateImage ) {
                            penci_soledaddi.generateImages();
                        } else {
                            penci_soledaddi.log('Import completed!');
                            penci_soledaddi.$progress.find('.spinner').hide();
                        }
                    }
                }
            ).fail(function () {
                penci_soledaddi.log('Failed');
            });
        },

        generateImages: function () {
            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_get_images',
                    _wpnonce: penci_soledaddi.$importer.find('input[name="_wpnonce"]').val()
                },
                function (response) {
                    if (!response.success) {
                        penci_soledaddi.log(response.data);
                        penci_soledaddi.log('Import completed!');
                        penci_soledaddi.$progress.find('.spinner').hide();
                        return;
                    } else {
                        var ids = response.data;

                        if (!ids.length) {
                            penci_soledaddi.log('Import completed!');
                            penci_soledaddi.$progress.find('.spinner').hide();
                        }

                        penci_soledaddi.log('Starting generate ' + ids.length + ' images');

                        penci_soledaddi.generateSingleImage(ids);
                    }
                }
            );
        },

        generateSingleImage: function (ids) {
            if (!ids.length) {
                penci_soledaddi.log('Import completed!');
                penci_soledaddi.$progress.find('.spinner').hide();
                return;
            }

            var id = ids.shift();

            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_generate_image',
                    id: id,
                    _wpnonce: penci_soledaddi.$importer.find('input[name="_wpnonce"]').val()
                },
                function (response) {
                    penci_soledaddi.log(response.data + ' (' + ids.length + ' images left)');

                    penci_soledaddi.generateSingleImage(ids);
                }
            );
        },

        unintall_demo: function () {
            penci_soledaddi.log('Uninstalling....');

            $.get(
                ajaxurl, {
                action: 'penci_soledad_unintall_demo',
                type: 'unintall_demo',
                _wpnonce: penci_soledaddi.$uninstall.find('input[name="_wpnonce"]').val()
            },
                function (response) {
                    if (response.success) {
                        penci_soledaddi.log('Unintall Demo completed!');
                        penci_soledaddi.$progress.find('.spinner').hide();
                    } else {
                        penci_soledaddi.log(response.data);
                    }
                }
            ).fail(function () {
                penci_soledaddi.log('Failed');
            });
        },
        log: function (message) {
            penci_soledaddi.$progress.find('.text').text(message);
            penci_soledaddi.$log.prepend('<p>' + message + '</p>');
        }

    };


    penci_soledaddi.init();
    penci_soledaddi.handle_popups();
});
