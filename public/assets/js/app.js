/* ================================================================
   Hackathon Learning App – Frontend JS (jQuery)
   ================================================================ */

var App = (function ($) {
    'use strict';

    var API = '../api';

    // ── Helpers ──────────────────────────────────────────────────

    function post(url, data) {
        return $.ajax({
            url: API + url,
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json'
        });
    }

    function get(url) {
        return $.ajax({ url: API + url, method: 'GET', dataType: 'json' });
    }

    function showMsg(text, isError) {
        var cls = isError ? 'error' : 'success';
        $('#form-msg').attr('class', 'msg ' + cls).text(text);
    }

    function btnLoading(btn, loading) {
        if (loading) {
            btn.data('orig', btn.html()).prop('disabled', true).html('<span class="spinner"></span> Please wait…');
        } else {
            btn.prop('disabled', false).html(btn.data('orig'));
        }
    }

    // ── Auth ─────────────────────────────────────────────────────

    $(document).on('submit', '#register-form', function (e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btnLoading(btn, true);

        post('/auth/register.php', {
            name: $(this).find('[name="name"]').val(),
            email: $(this).find('[name="email"]').val(),
            password: $(this).find('[name="password"]').val()
        }).done(function (r) {
            showMsg(r.message, false);
            setTimeout(function () { location.href = 'dashboard.php'; }, 400);
        }).fail(function (xhr) {
            showMsg(xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
            btnLoading(btn, false);
        });
    });

    $(document).on('submit', '#login-form', function (e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btnLoading(btn, true);

        post('/auth/login.php', {
            email: $(this).find('[name="email"]').val(),
            password: $(this).find('[name="password"]').val()
        }).done(function (r) {
            showMsg(r.message, false);
            setTimeout(function () { location.href = 'dashboard.php'; }, 400);
        }).fail(function (xhr) {
            showMsg(xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
            btnLoading(btn, false);
        });
    });

    // ── Module (tutor / notes / quiz) ────────────────────────────

    $(document).on('submit', '#module-form', function (e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var module = form.data('module');
        var topic = form.find('[name="topic"]').val();

        btnLoading(btn, true);
        $('#module-result').hide();

        post('/module/run.php', { module: module, topic: topic })
            .done(function (r) {
                $('#module-result').html(r.content).show();
                showMsg('', false);
            })
            .fail(function (xhr) {
                showMsg(xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
            })
            .always(function () { btnLoading(btn, false); });
    });

    // ── Test: Create ─────────────────────────────────────────────

    $(document).on('submit', '#test-create-form', function (e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btnLoading(btn, true);

        var topic = $(this).find('[name="topic"]').val();
        var count = parseInt($(this).find('[name="count"]').val(), 10);

        post('/test/create.php', { topic: topic, count: count })
            .done(function (r) {
                sessionStorage.setItem('test_id', r.test_id);
                sessionStorage.setItem('test_topic', r.topic);
                renderTestPaper(r.questions, r.test_id);
            })
            .fail(function (xhr) {
                showMsg(xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
                btnLoading(btn, false);
            });
    });

    function renderTestPaper(questions, testId) {
        var html = '<form id="test-submit-form" class="card"><h2>Answer All Questions</h2>';
        $.each(questions, function (i, q) {
            html += '<div class="question-block"><h4>Q' + (i + 1) + '. ' + q.question + '</h4>';
            $.each(q.options, function (j, opt) {
                var letter = String.fromCharCode(65 + j); // A, B, C, D
                html += '<label class="option-label"><input type="radio" name="q' + i + '" value="' + letter + '" required> ' + opt + '</label>';
            });
            html += '</div>';
        });
        html += '<input type="hidden" name="test_id" value="' + testId + '">';
        html += '<button type="submit" class="btn btn-primary">Submit Test</button>';
        html += '<p id="form-msg" class="msg"></p></form>';

        $('#test-setup').hide();
        $('#test-paper').html(html).show();
    }

    // ── Test: Submit ─────────────────────────────────────────────

    $(document).on('submit', '#test-submit-form', function (e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btnLoading(btn, true);

        var testId = parseInt($(this).find('[name="test_id"]').val(), 10);
        var answers = {};
        $(this).find('.question-block').each(function (i) {
            var val = $(this).find('input[name="q' + i + '"]:checked').val() || '';
            answers[i] = val;
        });

        post('/test/submit.php', { test_id: testId, answers: answers })
            .done(function (r) {
                // Store result in session and redirect
                sessionStorage.setItem('last_result', JSON.stringify(r));
                location.href = 'result.php';
            })
            .fail(function (xhr) {
                showMsg(xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
                btnLoading(btn, false);
            });
    });

    // ── Result Page ──────────────────────────────────────────────

    function loadResult() {
        var raw = sessionStorage.getItem('last_result');
        if (!raw) { $('#score-display').text('No result found.'); return; }

        var r = JSON.parse(raw);
        $('#score-display').text(r.score + ' / ' + r.total + '  (' + r.percentage + '%)');

        // Show details
        var html = '';
        $.each(r.details, function (i, d) {
            var cls = d.is_correct ? 'correct' : 'wrong';
            html += '<div class="detail-item ' + cls + '">';
            html += '<strong>Q' + (i + 1) + '.</strong> ' + d.question + '<br>';
            html += 'Your answer: <strong>' + d.given + '</strong> | Correct: <strong>' + d.correct + '</strong>';
            html += '</div>';
        });
        $('#details-list').html(html);
        $('#result-details').show();

        // Store attempt_id for analysis/plan
        sessionStorage.setItem('attempt_id', r.attempt_id);
    }

    $(document).on('click', '#btn-analysis', function () {
        var btn = $(this);
        btnLoading(btn, true);
        var attemptId = parseInt(sessionStorage.getItem('attempt_id'), 10);

        post('/analysis/get.php', { attempt_id: attemptId })
            .done(function (r) {
                var a = r.analysis;
                var html = '';
                if (a.weaknesses && a.weaknesses.length) {
                    html += '<h4>Weaknesses</h4><ul>';
                    $.each(a.weaknesses, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                if (a.insights && a.insights.length) {
                    html += '<h4>Insights</h4><ul>';
                    $.each(a.insights, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                if (a.recommendations && a.recommendations.length) {
                    html += '<h4>Recommendations</h4><ul>';
                    $.each(a.recommendations, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                $('#analysis-content').html(html);
                $('#analysis-box').show();
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error getting analysis');
            })
            .always(function () { btnLoading(btn, false); });
    });

    $(document).on('click', '#btn-plan', function () {
        var btn = $(this);
        btnLoading(btn, true);
        var attemptId = parseInt(sessionStorage.getItem('attempt_id'), 10);

        post('/plan/get.php', { attempt_id: attemptId })
            .done(function (r) {
                var p = r.plan;
                var html = '<h4>' + (p.title || 'Study Plan') + '</h4>';
                if (p.days && p.days.length) {
                    $.each(p.days, function (i, day) {
                        html += '<div class="question-block"><strong>Day ' + day.day + ':</strong> ' + day.focus + '<ul>';
                        $.each(day.tasks || [], function (j, t) { html += '<li>' + t + '</li>'; });
                        html += '</ul></div>';
                    });
                }
                $('#plan-content').html(html);
                $('#plan-box').show();
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error getting plan');
            })
            .always(function () { btnLoading(btn, false); });
    });

    // ── Dashboard ────────────────────────────────────────────────

    function loadDashboard() {
        get('/dashboard/summary.php').done(function (r) {
            var s = r.stats;
            $('#s-activities').text(s.total_activities);
            $('#s-tutor').text(s.tutor_sessions);
            $('#s-notes').text(s.notes_generated);
            $('#s-quiz').text(s.quizzes_taken);
            $('#s-tests').text(s.tests_attempted);
            $('#s-avg').text(s.avg_score + '%');
        });
    }

    // ── History ──────────────────────────────────────────────────

    function loadHistory() {
        get('/history/list.php').done(function (r) {
            // Activities
            var html = '';
            if (r.activities.length === 0) {
                html = '<p>No activities yet.</p>';
            } else {
                $.each(r.activities, function (i, a) {
                    html += '<div class="history-item"><span><strong>' + a.module + '</strong> – ' + a.topic + '</span><span>' + a.created_at + '</span></div>';
                });
            }
            $('#activity-list').html(html);

            // Attempts
            html = '';
            if (r.attempts.length === 0) {
                html = '<p>No test attempts yet.</p>';
            } else {
                $.each(r.attempts, function (i, a) {
                    html += '<div class="history-item"><span><strong>' + a.topic + '</strong> – ' + a.score + '/' + a.total + ' (' + a.percentage + '%)</span><span>' + a.created_at + '</span></div>';
                });
            }
            $('#attempt-list').html(html);
        });
    }

    // ── Public API ───────────────────────────────────────────────
    return {
        loadDashboard: loadDashboard,
        loadHistory: loadHistory,
        loadResult: loadResult
    };

})(jQuery);
