(function ($) {
    $(function () {
        if (typeof window.wecoza_clients === 'undefined') {
            return;
        }

        var config = window.wecoza_clients;
        var form = $('#clients-form');
        if (!form.length || typeof FormData === 'undefined') {
            return;
        }

        var container = form.closest('.wecoza-clients-form-container');
        var submitButton = form.find('button[type="submit"]');
        var feedback = container.find('.wecoza-clients-feedback');

        if (!feedback.length) {
            feedback = $('<div class="wecoza-clients-feedback mt-3"></div>');
            container.prepend(feedback);
        }

        var renderMessage = function (type, message) {
            var classes = 'alert alert-dismissible fade show';
            if (type === 'success') {
                classes += ' alert-subtle-success';
            } else {
                classes += ' alert-subtle-danger';
            }

            feedback.html(
                '<div class="' + classes + '" role="alert">' +
                    '<div>' + message + '</div>' +
                    '<button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>'
            );
        };

        var setSubmittingState = function (isSubmitting) {
            if (!submitButton.length) {
                return;
            }

            if (isSubmitting) {
                submitButton.data('original-text', submitButton.text());
                submitButton.prop('disabled', true).text(config.messages.form.saving);
            } else {
                var original = submitButton.data('original-text');
                if (original) {
                    submitButton.text(original);
                }
                submitButton.prop('disabled', false);
            }
        };

        var extractErrors = function (errors) {
            if (!errors) {
                return config.messages.form.error;
            }

            if (errors.general) {
                return errors.general;
            }

            var list = [];
            $.each(errors, function (field, message) {
                if (message) {
                    list.push(message);
                }
            });

            return list.length ? list.join('<br>') : config.messages.form.error;
        };

        form.on('submit', function (event) {
            if (!form[0].checkValidity()) {
                form.addClass('was-validated');
                return;
            }

            event.preventDefault();
            form.addClass('was-validated');

            var formData = new FormData(form[0]);
            formData.append('action', config.actions.save);
            formData.append('nonce', config.nonce);

            setSubmittingState(true);

            $.ajax({
                url: config.ajaxUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function (response) {
                if (response && response.success) {
                    var message = response.message || config.messages.form.saved;
                    renderMessage('success', message);

                    if (response.client && response.client.id) {
                        var idInput = form.find('input[name="id"]');
                        if (!idInput.length) {
                            idInput = $('<input>', { type: 'hidden', name: 'id' }).appendTo(form);
                        }
                        idInput.val(response.client.id);
                    }

                    form.trigger('wecoza:client-saved', [response]);
                } else if (response && response.errors) {
                    renderMessage('error', extractErrors(response.errors));
                } else {
                    renderMessage('error', config.messages.form.error);
                }
            }).fail(function () {
                renderMessage('error', config.messages.form.error);
            }).always(function () {
                setSubmittingState(false);
            });
        });
    });
})(jQuery);
