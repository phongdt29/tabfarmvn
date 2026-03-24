(function ($) {
    'use strict';

    $(document).on('click', '.penci-regenerate-css', function (e) {
        e.preventDefault();

        var $button = $(this),
            type = $button.data('type'),
            id = $button.data('id');

        // Prevent double-click
        if ($button.hasClass('is-active')) return;

        $button.prop('disabled', true)
            .addClass('is-active')
            .text('Generating critical CSS...');

        $button.after('<div class="penci-ps-wrap"><p class="penci-ps-notice">Please do not navigate to another page while generating the critical CSS.</p><div class="penci-ps-log"></div></div>');

        let tasks = [];

        if (type === 'auto') {
            tasks = 'home,front,category,tag,author,singular,search'.split(',');
        } else if (type === 'manual') {
            let taskInput = $('#penci-critical-urls').val() || '';
            tasks = taskInput.split('\n').map(url => url.trim()).filter(Boolean);
            $('#penci-critical-urls').prop('disabled', true);
        } else {
            tasks = [id];
        }

        // Run tasks sequentially
        runTasksSequentially(type, tasks, $button);
    });

    function runTasksSequentially(type, tasks, $button) {
        let i = 0;
        let total = tasks.length;
        let $log = $('.penci-critical-tools-content');

        function next() {
            if (i >= total) {
                finish($button, true, $log);
                return;
            }

            let task = tasks[i];
            $log.append('<p>Generating task ' + (i + 1) + ' of ' + total + ': <strong>' + task + '</strong></p>');

            penci_create_critical_css(type, task, $button).then((response) => {
                if (response.success) {
                    $log.append('<p class="penci-ps-task-success">✔ Successfully generated: ' + task + '</p>');
                } else {
                    $log.append('<p class="penci-ps-task-error">✖ Error generating: ' + task + ' — ' + (response.data?.message || 'Unknown error') + '</p>');
                }
                i++;
                setTimeout(next, 500); // delay between requests
            }).catch(() => {
                $log.append('<p class="penci-ps-task-error">✖ AJAX error on task: ' + task + '</p>');
                finish($button, false, $log);
            });
        }

        next();
    }

    function penci_create_critical_css(type, id, $button) {
        return $.ajax({
            url: PENCIDASHBOARD.ajaxUrl,
            type: 'POST',
            data: {
                action: 'penci_generate_critical_css',
                nonce: PENCIDASHBOARD.nonce,
                type: type,
                id: id,
            }
        });
    }

    function finish($button, success, $log) {
        $button.prop('disabled', false).removeClass('is-active');
        $button.siblings('.penci-ps-wrap').find('.penci-ps-notice').remove();
        $('#penci-critical-urls').prop('disabled', false);

        if (success) {
            $button.addClass('penci-ps-critical-success').text('Generated successfully.');
            $log.append('<p class="penci-ps-final-success">🎉 All tasks completed successfully.</p>');
        } else {
            $button.addClass('penci-ps-critical-error').text('Error while generating.');
            $log.append('<p class="penci-ps-final-error">⚠ Stopped due to errors.</p>');
        }
    }
})(jQuery);