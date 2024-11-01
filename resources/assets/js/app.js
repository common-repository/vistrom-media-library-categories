window.wp = window.wp || {};

const { __ } = wp.i18n;

(($) => {
    function initializeSearchFieldsOnBulkEdit() {
        let searchField = `<input type="search" style="width: 100%;" class="js-vistrom-taxonomy-search-field" placeholder="${__('Filter %s', 'vistorm-media')}">`

        // We are on quick-edit view of post type listing
        const lists = $('.cat-checklist');

        lists.each(function (index, taxonomyField) {
            const taxonomy = $(taxonomyField);

            const search = searchField.replace('%s', taxonomy.find('.title').eq(index).text().toLowerCase());

            taxonomy.before(search);
        });

        $('.js-vistrom-taxonomy-search-field').on('input', function (event) {
            const query = event.target.value;

            let allItems = $(this).next('.cat-checklist').find('li');

            if (query.trim()) {
                allItems.hide();

                allItems.filter(function () {
                    return $(this).text().toLowerCase().indexOf(query.toLowerCase()) !== -1;
                })
                .show();
            } else {
                allItems.show();
            }
        });
    }

    if (document.querySelector('#posts-filter') && vistromMedia.screen === 'upload') {
        document.querySelector('#posts-filter').addEventListener('submit', (event) => {
            const action = $('[name=action]').val();
            const bulkEditSubmit = $('[name=vistrom_media_bulk_edit]').val();
            const triggeredAction = event.submitter;

            if (action === 'vistrom_media_edit' && triggeredAction.id === 'doaction') {
                event.preventDefault();

                let selectedPostIds = $('input[name="media[]"]:checked')
                    .map((index, item) => {
                        return item.value;
                    })
                    .get();

                // Load php view and append to form
                const data = {
                        action: 'admin_vistrom_media_render_bulk_edit',
                        post_ids: selectedPostIds,
                    };

                const columnCount = $('#posts-filter table').find('thead th:not(.hidden), thead td').length;

                $.post({
                    url: window.vistromMedia.ajaxUrl,
                    data,
                    success: function (response) {
                        const data = response.data;
                        const bulkEditView = data.html;

                        if ($('#vistrom-media-bulk-edit-row').length) {
                            $('#vistrom-media-bulk-edit-row').replaceWith(bulkEditView);
                        } else {
                            $('#posts-filter').find('#the-list').prepend(bulkEditView);
                        }

                        // Change colspan
                        $('#posts-filter').find('#vistrom-media-bulk-edit-row').find('td.colspanchange').prop('colspan', columnCount);

                        // Add event listeners
                        $('.js-vistrom-media-cancel-bulk-edit').click(() => {
                            $('#vistrom-media-bulk-edit-row').remove();
                        });

                        $('.js-vistrom-media-remove-from-bulk-edit').click((event) => {
                            const button = event.target;
                            const idToRemove = button.dataset.product_id;

                            const input = $('#posts-filter').find(`input[name="media[]"][value="${idToRemove}"]:checked`);
                            input.prop('checked', false);
                            button.parentElement.remove();

                            if ($('.js-vistrom-media-remove-from-bulk-edit').length === 0) {
                                $('#vistrom-media-bulk-edit-row').remove();
                            }
                        });

                        initializeSearchFieldsOnBulkEdit();
                    },
                    error: function (error) {
                        console.log(error);
                        $('#vistrom-media-bulk-edit-row').remove();
                    },
                    beforeSend: function () {
                        $('#posts-filter').find('#the-list').prepend(`<tr class="loading"><td class="colspanchange" colspan="${columnCount}">${__('Loading...', 'vistrom-media')}</td></tr>`);
                    },
                    complete: function () {
                        $('#posts-filter').find('#the-list').find($('tr.loading')).remove();
                    }
                });
            } else if (action === 'vistrom_media_edit' && bulkEditSubmit) {
                event.preventDefault();

                let formData = $('#posts-filter').serializeArray();

                formData.push({
                    name: 'action',
                    value: 'admin_vistrom_media_bulk_update',
                });
                formData.push({
                    name: '_ajax_nonce',
                    value: window.vistromMedia.nonces.bulkUpdate,
                });

                $.post({
                    url: window.vistromMedia.ajaxUrl,
                    data: formData,
                    success: (response) => {
                        const url = new URL(window.location.href);
                        url.searchParams.set('vistrom_media_updated', $('#bulk-titles').children().length);
                        window.location.href = url.href;
                    },
                    error: (response) => {
                        console.log(response);
                    },
                });
            }
        });
    }

    // Create a new taxonomy filter for vistrom_media_category
    const MediaLibraryTaxonomyFilter = wp.media.view.AttachmentFilters.extend({
        id: 'vistrom-media-category-filter',
        createFilters: function() {
            let filters = {};
            const that = this;

            _.each(this.options.taxonomy.terms, function (term, index) {
                filters[index] = {
                    text: `${term.name} (${term.count})`,
                    props: {},
                };

                filters[index].props[that.options.taxonomy.query_var] = term.slug;
            });

            filters.all = {
                text: __('All', 'vistrom-media') + ' ' + that.options.taxonomy.label,
                props: {},
                priority: 10,
            };

            filters.all.props[that.options.taxonomy.query_var] = '';

            this.filters = filters;
        },
    });

    const button = wp.media.view.Button;

    const BulkEditButton = wp.media.view.Button.extend({
        initialize() {
            button.prototype.initialize.apply(this, arguments);

            this.controller.on('select:activate', this.show, this);
            this.controller.on('select:deactivate', this.hide, this);

            this.controller.on('selection:toggle', this.toggleDisabled, this);
            this.controller.on('select:activate', this.toggleDisabled, this);
        },
        toggleDisabled() {
            this.model.set('disabled', !this.controller.state().get('selection').length);
        },
        show() {
            this.$el.removeClass('hidden');
        },
        hide() {
            this.$el.addClass('hidden');
        },
        render() {
            button.prototype.render.apply(this, arguments);

            if (this.controller.isModeActive('select')) {
                this.$el.addClass('edit-selected-button');
            } else {
                this.$el.addClass('edit-selected-button hidden');
            }

            this.toggleDisabled();

            return this;
        },
        click() {
            if (!this.controller.state().get('selection').length) {
                return;
            }

            // Ajax to open modal?
            const selectedImageIds = this.controller.state().get('selection').map(function (attachment) {
                    const data = attachment.toJSON();

                    return data.id;
                });

            // Load php view and append to form
            const data = {
                action: 'admin_vistrom_media_render_grid_bulk_edit',
                post_ids: selectedImageIds,
            };

            $.post({
                url: window.vistromMedia.ajaxUrl,
                data,
                success: function (response) {
                    const data = response.data;
                    const bulkEditView = data.html;

                    $('body').append(bulkEditView);

                    const body = document.querySelector("body");
                    body.style.overflow = 'hidden';

                    $('.vistrom-media-modal').on('click', '.js-vistrom-media-modal-close', function (e) {
                        e.preventDefault();
                        VistromMediaCloseModal();
                    });

                    $('.js-vistrom-media-remove-from-bulk-edit').click((event) => {
                        event.preventDefault();
                        event.stopPropagation();

                        const button = event.target;
                        button.parentElement.remove();

                        if ($('.js-vistrom-media-remove-from-bulk-edit').length === 0) {
                            VistromMediaCloseModal();
                        }
                    });

                    $('body').on('click', function (event) {
                        if (!$(event.target).closest('.vistrom-media-modal-content').length) {
                            VistromMediaCloseModal();
                        }
                    });

                    $('body').on('keydown', function (event) {
                        if (event.key === 'Escape') {
                            VistromMediaCloseModal();
                        }
                    });

                    initializeSearchFieldsOnBulkEdit();

                    $('.js-vistrom-bulk-edit-form').on('submit', function (e) {
                        e.preventDefault();
                        const form = $(this);

                        let formData = form.serializeArray();

                        formData.push({
                            name: 'action',
                            value: 'admin_vistrom_media_bulk_update',
                        });
                        formData.push({
                            name: '_ajax_nonce',
                            value: window.vistromMedia.nonces.bulkUpdate,
                        });

                        $.post({
                            url: window.vistromMedia.ajaxUrl,
                            data: formData,
                            success: (response) => {
                                window.location.reload();
                            },
                            error: (response) => {
                                console.log(response);
                            },
                        });
                    })
                },
                error: function (error) {
                    console.log(error);
                }
            });

        }
    });

    // Extend the media gridview to use the custom added filter.
    const attachmentsBrowser = wp.media.view.AttachmentsBrowser;
    wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
        createToolbar: function() {
            // Make sure to load the original toolbar
            attachmentsBrowser.prototype.createToolbar.call(this);

            window.vistromMedia.mediaTaxonomies.forEach((item) => {
                this.toolbar.set("MediaLibrary" + item.name + "Filter", new MediaLibraryTaxonomyFilter({
                    controller: this.controller,
                    model: this.collection.props,
                    priority: -75,
                    taxonomy: item
                }).render());
            });

            if (this.controller.isModeActive('grid')) {
                this.toolbar.set("MediaLibraryBulkEdit", new BulkEditButton({
                    style: 'primary',
                    text: __('Edit', 'vistrom-media'),
                    controller: this.controller,
                    priority: -80,
                }).render());
            }
        }
    });

    // Add searchable taxonomies on Media details view.
    const attachmentDetails = wp.media.view.AttachmentCompat;
    wp.media.view.AttachmentCompat = wp.media.view.AttachmentCompat.extend({
        render: function() {
            attachmentDetails.prototype.render.call(this);

            // Ugly workaround to ensure the fields have been rendered
            setTimeout(() => {
                initializeSearchFieldsOnBulkEdit();
            }, 0);

            return this;
        },
    });

    /**
     * Override the default save functionality to support array fields
     *
     * https://stackoverflow.com/questions/52810006/wordpress-attachment-taxonomy-checkbox-group-not-saving-in-grid-view
     */
    wp.media.view.AttachmentCompat.prototype.save = function (event) {
        let data = {};

        if (event) {
            event.preventDefault();
        }

        _.each(this.$el.serializeArray(), function(pair) {
            if ( /\[\]$/.test( pair.name ) ) {
                if ( undefined === data[ pair.name ] ) {
                    data[pair.name] = [];
                }
                data[pair.name].push(pair.value);
            } else {
                data[pair.name] = pair.value;
            }
        });

        this.controller.trigger('attachment:compat:waiting', ['waiting']);
        this.model.saveCompat(data).always(_.bind(this.postSave, this));
    };

    function VistromMediaCloseModal() {
        $('.vistrom-media-modal').remove();
        document.querySelector("body").style.overflow = 'initial';
    }

})(jQuery);
