jQuery(document).ready(function ($) {
    "use strict";

    var penci_soledaddi_wz = {
        init: function () {
            this.progress = $('.demos-container-wrap');
            this.log = $('.penci-soledad-demo-import-log');
            this.importer = $('#penci-soledad-demo-importer');
            this.steps = [];
            this.nocontent = false;
            this.generateImage = false;
            this.needheaderfooter = true;
            this.demoid = 0;
            this.currentButton = null;
            this.currentDemoWrapper = null;
            this.currentDemoWrap = null;

            $('body').on('click', '.penci-winstall-demo', function (e) {
                e.preventDefault();
                
                var demowrapper = $(this).closest('.demo-selector'),
                    demowrap = $(this).closest('.soledad-wizard-content-inner'),
                    includeContent,
                    importProducts = true,
                    installPlugin = demowrapper.find('input[name="install-plugin"]').is(':checked'),
                    includeStyle = demowrapper.find('input[name="include-style"]').is(':checked'),
                    includePages = demowrapper.find('input[name="include-pages"]').is(':checked'),
                    includePosts = demowrapper.find('input[name="include-posts"]').is(':checked'),
                    includeProducts = demowrapper.find('input[name="include-products"]').is(':checked'),
                    generateImage = demowrapper.find('input[name="generate-image"]').is(':checked'),
                    isWooCommerce = demowrapper.find('input[name="woocommerce_demo"]').is(':checked');

                // Store references to UI elements for later use
                penci_soledaddi_wz.currentButton = $(this);
                penci_soledaddi_wz.currentDemoWrapper = demowrapper;
                penci_soledaddi_wz.currentDemoWrap = demowrap;

                // Reset steps array for each installation
                penci_soledaddi_wz.steps = [];
                penci_soledaddi_wz.nocontent = false;
                penci_soledaddi_wz.generateImage = false;
                penci_soledaddi_wz.needheaderfooter = true;
                penci_soledaddi_wz.demoid = demowrapper.find('input[name="demo"]').val();

                // Set loading state
                demowrap.addClass('importing');
                demowrapper.addClass('loading');
                $(this).val($(this).attr('data-installing-text')).prop('disabled', true);

                if ( isWooCommerce && ! includeProducts ) {
                    includeProducts = false;
                }

                if ( includePages && includePosts && importProducts ) {
                    penci_soledaddi_wz.steps.push('content');
                    penci_soledaddi_wz.needheaderfooter = false;
                } else {
                    if ( includePosts ) {
                        penci_soledaddi_wz.steps.push('posts');
                    }
                    if ( includePages ) {
                        penci_soledaddi_wz.steps.push('pages');
                    }
                    if ( includeProducts ) {
                        penci_soledaddi_wz.steps.push('products');
                    }
                }

                if (installPlugin) {
                    penci_soledaddi_wz.steps.push('plugin');
                }
                if ( generateImage ) {
                    penci_soledaddi_wz.generateImage = true;
                }
                if ( includePages || includePosts || includeProducts ) {
                    includeContent = true;
                }
                if (! includeContent ) {
                    penci_soledaddi_wz.nocontent = true;
                }
                if (includeStyle) {
                    penci_soledaddi_wz.steps.push('customizer');
                }
                if ( includeContent ) {
                    penci_soledaddi_wz.steps.push('widgets', 'sliders');
                }
                if ( penci_soledaddi_wz.needheaderfooter ) {
                    penci_soledaddi_wz.steps.push('header_footer_only');
                }

                var $first_item = penci_soledaddi_wz.steps.shift();
                if ('plugin' === $first_item) {
                    penci_soledaddi_wz.install_plugin();
                } else if ('customizer' === $first_item) {
                    penci_soledaddi_wz.install_only_customize($first_item);
                } else {
                    penci_soledaddi_wz.download($first_item);
                }
                // Don't remove loading classes here - let the process complete first
            });
        },

        // Helper function to remove loading state
        removeLoadingState: function() {
            if (penci_soledaddi_wz.currentDemoWrap) {
                penci_soledaddi_wz.currentDemoWrap.removeClass('importing');
            }
            if (penci_soledaddi_wz.currentDemoWrapper) {
                penci_soledaddi_wz.currentDemoWrapper.removeClass('loading');
            }
            if (penci_soledaddi_wz.currentButton) {
                var uninstallText = penci_soledaddi_wz.currentButton.attr('data-uninstall-text') || 'Install Demo';
                penci_soledaddi_wz.currentButton.val(uninstallText).prop('disabled', false);
                penci_soledaddi_wz.currentButton.removeClass('penci-winstall-demo').addClass('penci-wuninstall-demo');
            }
            window.location = soledadInstallations.done_url || window.location.href;
        },

        install_plugin: function () {
            var $plugins = PenciObject.plugins_required;

            if (!$plugins.length) {
                penci_soledaddi_wz.progress.find('.spinner').hide();
                // Continue to next step if no plugins to install
                var nextStep = penci_soledaddi_wz.steps.shift();
                if (nextStep) {
                    if ('customizer' === nextStep) {
                        penci_soledaddi_wz.install_only_customize(nextStep);
                    } else {
                        penci_soledaddi_wz.download(nextStep);
                    }
                } else {
                    penci_soledaddi_wz.configTheme();
                }
                return;
            }
            var plugin = $plugins.shift();

            penci_soledaddi_wz.showlog('Installing ' + plugin + ' plugin…');

            $.get(
                ajaxurl, {
                action: 'penci_soledad_install_plugin',
                plugin: plugin,
                _wpnonce: soledadInstallations.demononce
            },
                function (response) {
                    penci_soledaddi_wz.showlog(response.data);

                    if ($plugins.length) {
                        setTimeout(function () {
                            penci_soledaddi_wz.install_plugin();
                        }, 1000);
                    } else {
                        var nextStep = penci_soledaddi_wz.steps.shift();
                        if (nextStep) {
                            if ('customizer' === nextStep) {
                                penci_soledaddi_wz.install_only_customize(nextStep);
                            } else {
                                penci_soledaddi_wz.download(nextStep);
                            }
                        } else {
                            penci_soledaddi_wz.configTheme();
                        }
                    }
                }
            ).fail(function () {
                penci_soledaddi_wz.showlog('Plugin installation failed');
                penci_soledaddi_wz.removeLoadingState();
            });
        },

        download: function (type) {
            penci_soledaddi_wz.showlog('Downloading ' + type + ' file');

            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_download_file',
                    type: type,
                    demo: penci_soledaddi_wz.demoid,
                    _wpnonce: soledadInstallations.demononce
                },
                function (response) {
                    if (response.success) {
                        penci_soledaddi_wz.import(type);
                    } else {
                        penci_soledaddi_wz.showlog(response.data);

                        if (penci_soledaddi_wz.steps.length) {
                            penci_soledaddi_wz.download(penci_soledaddi_wz.steps.shift());
                        } else {
                            penci_soledaddi_wz.configTheme();
                        }
                    }
                }
            ).fail(function () {
                penci_soledaddi_wz.showlog('Download failed');
                penci_soledaddi_wz.removeLoadingState();
            });
        },
        download_only_pages: function (type) {

            var name_file = type;
            if ('pages' === type) {
                name_file = 'pages';
            }
            penci_soledaddi_wz.showlog('Downloading ' + name_file + ' file');

            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_download_file',
                    type: type,
                    demo: penci_soledaddi_wz.demoid,
                    _wpnonce: soledadInstallations.demononce
                },
                function (response) {
                    if (response.success) {
                        penci_soledaddi_wz.import_only_page(type);
                    } else {
                        penci_soledaddi_wz.showlog(response.data);

                        if (penci_soledaddi_wz.steps.length && !penci_soledaddi_wz.nocontent) {
                            penci_soledaddi_wz.download_only_pages(penci_soledaddi_wz.steps.shift());
                        } else {
                            penci_soledaddi_wz.removeLoadingState();
                        }
                    }
                }
            ).fail(function () {
                penci_soledaddi_wz.showlog('Download failed');
                penci_soledaddi_wz.removeLoadingState();
            });
        },
        install_only_customize: function (type) {
            penci_soledaddi_wz.showlog('Downloading ' + type + ' file');
            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_download_file',
                    type: type,
                    demo: penci_soledaddi_wz.demoid,
                    _wpnonce: soledadInstallations.demononce
                },
                function (response) {
                    if (response.success) {
                        penci_soledaddi_wz.import_customizer(type);
                    } else {
                        penci_soledaddi_wz.showlog(response.data);
                        penci_soledaddi_wz.removeLoadingState();
                    }
                }
            ).fail(function () {
                penci_soledaddi_wz.showlog('Download failed');
                penci_soledaddi_wz.removeLoadingState();
            });
        },
        import_customizer: function (type) {
            penci_soledaddi_wz.showlog('Importing ' + type);

            var data = {
                action: 'penci_soledad_import',
                type: type,
                demo: penci_soledaddi_wz.demoid,
                _wpnonce: soledadInstallations.demononce
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
                        penci_soledaddi_wz.showlog(type + ' has been imported successfully!');

                        setTimeout(function () {
                            if (penci_soledaddi_wz.steps.length && !penci_soledaddi_wz.nocontent) {
                                penci_soledaddi_wz.download_only_pages(penci_soledaddi_wz.steps.shift());
                            } else {
                                penci_soledaddi_wz.showlog('Import completed!');
                                penci_soledaddi_wz.progress.find('.spinner').hide();
                                penci_soledaddi_wz.removeLoadingState();
                            }
                        }, 200);
                        break;
                }
            });

            evtSource.addEventListener('log', function (message) {
                var data = JSON.parse(message.data);
                penci_soledaddi_wz.showlog(data.message);
            });

            evtSource.addEventListener('error', function () {
                evtSource.close();
                penci_soledaddi_wz.showlog('Import failed');
                penci_soledaddi_wz.removeLoadingState();
            });
        },
        import_only_page: function (type) {

            var name_file = type;
            if ('content_only_pages' === type) {
                name_file = 'pages';
            }

            penci_soledaddi_wz.showlog('Importing ' + name_file);

            var data = {
                action: 'penci_soledad_import',
                type: type,
                demo: penci_soledaddi_wz.demoid,
                _wpnonce: soledadInstallations.demononce
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
                        penci_soledaddi_wz.showlog(name_file + ' has been imported successfully!');

                        if (penci_soledaddi_wz.steps.length && !penci_soledaddi_wz.nocontent) {
                            penci_soledaddi_wz.download_only_pages(penci_soledaddi_wz.steps.shift());
                        } else {
                            penci_soledaddi_wz.removeLoadingState();
                        }

                        break;
                }
            });

            evtSource.addEventListener('log', function (message) {
                var data = JSON.parse(message.data);
                penci_soledaddi_wz.showlog(data.message);
            });

            evtSource.addEventListener('error', function () {
                evtSource.close();
                penci_soledaddi_wz.showlog('Import failed');
                penci_soledaddi_wz.removeLoadingState();
            });
        },
        import: function (type) {
            penci_soledaddi_wz.showlog('Importing ' + type);

            var data = {
                action: 'penci_soledad_import',
                type: type,
                demo: penci_soledaddi_wz.demoid,
                _wpnonce: soledadInstallations.demononce
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
                        penci_soledaddi_wz.showlog(type + ' has been imported successfully!');

                        if (penci_soledaddi_wz.steps.length) {
                            penci_soledaddi_wz.download(penci_soledaddi_wz.steps.shift());
                        } else {
                            penci_soledaddi_wz.configTheme();
                        }

                        break;
                }
            });

            evtSource.addEventListener('log', function (message) {
                var data = JSON.parse(message.data);
                penci_soledaddi_wz.showlog(data.message);
            });

            evtSource.addEventListener('error', function () {
                evtSource.close();
                penci_soledaddi_wz.showlog('Import failed');
                penci_soledaddi_wz.removeLoadingState();
            });
        },

        configTheme: function () {
            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_config_theme',
                    demo: penci_soledaddi_wz.demoid,
                    _wpnonce: soledadInstallations.demononce
                },
                function (response) {
                    if (response.success ) {
                        if ( penci_soledaddi_wz.generateImage ) {
                            penci_soledaddi_wz.generateImages();
                        } else {
                            penci_soledaddi_wz.showlog('Import completed!');
                            penci_soledaddi_wz.progress.find('.spinner').hide();
                            penci_soledaddi_wz.removeLoadingState();
                        }
                    } else {
                        penci_soledaddi_wz.showlog('Theme configuration failed');
                        penci_soledaddi_wz.removeLoadingState();
                    }
                }
            ).fail(function () {
                penci_soledaddi_wz.showlog('Theme configuration failed');
                penci_soledaddi_wz.removeLoadingState();
            });
        },

        generateImages: function () {
            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_get_images',
                    _wpnonce: soledadInstallations.demononce
                },
                function (response) {
                    if (!response.success) {
                        penci_soledaddi_wz.showlog(response.data);
                        penci_soledaddi_wz.showlog('Import completed!');
                        penci_soledaddi_wz.progress.find('.spinner').hide();
                        penci_soledaddi_wz.removeLoadingState();
                        return;
                    } else {
                        var ids = response.data;

                        if (!ids.length) {
                            penci_soledaddi_wz.showlog('Import completed!');
                            penci_soledaddi_wz.progress.find('.spinner').hide();
                            penci_soledaddi_wz.removeLoadingState();
                            return;
                        }

                        penci_soledaddi_wz.showlog('Starting generate ' + ids.length + ' images');

                        penci_soledaddi_wz.generateSingleImage(ids);
                    }
                }
            ).fail(function () {
                penci_soledaddi_wz.showlog('Image generation failed');
                penci_soledaddi_wz.removeLoadingState();
            });
        },

        generateSingleImage: function (ids) {
            if (!ids.length) {
                penci_soledaddi_wz.showlog('Import completed!');
                penci_soledaddi_wz.progress.find('.spinner').hide();
                penci_soledaddi_wz.removeLoadingState();
                return;
            }

            var id = ids.shift();

            $.get(
                ajaxurl,
                {
                    action: 'penci_soledad_generate_image',
                    id: id,
                    _wpnonce: soledadInstallations.demononce
                },
                function (response) {
                    penci_soledaddi_wz.showlog(response.data + ' (' + ids.length + ' images left)');

                    penci_soledaddi_wz.generateSingleImage(ids);
                }
            ).fail(function () {
                penci_soledaddi_wz.showlog('Image generation failed');
                penci_soledaddi_wz.removeLoadingState();
            });
        },
        showlog: function (message) {
            penci_soledaddi_wz.progress.find('.text').text(message);
            penci_soledaddi_wz.log.prepend('<p>' + message + '</p>');
        }

    };


    penci_soledaddi_wz.init();
});